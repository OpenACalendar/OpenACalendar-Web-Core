<?php

namespace index\controllers;


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
use twig\extensions\TimeSinceInWordsExtension;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CurrentUserController {
	
	
	function logout(Application $app) {
		userLogOut();
		
		return $app['twig']->render('index/user/logout.html.twig', array(
				'currentUser'=>null,
			));
	}
	
	function verifyNeeded(Application  $app) {
		return $app['twig']->render('index/currentuser/verifyAccountNeeded.html.twig', array());
	}
	
	
	function resendVerifyEmail(Request $request, Application $app) {		
		$repo = new UserAccountVerifyEmailRepository();
		
		$date = $repo->getLastSentForUserAccount(userGetCurrent());
		if ($date && $date->getTimestamp() > (\TimeSource::time() - $app['config']->userAccountVerificationSecondsBetweenAllowedSends)) {
			$app['flashmessages']->addMessage("Sorry, but the email was sent to recently. Please try again soon.");
		}  else {
			
			$verifyEmail = $repo->create(userGetCurrent());
			$verifyEmail->sendEmail($app, userGetCurrent());


			$app['flashmessages']->addMessage("Verification email resent.");
		
		}
		
		return $app->redirect("/me/");
		
	}
	
	function changePassword(Request $request, Application $app) {		
		$form = $app['form.factory']->create(new UserChangePasswordForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();

				if (!userGetCurrent()->checkPassword($data['oldpassword'])) {
					$form->addError(new FormError('old password wrong'));
				} else {
					userGetCurrent()->setPassword($data['password1']);
					$userAccountRepository = new UserAccountRepository();
					$userAccountRepository->editPassword(userGetCurrent());
					$app['flashmessages']->addMessage("Password Changed.");
					return $app['twig']->render('index/currentuser/changePasswordDone.html.twig', array());
				}
								
			}
		}
		
		
		return $app['twig']->render('index/currentuser/changePassword.html.twig', array(
			'form'=>$form->createView(),
		));
	}
	
	function emails(Request $request, Application $app) {
		$ourForm = new UserEmailsForm($app['extensions'], userGetCurrent());
		$form = $app['form.factory']->create($ourForm, userGetCurrent());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$userRepo = new UserAccountRepository;
				$userRepo->editEmailsOptions(userGetCurrent());
				$ourForm->savePreferences($form);
				$app['flashmessages']->addMessage("Options Changed.");
				return $app->redirect("/me/");
			}
		}
		
		return $app['twig']->render('index/currentuser/emails.html.twig', array(
			'form'=>$form->createView(),
		));
	}
	
	
	function prefs(Request $request, Application $app) {		
		$form = $app['form.factory']->create(new UserPrefsForm(), userGetCurrent());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$userRepo = new UserAccountRepository;
				$userRepo->editPreferences(userGetCurrent());
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
		
		$srb = new SiteRepositoryBuilder();
		$srb->setUserInterestedIn(userGetCurrent());
		$srb->setIsOpenBySysAdminsOnly(true);
		
		return $app['twig']->render('index/currentuser/sites.html.twig', array(
			'sites'=>$srb->fetchAll(),
		));
		
	}
	
	function agenda(Request $request, Application $app) {
		
		$params = new EventFilterParams();
		$params->setSpecifiedUserControls(true, userGetCurrent(), true);
		$params->getEventRepositoryBuilder()->setIncludeAreaInformation(true);
		$params->getEventRepositoryBuilder()->setIncludeVenueInformation(true);
		$params->set($_GET);
		$events = $params->getEventRepositoryBuilder()->fetchAll();
		
		return $app['twig']->render('index/currentuser/agenda.html.twig', array(
				'eventListFilterParams'=>$params,
				'events'=>$events,
			));
		
	}
	
	
	
	function calendarNow(Application $app) {
		$cal = new \RenderCalendar();
		$params = new EventFilterParams($cal->getEventRepositoryBuilder());
		$params->setHasDateControls(false);
		$params->setSpecifiedUserControls(true, userGetCurrent(), true);
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
				'showCurrentUserOptions' => true,
			));
	}
	
	function calendar($year, $month, Application $app) {
		
		$cal = new \RenderCalendar();
		$params = new EventFilterParams($cal->getEventRepositoryBuilder());
		$params->setHasDateControls(false);
		$params->setSpecifiedUserControls(true, userGetCurrent(), true);
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
				'showCurrentUserOptions' => true,
			));
	}
	
	function listNotifications(Application $app) {
	
		$rb = new UserNotificationRepositoryBuilder($app['extensions']);
		$rb->setLimit(20);
		$rb->setUser(userGetCurrent());
		
		$notifications = $rb->fetchAll();
		
		
			return $app['twig']->render('/index/currentuser/notifications.html.twig', array(
				'notifications'=>$notifications,
			));
	}
	
	function listNotificationsJson(Application $app) {
	
		$rb = new UserNotificationRepositoryBuilder($app['extensions']);
		$rb->setIsOpenBySysAdminsOnly(true);
		$rb->setLimit(20);
		$rb->setUser(userGetCurrent());
		
		$notifications = $rb->fetchAll();
		
		$timeSinceInWordsExtension = new TimeSinceInWordsExtension($app);
		
		$out = array();
		foreach($notifications as $notification) {
			$out[] = array(
				'id'=>$notification->getId(),
				'text'=>$notification->getNotificationText(),
				'read'=>$notification->getIsRead(),
				'timesince'=>$timeSinceInWordsExtension->timeSinceInWords($notification->getCreatedAt()),
				'site'=>array(
					'slug'=>$notification->getSite()->getSlug(),
					'title'=>$notification->getSite()->getTitle(),
				),
			);
		}
		
		return json_encode(array('notifications'=>$out));
		
	}
	
	function showNotification($id, Application $app) {

		// get
		$repo = new UserNotificationRepository();
		$notification = $repo->loadByIdForUser($id, userGetCurrent());

		// Mark read
		$repo->markRead($notification);
		
		// Redirect
		$url = $notification->getNotificationURL();
		return $app->redirect($url);
		
	}
	
}

