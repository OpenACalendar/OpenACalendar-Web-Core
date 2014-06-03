<?php

namespace tasks;


use Silex\Application;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\UserWatchesSiteRepository;
use repositories\UserWatchesGroupRepository;
use repositories\UserWatchesSiteStopRepository;
use repositories\UserWatchesGroupStopRepository;
use repositories\EventRepository;
use repositories\GroupRepository;
use repositories\builders\UserWatchesGroupRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\UserNotificationRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesGroupPromptEmailsTask {

	public static function run(Application $app, $verbose = false) {
		
		if ($verbose) print "Starting ".date("c")."\n";
		
		$userRepo = new UserAccountRepository();
		$siteRepo = new SiteRepository();
		$groupRepo = new GroupRepository();
		$eventRepo = new EventRepository();
		$userWatchesGroupRepository = new UserWatchesGroupRepository();
		$userWatchesGroupStopRepository = new UserWatchesGroupStopRepository();
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
		$userNotificationRepo = new UserNotificationRepository();

		/** @var usernotifications/UserWatchesGroupPromptNotificationType **/
		$userNotificationType = $app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesGroupPrompt');

		$b = new UserWatchesGroupRepositoryBuilder();
		foreach($b->fetchAll() as $userWatchesGroup) {

			$user = $userRepo->loadByID($userWatchesGroup->getUserAccountId());
			$group = $groupRepo->loadById($userWatchesGroup->getGroupId());
			$site = $siteRepo->loadById($group->getSiteID());

			if ($verbose) print date("c")." User ".$user->getEmail()." Site ".$site->getTitle()." Group ".$group->getTitle()."\n";

			// UserWatchesGroupRepositoryBuilder() should only return instances where site is not also watched

			// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
			if ($userWatchesGroup->getIsWatching() && $user->getIsCanSendNormalEmails() && $user->getIsEmailWatchPrompt()) {

				if ($verbose) print " ... searching for data\n";


				$lastEvent = $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId());
				$data = $userWatchesGroup->getPromptEmailData($site, $lastEvent);


				if ($data['moreEventsNeeded']) {


					if ($verbose) print " ... found data\n";

					///// Notification Class 
					$userNotification = $userNotificationType->getNewNotification($user, $site, true);
					$userNotification->setGroup($group);

					////// Save Notification Class
					$userNotificationRepo->create($userNotification);

					////// Send Email
					$userWatchesGroupStop = $userWatchesGroupStopRepository->getForUserAndGroup($user, $group);

					configureAppForSite($site);
					configureAppForUser($user);

					$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
					$unsubscribeURL = $CONFIG->getWebIndexDomainSecure().'/you/emails/'.$user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();

					$lastEventsBuilder = new EventRepositoryBuilder();
					$lastEventsBuilder->setSite($site);
					$lastEventsBuilder->setGroup($group);
					$lastEventsBuilder->setOrderByStartAt(true);
					$lastEventsBuilder->setIncludeDeleted(false);			
					$lastEventsBuilder->setIncludeImported(false);
					$lastEventsBuilder->setLimit($CONFIG->userWatchesGroupPromptEmailShowEvents);
					$lastEvents = $lastEventsBuilder->fetchAll();

					$message = \Swift_Message::newInstance();
					$message->setSubject("Any news about ".$group->getTitle()."?");
					$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
					$message->setTo($user->getEmail());

					$messageText = $app['twig']->render('email/userWatchesGroupPromptEmail.txt.twig', array(
						'group'=>$group,
						'user'=>$user,
						'lastEvents'=>$lastEvents,
						'stopCode'=>$userWatchesGroupStop->getAccessKey(),
						'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
						'unsubscribeURL'=>$unsubscribeURL,
					));
					if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesGroupPromptEmail.txt', $messageText);
					$message->setBody($messageText);

					$messageHTML = $app['twig']->render('email/userWatchesGroupPromptEmail.html.twig', array(
						'group'=>$group,
						'user'=>$user,
						'lastEvents'=>$lastEvents,
						'stopCode'=>$userWatchesGroupStop->getAccessKey(),
						'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
						'unsubscribeURL'=>$unsubscribeURL,
					));
					if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesGroupPromptEmail.html', $messageHTML);
					$message->addPart($messageHTML,'text/html');

					$headers = $message->getHeaders();
					$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);

					if ($verbose) print " ... sending\n";
					if (!$CONFIG->isDebug) {
						$app['mailer']->send($message);	
					}
					$userWatchesGroupRepository->markPromptEmailSent($userWatchesGroup, $data['checkTime']);
					$userNotificationRepo->markEmailed($userNotification);

				}

			}

		}

		if ($verbose) print "Finished ".date("c")."\n";

	}
	
}

