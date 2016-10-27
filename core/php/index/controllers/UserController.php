<?php

namespace index\controllers;


use models\UserAccountEditMetaDataModel;
use models\UserInterestedInSiteModel;
use repositories\AreaRepository;
use repositories\EventRepository;
use repositories\SiteRepository;
use repositories\UserAtEventRepository;
use repositories\UserInterestedInSiteRepository;
use repositories\UserWatchesAreaRepository;
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
use Symfony\Component\HttpFoundation\Response;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserController {
	
	function index(Request $request, Application $app) {
		
		return $app['twig']->render('index/user/index.html.twig', array(
		));
	}


	protected $parameters = array();

	protected function processThingsToDoAfterGetUser(Request $request, Application $app) {

		$siteRepo = new SiteRepository($app);
		$eventRepo = new EventRepository($app);
		$areaRepo = new AreaRepository($app);
		$event = null;
		$area = null;
		$siteToAdd = null;

		// Any sites of general interest?
		if ($request->query->has("site") && !$app['config']->isSingleSiteMode) {
			$siteToAdd = $siteRepo->loadBySlug($request->query->get("site"));
			if ($siteToAdd && $siteToAdd->getIsAllowedForAfterGetUser()) {
				if (!$app['websession']->hasArray("afterGetUserAddSites")) {
					$app['websession']->setArray("afterGetUserAddSites",array($siteToAdd->getId()));
				} else {
					if (!in_array($siteToAdd->getId(),$app['websession']->getArray("afterGetUserAddSites") )) {
						$app['websession']->appendArray("afterGetUserAddSites", $siteToAdd->getId());
					}
				}
			}
		}

		// Any events to add?
		if ($request->query->has("event")) {
			if ($app['config']->isSingleSiteMode) {
				$event = $eventRepo->loadBySiteIDAndEventSlug($app['config']->singleSiteID, $request->query->get("event"));
			} else {
				$site = $siteRepo->loadBySlug($request->query->get("eventSite"));
				if ($site) {
					$event = $eventRepo->loadBySlug($site, $request->query->get("event"));
				}
			}
			if ($event && $event->getIsAllowedForAfterGetUser()) {
				if (!$app['websession']->hasArray("afterGetUserAddEvents")) {
					$app['websession']->setArray("afterGetUserAddEvents",array($event->getId()));
				} else {
					if (!in_array($event->getId(),$app['websession']->getArray("afterGetUserAddEvents") )) {
						$app['websession']->appendArray("afterGetUserAddEvents", $event->getId());
					}
				}
			}
		}


		// Any areas to add?
		if ($request->query->has("area")) {
			if ($app['config']->isSingleSiteMode) {
				$area = $areaRepo->loadBySiteIDAndAreaSlug($app['config']->singleSiteID, $request->query->get("area"));
			} else {
				$site = $siteRepo->loadBySlug($request->query->get("areaSite"));
				if ($site) {
					$area = $areaRepo->loadBySlug($site, $request->query->get("area"));
				}
			}
			if ($area && $area->getIsAllowedForAfterGetUser()) {
				if (!$app['websession']->hasArray("afterGetUserAddAreas")) {
					$app['websession']->setArray("afterGetUserAddAreas",array($area->getId()));
				} else {
					if (!in_array($area->getId(),$app['websession']->getArray("afterGetUserAddAreas") )) {
						$app['websession']->appendArray("afterGetUserAddAreas", $area->getId());
					}
				}
			}
		}

		// Remove events?
		if ($request->query->has("removeEventId")) {
			$app['websession']->removeValueFromArray("afterGetUserAddEvents", $request->query->has("removeEventId"));
		}

		// Remove areas?
		if ($request->query->has("removeAreaId")) {
			$app['websession']->removeValueFromArray("afterGetUserAddAreas", $request->query->has("removeAreaId"));
		}

		// Remove Sites?
		if ($request->query->has("removeSiteId")) {
			$app['websession']->removeValueFromArray("afterGetUserAddSites", $request->query->has("removeSiteId"));
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

		// load areas to show user
		$this->parameters['afterGetUserAddAreas'] = array();
		if ($app['websession']->hasArray("afterGetUserAddAreas")) {
			foreach($app['websession']->getArray("afterGetUserAddAreas") as $areaID) {
				if ($area != null && $areaID == $area->getId()) {
					if ($area->getIsAllowedForAfterGetUser()) {
						$this->parameters['afterGetUserAddAreas'][] = $area;
					}
				} else {
					$areaTmp = $areaRepo->loadByID($areaID);
					if ($areaTmp && $areaTmp->getIsAllowedForAfterGetUser()) {
						$this->parameters['afterGetUserAddAreas'][] = $areaTmp;
					}
				}
			}
		}

		// load sites to show user
		$this->parameters['afterGetUserAddSites'] = array();
		if ($app['websession']->hasArray("afterGetUserAddSites")) {
			foreach($app['websession']->getArray("afterGetUserAddSites") as $siteID) {
				if ($siteToAdd != null && $siteID == $siteToAdd->getId()) {
					if ($siteToAdd->getIsAllowedForAfterGetUser()) {
						$this->parameters['afterGetUserAddSites'][] = $siteToAdd;
					}
				} else {
					$siteTmp = $siteRepo->loadByID($siteID);
					if ($siteTmp && $siteTmp->getIsAllowedForAfterGetUser()) {
						$this->parameters['afterGetUserAddSites'][] = $siteTmp;
					}
				}
			}
		}


	}


	protected function actionThingsToDoAfterGetUser(Application $app, UserAccountModel $user) {

		// events
		$uaerepo = new UserAtEventRepository($app);

		$eventsAdded = false;
		foreach($this->parameters['afterGetUserAddEvents'] as $event) {
			if ($event->getIsAllowedForAfterGetUser()) {
				$uae = $uaerepo->loadByUserAndEventOrInstanciate($user, $event);
				if (!$uae->getIsPlanAttending() && !$uae->getIsPlanMaybeAttending()) {
					$uae->setIsPlanMaybeAttending(true);
					$uaerepo->save($uae);
					$eventsAdded = true;
				}
			}
		}
		if ($eventsAdded) {
			$app['flashmessages']->addMessage("Check out your personal calendar for events you are interested in!"); // TODO add link
		}

		// areas
		$uwarepo = new UserWatchesAreaRepository($app);
		foreach($this->parameters['afterGetUserAddAreas'] as $area) {
			if ($area->getIsAllowedForAfterGetUser()) {
				$uwarepo->startUserWatchingArea($user, $area);
			}
		}

		// sites
		$uiisr = new UserInterestedInSiteRepository($app);
		foreach($this->parameters['afterGetUserAddSites'] as $site) {
			if ($site->getIsAllowedForAfterGetUser()) {
				$uiisr->markUserInterestedInSite($user, $site);
			}
		}

		// reset
		$app['websession']->setArray("afterGetUserAddEvents",array());
		$app['websession']->setArray("afterGetUserAddAreas",array());
		$app['websession']->setArray("afterGetUserAddSites",array());

	}



	public function afterGetUserAPI(Request $request, Application $app) {

		$this->processThingsToDoAfterGetUser($request, $app);

		$response = new Response(json_encode(array('result'=>'ok')));
		$response->headers->set('Content-Type', 'application/json');
		return $response;

	}


	function register(Request $request, Application $app) {
		if (!$app['config']->allowNewUsersToRegister) {
			return $app['twig']->render('index/user/register.notallowed.html.twig', array(
			));
		}

        if ($app['currentUser']) {
            return $app->redirect("/");
        }

		$this->processThingsToDoAfterGetUser($request, $app);

		$userRepository = new UserAccountRepository($app);
				
		$form = $app['form.factory']->create(new SignUpUserForm($app));
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			$data = $form->getData();

            if (is_array($app['config']->userNameReserved)) {
                foreach($app['config']->userNameReserved as $reserved) {
                    if (UserAccountModel::makeCanonicalUserName($reserved) == UserAccountModel::makeCanonicalUserName($data['username'])) {
                        $form->addError(new FormError('That user name is already taken'));
                    }
                }
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

				$userAccountMeta = new UserAccountEditMetaDataModel();
				$userAccountMeta->setFromRequest($request);

				$userRepository->create($user, $userAccountMeta);
				
				$repo = new UserAccountVerifyEmailRepository($app);
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
        if ($app['currentUser']) {
            return $app->redirect("/");
        }

		$form = $app['form.factory']->create(new LogInUserForm());


		$this->processThingsToDoAfterGetUser($request, $app);

		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();
				
				$userRepository = new UserAccountRepository($app);
				$user = null;
				// We are deliberately very forgiving about people putting the wrong thing in the wrong field.
				if ($data['email']) {
					$user = $userRepository->loadByUserNameOrEmail($data['email']);
				}
				if (!$user && $data['username']) {
					$user = $userRepository->loadByUserNameOrEmail($data['username']);
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
								$uarmr = new UserAccountRememberMeRepository($app);
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
	
	
	function verify($id, $code, Application  $app, Request $request) {
		
		$userRepository = new UserAccountRepository($app);
		
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
			return $app['twig']->render('index/user/verifyDone.html.twig', array('user'=>$user));
		}

		$repo = new UserAccountVerifyEmailRepository($app);
		$userVerifyCode = $repo->loadByUserAccountIDAndAccessKey($id, $code);
		
		if ($userVerifyCode) {
			// new way of generating access codes
		} else if ($user->getEmailVerifyCode() && $user->getEmailVerifyCode() == $code) {
			// old way of generating access codes
		} else {
			$app['monolog']->addError("Failed verifying account - user ".$user->getId());
			return $app['twig']->render('index/user/verifyFail.html.twig', array());
		}

		if ($request->getMethod() == 'POST' && $request->request->get('action') == 'verify' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			if ($userVerifyCode) {
				// new way of generating access codes
				$repo->markVerifiedByUserAccountIDAndAccessKey($id, $code, $request->server->get('REMOTE_ADDR'));
				$user->setIsEmailVerified(true);
			} else if ($user->getEmailVerifyCode() && $user->getEmailVerifyCode() == $code) {
				// old way of generating access codes
				$userRepository->verifyEmail($user);
				$user->setIsEmailVerified(true);
			}
			return $app['twig']->render('index/user/verifyDone.html.twig', array('user'=>$user));
		} else {
			return $app['twig']->render('index/user/verify.html.twig', array('user'=>$user));
		}

	}


    function forgot(Request $request, Application $app) {
        if ($app['currentUser']) {
            return $app->redirect("/");
        }

		$form = $app['form.factory']->create(new ForgotUserForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();
				
				$userRepository = new UserAccountRepository($app);
				if ($data['email']) {
					$user = $userRepository->loadByEmail($data['email']);
				} else if ($data['username']) {
					$user = $userRepository->loadByUserName($data['username']);
				}
				if ($user) {
					if ($user->getIsClosedBySysAdmin()) {
						$form->addError(new FormError('There was a problem with this account and it has been closed: '.$user->getClosedBySysAdminReason()));
					} else {
						$aurr = new UserAccountResetRepository($app);
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
        if ($app['currentUser']) {
            return $app->redirect("/");
        }
		
		$userRepository = new UserAccountRepository($app);
		
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
		
		$userAccountResetRepository = new UserAccountResetRepository($app);
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
		$userRepository = new UserAccountRepository($app);
		
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
		
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository($app);
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

    function listUnsub($id, $code, Request $request, Application $app) {
        $userRepository = new UserAccountRepository($app);

        if ($app['currentUser'] && $app['currentUser']->getId() == $id) {
            // We do this to save a DB Query
            $user = $app['currentUser'];
        } else {
            $user = $userRepository->loadByID($id);
        }

        if (!$user) {
            $app['monolog']->addError("Failed List-Unsubscribe - no account");
            $app->abort(404, "No User");
        }

        $userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository($app);
        $userAccountGSK = $userAccountGeneralSecurityKeyRepository->loadByUserAccountIDAndAccessKey($id, $code);

        if (!$userAccountGSK) {
            $app['monolog']->addError("Failed List-Unsubscribe - account user ".$user->getId()." - code wrong");
            $app->abort(404, "No User");
        }

        // Turn off email prefs on user
        $user->setEmailUpcomingEvents('n');
        $userRepository->editEmailsOptions($user);

        // Turn off all user notification prefs
        $repo = new \repositories\UserNotificationPreferenceRepository($app);
        foreach($app['extensions']->getExtensionsIncludingCore() as $extension) {
            $extID = $extension->getId();
            foreach($extension->getUserNotificationPreferenceTypes() as $type) {
                $repo->editEmailPreference($user,$extID, $type, false);
            }
        }

        $app['monolog']->addError("List-Unsubscribe - account user ".$user->getId()." - done");

        return 'Unsubscribed.';
    }

}

