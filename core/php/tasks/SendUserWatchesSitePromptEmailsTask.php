<?php

namespace tasks;

use Silex\Application;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\UserWatchesSiteRepository;
use repositories\UserWatchesSiteStopRepository;
use repositories\EventRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\builders\UserWatchesSiteRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;
use repositories\UserNotificationRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesSitePromptEmailsTask {

	public static function run(Application $app, $verbose = false) {
		global $CONFIG;
		
		if ($verbose) print "Starting ".date("c")."\n";
		
		$userRepo = new UserAccountRepository();
		$siteRepo = new SiteRepository();
		$eventRepo = new EventRepository();
		$userWatchesSiteRepository = new UserWatchesSiteRepository();
		$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
		$userNotificationRepo = new UserNotificationRepository();

		/** @var usernotifications/UserWatchesSiteGroupPromptNotificationType **/
		$userNotificationType = $app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesSitePrompt');

		$b = new UserWatchesSiteRepositoryBuilder();
		foreach($b->fetchAll() as $userWatchesSite) {

			$user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
			$site = $siteRepo->loadById($userWatchesSite->getSiteId());

			if ($verbose) print date("c")." User ".$user->getEmail()." Site ".$site->getTitle()."\n";

			// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
			if ($userWatchesSite->getIsWatching()) {

				if ($verbose) print " ... searching for data\n";

				$lastEvent = $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId());
				$data = $userWatchesSite->getPromptEmailData($site, $lastEvent);


				if ($data['moreEventsNeeded']) {


					if ($verbose) print " ... found data\n";

					///// Notification Class 
					$userNotification = $userNotificationType->getNewNotification($user, $site);

					////// Save Notification Class
					$userNotificationRepo->create($userNotification);

					////// Send Email
					if ($userNotification->getIsEmail()) {
						$userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);

						configureAppForSite($site);
						configureAppForUser($user);

						$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
						$unsubscribeURL = $CONFIG->getWebIndexDomainSecure().'/you/emails/'.$user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();

						$lastEventsBuilder = new EventRepositoryBuilder();
						$lastEventsBuilder->setSite($site);
						$lastEventsBuilder->setOrderByStartAt(true);
						$lastEventsBuilder->setIncludeDeleted(false);
						$lastEventsBuilder->setIncludeImported(false);
						$lastEventsBuilder->setLimit($CONFIG->userWatchesGroupPromptEmailShowEvents);
						$lastEvents = $lastEventsBuilder->fetchAll();

						$message = \Swift_Message::newInstance();
						$message->setSubject("Any news about ".$site->getTitle()."?");
						$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
						$message->setTo($user->getEmail());

						$messageText = $app['twig']->render('email/userWatchesSitePromptEmail.txt.twig', array(
							'user'=>$user,
							'lastEvents'=>$lastEvents,
							'stopCode'=>$userWatchesSiteStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSitePromptEmail.txt', $messageText);
						$message->setBody($messageText);

						$messageHTML = $app['twig']->render('email/userWatchesSitePromptEmail.html.twig', array(
							'user'=>$user,
							'lastEvents'=>$lastEvents,
							'stopCode'=>$userWatchesSiteStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSitePromptEmail.html', $messageHTML);
						$message->addPart($messageHTML,'text/html');

						$headers = $message->getHeaders();
						$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);


						if ($verbose) print " ... sending\n";
						if (!$CONFIG->isDebug) {
							$app['mailer']->send($message);	
						}
						$userNotificationRepo->markEmailed($userNotification);
					}
					$userWatchesSiteRepository->markPromptEmailSent($userWatchesSite, $data['checkTime']);


				}

			}

		}

		if ($verbose) print "Finished ".date("c")."\n";

	}
	
}

