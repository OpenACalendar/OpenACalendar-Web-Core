<?php

namespace index\controllers;


use repositories\EventRepository;
use repositories\SiteRepository;
use repositories\UserAtEventRepository;
use Silex\Application;
use index\forms\SignUpUserForm;
use index\forms\LogInUserForm;
use index\forms\ForgotUserForm;
use index\forms\ResetUserForm;
use index\forms\UserEmailsForm;
use Symfony\Component\HttpFoundation\Request;
use models\UserAccountModel;
use repositories\UserAccountRepository;
use repositories\UserAccountRememberMeRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\builders\SiteRepositoryBuilder;
use Symfony\Component\Form\FormError;
use repositories\UserAccountResetRepository;
use index\forms\UserChangePasswordForm;
use repositories\UserAccountVerifyEmailRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserController {
	
	function index(Request $request, Application $app) {
		
		return $app['twig']->render('index/user/index.html.twig', array(
		));
	}


	protected $parameters = array();

	protected function processThingsToDoAfterGetUser(Request $request, Application $app) {
		global $CONFIG;


		$eventRepo = new EventRepository();
		$event = null;

		// Any events to add?
		if ($request->query->has("event")) {
			if ($CONFIG->isSingleSiteMode) {
				$event = $eventRepo->loadBySiteIDAndEventSlug($CONFIG->singleSiteID, $request->query->get("event"));
			} else {
				$siteRepo = new SiteRepository();
				$site = $siteRepo->loadBySlug($request->query->get("eventSite"));
				if ($site) {
					$event = $eventRepo->loadBySlug($site, $request->query->get("event"));
				}
			}
			if ($event && !$event->getIsDeleted()) {
				if (!$app['websession']->hasArray("afterGetUserAddEvents")) {
					$app['websession']->setArray("afterGetUserAddEvents",array($event->getId()));
				} else {
					if (!in_array($event->getId(),$app['websession']->getArray("afterGetUserAddEvents") )) {
						$app['websession']->appendArray("afterGetUserAddEvents", $event->getId());
					}
				}
			}
		}

		// load events to show user
		$this->parameters['afterGetUserAddEvents'] = array();
		if ($app['websession']->hasArray("afterGetUserAddEvents")) {
			foreach($app['websession']->getArray("afterGetUserAddEvents") as $eventID) {
				if ($event != null && $eventID == $event->getId()) {
					if ($event->getIsAllowedForAfterGetUser()) {
						$this->parameters['afterGetUserAddEvents'][] = $event;
					}
				} else {
					$eventTmp = $eventRepo->loadByID($eventID);
					if ($eventTmp && $eventTmp->getIsAllowedForAfterGetUser()) {
						$this->parameters['afterGetUserAddEvents'][] = $eventTmp;
					}
				}
			}

		}

	}

	protected function actionThingsToDoAfterGetUser(Application $app, UserAccountModel $user) {

		$uaerepo = new UserAtEventRepository();

		$eventsAdded = false;
		foreach($this->parameters['afterGetUserAddEvents'] as $event) {
			$uae = $uaerepo->loadByUserAndEventOrInstanciate($user, $event);
			if (!$uae->getIsPlanAttending() && !$uae->getIsPlanMaybeAttending()) {
				$uae->setIsPlanMaybeAttending(true);
				$uaerepo->save($uae);
				$eventsAdded = true;
			}
		}
		if ($eventsAdded) {
			$app['flashmessages']->addMessage("Check out your personal calendar for events you are interested in!"); // TODO add link
		}

		$app['websession']->setArray("afterGetUserAddEvents",array());

	}

	function register(Request $request, Application $app) {
		global $CONFIG;
		if (!$app['config']->allowNewUsersToRegister) {
			return $app['twig']->render('index/user/register.notallowed.html.twig', array(
			));
		}

		$this->processThingsToDoAfterGetUser($request, $app);

		$userRepository = new UserAccountRepository();
				
		$form = $app['form.factory']->create(new SignUpUserForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			$data = $form->getData();

			if (is_array($CONFIG->userNameReserved) && in_array($data['username'], $CONFIG->userNameReserved)) {
				$form->addError(new FormError('That user name is already taken'));
			}

			$userExistingUserName = $userRepository->loadByUserName($data['username']);
			if ($userExistingUserName) {
				$form->addError(new FormError('That user name is already taken'));
			}
			
			$userExistingEmail = $userRepository->loadByEmail($data['email']);
			if ($userExistingEmail) {
				$form->addError(new FormError('That email address already has an account'));
			}
			
			if ($form->isValid()) {
			
				$user = new UserAccountModel();
				$user->setEmail($data['email']);
				$user->setUsername($data['username']);
				$user->setPassword($data['password1']);
				
				$userRepository->create($user);
				
				$repo = new UserAccountVerifyEmailRepository();
				$userVerify = $repo->create($user);
				$userVerify->sendEmail($app, $user);
				
				userLogIn($user);
				$this->actionThingsToDoAfterGetUser($app, $user);
				return $app->redirect("/");
				
			}
		}
		

		$this->parameters['form'] = $form->createView();

		return $app['twig']->render('index/user/register.html.twig', $this->parameters);
		
	}
	
	function login(Request $request, Application $app) {				
		$form = $app['form.factory']->create(new LogInUserForm());


		$this->processThingsToDoAfterGetUser($request, $app);

		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();
				
				$userRepository = new UserAccountRepository();
				if ($data['email']) {
					$user = $userRepository->loadByEmail($data['email']);
				} else if ($data['username']) {
					$user = $userRepository->loadByUserName($data['username']);
				}
				if ($user) {
					if ($user->checkPassword($data['password'])) {
						if ($user->getIsClosedBySysAdmin()) {
							$form->addError(new FormError('There was a problem with this account and it has been closed: '.$user->getClosedBySysAdminReason()));
							$app['monolog']->addError("Login attempt - account ".$user->getId().' - closed.');
						} else {
							userLogIn($user);
							$this->actionThingsToDoAfterGetUser($app, $user);

							if ($data['rememberme']) {
								$uarmr = new UserAccountRememberMeRepository();
								$uarm = $uarmr->create($user);
								$uarm->sendCookies();
							}

							return $app->redirect("/");
						}
					} else {
						$app['monolog']->addError("Login attempt - account ".$user->getId().' - password wrong.');
						$form->addError(new FormError('User and password not recognised'));
					}
				} else {
					$app['monolog']->addError("Login attempt - unknown account");
					$form->addError(new FormError('User and password not recognised'));
				}
				
			}
		}

		$this->parameters['form'] = $form->createView();

		return $app['twig']->render('index/user/login.html.twig', $this->parameters);
		
	}	
	
	
	function verify($id, $code, Application  $app) {
		
		$userRepository = new UserAccountRepository();
		
		if ($app['currentUser'] && $app['currentUser']->getId() == $id) {
			// we don't just do this to save a DB Query. We do this so when we mark user object 
			// verified the user object available to twig is marked verified and so the user
			// doesn't see big notices on the page.
			$user = $app['currentUser'];
		} else {
			$user = $userRepository->loadByID($id);
		}
		
		if (!$user) {
			$app['monolog']->addError("Failed verifying account - no user");
			return $app['twig']->render('index/user/verifyFail.html.twig', array());
		}
		if ($user->getIsEmailVerified()) {
			$app['monolog']->addError("Failed verifying account - user ".$user->getId()." - already verified");
			return $app['twig']->render('index/user/verifyDone.html.twig', array());
		}
		
		$repo = new UserAccountVerifyEmailRepository();
		$userVerifyCode = $repo->loadByUserAccountIDAndAccessKey($id, $code);
		
		if ($userVerifyCode) {
			// new way of generating access codes
			$repo->markVerifiedByUserAccountIDAndAccessKey($id, $code);
			$user->setIsEmailVerified(true);
			return $app['twig']->render('index/user/verifyDone.html.twig', array());
		} else if ($user->getEmailVerifyCode() && $user->getEmailVerifyCode() == $code) {
			// old way of generating access codes
			$userRepository->verifyEmail($user);
			$user->setIsEmailVerified(true);
			return $app['twig']->render('index/user/verifyDone.html.twig', array());
		} else {
			$app['monolog']->addError("Failed verifying account - user ".$user->getId());
			return $app['twig']->render('index/user/verifyFail.html.twig', array());
		}

	}
	
	
	function forgot(Request $request, Application $app) {		
		$form = $app['form.factory']->create(new ForgotUserForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();
				
				$userRepository = new UserAccountRepository();
				if ($data['email']) {
					$user = $userRepository->loadByEmail($data['email']);
				} else if ($data['username']) {
					$user = $userRepository->loadByUserName($data['username']);
				}
				if ($user) {
					if ($user->getIsClosedBySysAdmin()) {
						$form->addError(new FormError('There was a problem with this account and it has been closed: '.$user->getClosedBySysAdminReason()));
					} else {
						$aurr = new UserAccountResetRepository();
						$uarLast = $aurr->loadRecentlyUnusedSentForUserAccountId($user->getId(), $app['config']->resetEmailsGapBetweenInSeconds);
						if ($uarLast) {
							$form->addError(new FormError('An email was sent recently; please try again soon'));
						} else {
							$uar = $aurr->create($user);
							$uar->sendEmail($app, $user);
							return $app['twig']->render('index/user/forgotDone.html.twig', array());
						}
					}
				} else {
					$form->addError(new FormError('User not known'));
				}
				
			}
		}
		
		return $app['twig']->render('index/user/forgot.html.twig', array(
			'form'=>$form->createView(),
		));

	}
	
	function reset($id, $code, Request $request, Application $app) {
		
		$userRepository = new UserAccountRepository();
		
		if ($app['currentUser'] && $app['currentUser']->getId() == $id) {
			// We do this to save a DB Query
			$user = $app['currentUser'];
		} else {
			$user = $userRepository->loadByID($id);
		}
		
		if (!$user) {
			$app['monolog']->addError("Failed resetting account - user not known");
			return $app['twig']->render('index/user/resetFail.html.twig', array());
		}
		
		$userAccountResetRepository = new UserAccountResetRepository();
		$userAccountReset = $userAccountResetRepository->loadByUserAccountIDAndAccessKey($id, $code);
		
		if (!$userAccountReset) {
			$app['monolog']->addError("Failed resetting account - user ".$user->getId()." - code wrong");
			return $app['twig']->render('index/user/resetFail.html.twig', array());
		}
		
		if ($userAccountReset->getIsAlreadyUsed()) {
			$app['monolog']->addError("Failed resetting account - user ".$user->getId()." - code already used");
			return $app['twig']->render('index/user/resetFail.html.twig', array());
		}
		
		$form = $app['form.factory']->create(new ResetUserForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();
		
				$user->setPassword($data['password1']);
				
				$userRepository->resetAccount($user, $userAccountReset);
				
				return $app['twig']->render('index/user/resetDone.html.twig', array(
						'user'=>$user,
					));
			}
		}
		
		return $app['twig']->render('index/user/reset.html.twig', array(
			'form'=>$form->createView(),
			'user'=>$user,
		));

	}
	
	
	function emails($id, $code, Request $request, Application $app) {		
		$userRepository = new UserAccountRepository();
		
		if ($app['currentUser'] && $app['currentUser']->getId() == $id) {
			// We do this to save a DB Query
			$user = $app['currentUser'];
		} else {
			$user = $userRepository->loadByID($id);
		}
		
		
		if (!$user) {
			$app['monolog']->addError("Failed changing email - no account");
			return $app['twig']->render('index/user/emails.fail.html.twig', array());
		}
		
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
		$userAccountGSK = $userAccountGeneralSecurityKeyRepository->loadByUserAccountIDAndAccessKey($id, $code);
		
		if (!$userAccountGSK) {
			$app['monolog']->addError("Failed changing email - account user ".$user->getId()." - code wrong");
			return $app['twig']->render('index/user/emails.fail.html.twig', array());
		}
		
		
		$ourForm = new UserEmailsForm($app['extensions'], $user);
		$form = $app['form.factory']->create($ourForm, $user);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$userRepository->editEmailsOptions($user);
				$ourForm->savePreferences($form);
				$app['flashmessages']->addMessage("Options Changed.");
				return $app->redirect("/");
			}
		}
		
		return $app['twig']->render('index/user/emails.html.twig', array(
			'form'=>$form->createView(),
			'user'=>$user,
		));
	}
	
}

