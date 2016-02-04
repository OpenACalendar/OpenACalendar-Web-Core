<?php

namespace tasks;

use Silex\Application;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\UserNotificationRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUpcomingEventsForUsersTask  extends \BaseTask  {



	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'SendUpcomingEventsForUsers';
	}

	public function getShouldRunAutomaticallyNow() {
		return !$this->hasRunToday();
	}

	public function getCanRunManuallyNow() {
		return !$this->hasRunToday();
	}

	protected function run() {

		$userRepoBuilder = new UserAccountRepositoryBuilder($this->app);
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository($this->app);
		$userNotificationRepo = new UserNotificationRepository($this->app);

		/** @var usernotifications/UpcomingEventsUserNotificationType **/
		$userNotificationType = $this->app['extensions']->getCoreExtension()->getUserNotificationType('UpcomingEvents');

		configureAppForThemeVariables(null);

		foreach($userRepoBuilder->fetchAll() as $user) {

			$this->logVerbose(" User ".$user->getEmail() );

			$this->logVerbose(" ... searching" );
			list($upcomingEvents, $allEvents, $userAtEvent, $flag) = $user->getDataForUpcomingEventsEmail();
			if ($flag) {
				$this->logVerbose(" ... found data");

				/**  Notification Class 
				 * @var usernotifications/UpcomingEventsUserNotificationModel **/
				$userNotification = $userNotificationType->getNewNotification($user, null);
				$userNotification->setUpcomingEvents($upcomingEvents);
				$userNotification->setAllEvents($allEvents);

				////// Save Notification Class
				$userNotificationRepo->create($userNotification);

				////// Send Email
				if ($userNotification->getIsEmail()) {
					
					configureAppForUser($user);

					$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
					$unsubscribeURL = $this->app['config']->getWebIndexDomainSecure().'/you/emails/'.$user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();

					$message = \Swift_Message::newInstance();
					$message->setSubject("Events coming up");
					$message->setFrom(array($this->app['config']->emailFrom => $this->app['config']->emailFromName));
					$message->setTo($user->getEmail());

					$messageText = $this->app['twig']->render('email/upcomingEventsForUser.txt.twig', array(
						'user'=>$user,
						'upcomingEvents'=>$upcomingEvents,
						'allEvents'=>$allEvents,
						'userAtEvent'=>$userAtEvent,
						'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
						'currentTimeZone'=>'Europe/London',
						'unsubscribeURL'=>$unsubscribeURL,
					));
					if ($this->app['config']->isDebug) file_put_contents('/tmp/upcomingEventsForUser.txt', $messageText);
					$message->setBody($messageText);

					$messageHTML = $this->app['twig']->render('email/upcomingEventsForUser.html.twig', array(
						'user'=>$user,
						'upcomingEvents'=>$upcomingEvents,
						'allEvents'=>$allEvents,
						'userAtEvent'=>$userAtEvent,
						'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
						'currentTimeZone'=>'Europe/London',
						'unsubscribeURL'=>$unsubscribeURL,
					));
					if ($this->app['config']->isDebug) file_put_contents('/tmp/upcomingEventsForUser.html', $messageHTML);
					$message->addPart($messageHTML,'text/html');

					$headers = $message->getHeaders();
					$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);

					$this->logVerbose( " ... sending" );
					if (!$this->app['config']->isDebug) {
						$this->app['mailer']->send($message);
					}
					$userNotificationRepo->markEmailed($userNotification);
					
				}
			}

		}


		return array('result'=>'ok');
	}
	
}

