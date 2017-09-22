<?php

namespace index\controllers;


use index\forms\UserProfileForm;
use Silex\Application;
use index\forms\SignUpUserForm;
use index\forms\LogInUserForm;
use index\forms\ForgotUserForm;
use index\forms\ResetUserForm;
use index\forms\UserEmailsForm;
use index\forms\UserPrefsForm;
use Symfony\Component\HttpFoundation\Request;
use models\UserAccountModel;
use repositories\UserAccountRepository;
use repositories\UserAccountRememberMeRepository;
use repositories\builders\SiteRepositoryBuilder;
use Symfony\Component\Form\FormError;
use repositories\UserAccountResetRepository;
use index\forms\UserChangePasswordForm;
use repositories\builders\filterparams\EventFilterParams;
use repositories\UserAccountVerifyEmailRepository;
use repositories\UserNotificationRepository;
use repositories\builders\UserNotificationRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CurrentUserController {
	
	
	function logout(Application $app) {
		if ($app['currentUser']) {
			userLogOut();
			// We do this redirect so the page view can be set up again from fresh with the correct enviroment
			// Maybe we could store in the user session which user it was here ...
			return $app->redirect('/me/logout');
		} else {
			// ... so here we can get user out of the session and pass to template so goodbye message can be personalised?
			return $app['twig']->render('index/user/logout.html.twig', array( ));
		}
	}
	
	function verifyNeeded(Application  $app) {
		return $app['twig']->render('index/currentuser/verifyAccountNeeded.html.twig', array());
	}
	
	
	function resendVerifyEmail(Request $request, Application $app) {		
		$repo = new UserAccountVerifyEmailRepository($app);
		
		$date = $repo->getLastSentForUserAccount($app['currentUser']);
		if ($date && $date->getTimestamp() > (\TimeSource::time() - $app['config']->userAccountVerificationSecondsBetweenAllowedSends)) {
			$app['flashmessages']->addMessage("Sorry, but an email was sent too recently. Please try again later.");
		}  else {
			
			$verifyEmail = $repo->create($app['currentUser']);
			$verifyEmail->sendEmail($app, $app['currentUser']);


			$app['flashmessages']->addMessage("Verification email resent.");
		
		}
		
		return $app->redirect("/me/");
		
	}
	
	function changePassword(Request $request, Application $app) {		
		$form = $app['form.factory']->create(UserChangePasswordForm::class);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();

				if (!$app['currentUser']->checkPassword($data['oldpassword'])) {
					$form->addError(new FormError('old password wrong'));
				} else {
					$app['currentUser']->setPassword($data['password1']);
					$userAccountRepository = new UserAccountRepository($app);
					$userAccountRepository->editPassword($app['currentUser']);
					$app['flashmessages']->addMessage("Password Changed.");
                    return $app->redirect("/me/");
                }
								
			}
		}
		
		
		return $app['twig']->render('index/currentuser/changePassword.html.twig', array(
			'form'=>$form->createView(),
		));
	}
	
	function emails(Request $request, Application $app) {
		$ourForm = new UserEmailsForm($app['extensions'], $app['currentUser']);
		$form = $app['form.factory']->create($ourForm, $app['currentUser']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$userRepo = new UserAccountRepository($app);
				$userRepo->editEmailsOptions($app['currentUser']);
				$ourForm->savePreferences($form);
				$app['flashmessages']->addMessage("Options Changed.");
				return $app->redirect("/me/");
			}
		}
		
		return $app['twig']->render('index/currentuser/emails.html.twig', array(
			'form'=>$form->createView(),
		));
	}

	function profile(Request $request, Application $app) {
		$form = $app['form.factory']->create(UserProfileForm::class, $app['currentUser']);

		if ('POST' == $request->getMethod()) {
			$form->bind($request);

            if (!$app['config']->isDisplayNameAllowed($app['currentUser']->getDisplayname())) {
                $form->addError(new FormError('That name is not allowed'));
            }

			if ($form->isValid()) {
				$userRepo = new UserAccountRepository($app);
				$userRepo->editProfile($app['currentUser']);
				$app['flashmessages']->addMessage("Profile Changed.");
				return $app->redirect("/me/");
			}
		}

		return $app['twig']->render('index/currentuser/profile.html.twig', array(
			'form'=>$form->createView(),
		));
	}

	
	function prefs(Request $request, Application $app) {		
		$form = $app['form.factory']->create(UserPrefsForm::class, $app['currentUser']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$userRepo = new UserAccountRepository($app);
				$userRepo->editPreferences($app['currentUser']);
				$app['flashmessages']->addMessage("Options Changed.");
				return $app->redirect("/me/");
			}
		}
		
		return $app['twig']->render('index/currentuser/prefs.html.twig', array(
			'form'=>$form->createView(),
		));
	}
	
	function index(Request $request, Application $app) {
		
		return $app['twig']->render('index/currentuser/index.html.twig', array(
		));
		
	}
	
	function sites(Request $request, Application $app) {
		
		$srb = new SiteRepositoryBuilder($app);
		$srb->setUserInterestedIn($app['currentUser']);
		$srb->setIsOpenBySysAdminsOnly(true);
		
		return $app['twig']->render('index/currentuser/sites.html.twig', array(
			'sites'=>$srb->fetchAll(),
		));
		
	}
	
	function agenda(Request $request, Application $app) {
		
		$params = new EventFilterParams($app);
		$params->setSpecifiedUserControls(true, $app['currentUser'], true);
		$params->getEventRepositoryBuilder()->setIncludeAreaInformation(true);
		$params->getEventRepositoryBuilder()->setIncludeVenueInformation(true);
		$params->getEventRepositoryBuilder()->setIncludeMediasSlugs(true);
		$params->set($_GET);
		$events = $params->getEventRepositoryBuilder()->fetchAll();
		
		return $app['twig']->render('index/currentuser/agenda.html.twig', array(
				'eventListFilterParams'=>$params,
				'events'=>$events,
			));
		
	}
	
	
	
	function calendarNow(Application $app) {
		$cal = new \RenderCalendar($app);
		$params = new EventFilterParams($app, $cal->getEventRepositoryBuilder());
		$params->setHasDateControls(false);
		$params->setSpecifiedUserControls(true, $app['currentUser'], true);
        $params->setFallBackFrom(true);
		$params->set($_GET);
		$cal->byDate(\TimeSource::getDateTime(), 31, true);
		
		list($prevYear,$prevMonth,$nextYear,$nextMonth) = $cal->getPrevNextLinksByMonth();

		return $app['twig']->render('/index/currentuser/calendar.html.twig', array(
				'calendar'=>$cal,
				'eventListFilterParams'=>$params,
				'prevYear' => $prevYear,
				'prevMonth' => $prevMonth,
				'nextYear' => $nextYear,
				'nextMonth' => $nextMonth,
			));
	}
	
	function calendar($year, $month, Application $app) {
		
		$cal = new \RenderCalendar($app);
		$params = new EventFilterParams($app, $cal->getEventRepositoryBuilder());
		$params->setHasDateControls(false);
		$params->setSpecifiedUserControls(true, $app['currentUser'], true);
        $params->setFallBackFrom(true);
		$params->set($_GET);
		$cal->byMonth($year, $month, true);
		
		list($prevYear,$prevMonth,$nextYear,$nextMonth) = $cal->getPrevNextLinksByMonth();

		return $app['twig']->render('/index/currentuser/calendar.html.twig', array(
				'calendar'=>$cal,
				'eventListFilterParams'=>$params,
				'prevYear' => $prevYear,
				'prevMonth' => $prevMonth,
				'nextYear' => $nextYear,
				'nextMonth' => $nextMonth,
			));
	}
	
	function listNotifications(Application $app) {
	
		$rb = new UserNotificationRepositoryBuilder($app);
		$rb->setLimit(20);
		$rb->setUser($app['currentUser']);
		
		$notifications = $rb->fetchAll();
		
		
			return $app['twig']->render('/index/currentuser/notifications.html.twig', array(
				'notifications'=>$notifications,
			));
	}
	
	function listNotificationsJson(Application $app) {
	
		$rb = new UserNotificationRepositoryBuilder($app);
		$rb->setIsOpenBySysAdminsOnly(true);
		$rb->setLimit(20);
		$rb->setUser($app['currentUser']);
		
		$notifications = $rb->fetchAll();

		$out = array();
		foreach($notifications as $notification) {
			$data = array(
				'id'=>$notification->getId(),
				'text'=>$notification->getNotificationText(),
				'read'=>$notification->getIsRead(),
				'timesince'=>$app['twig']->render('timediff.html.twig', array('data'=>$notification->getCreatedAt())),
				'site'=>null,
			);
            if ($notification->getSite()) {
                $data['site'] = array(
                    'slug'=>$notification->getSite()->getSlug(),
                    'title'=>$notification->getSite()->getTitle(),
                );
            }
            $out[] = $data;
		}
		
		return json_encode(array('notifications'=>$out));
		
	}
	
	function showNotification($id, Application $app) {

		// get
		$repo = new UserNotificationRepository($app);
		$notification = $repo->loadByIdForUser($id, $app['currentUser']);

		// Mark read
		$repo->markRead($notification);
		
		// Redirect
		$url = $notification->getNotificationURL();
		return $app->redirect($url);
		
	}
	
}

