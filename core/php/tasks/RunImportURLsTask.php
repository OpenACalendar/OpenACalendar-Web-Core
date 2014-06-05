<?php

namespace tasks;


use repositories\builders\ImportURLRepositoryBuilder;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportURLRepository;
use import\ImportURLRunner;
use repositories\builders\UserWatchesGroupRepositoryBuilder;
use repositories\builders\UserWatchesSiteRepositoryBuilder;
use repositories\UserAccountRepository;
use repositories\UserWatchesSiteStopRepository;
use repositories\UserWatchesGroupStopRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\UserNotificationRepository;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RunImportURLsTask {

	public static function run(Application $app, $verbose = false) {
		global $CONFIG;

		if ($verbose) print "Starting ".date("c")."\n";

		$siteRepo = new SiteRepository();
		$groupRepo = new GroupRepository();
		$importURLRepo = new ImportURLRepository();
		$userRepo = new UserAccountRepository();
		$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
		$userWatchesGroupStopRepository = new UserWatchesGroupStopRepository();
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
		$userNotificationRepo = new UserNotificationRepository();

		/** @var usernotifications/UpcomingEventsUserNotificationType **/
		$userNotificationType = $app['extensions']->getCoreExtension()->getUserNotificationType('ImportURLExpired');

		$iurlBuilder = new ImportURLRepositoryBuilder();

		foreach($iurlBuilder->fetchAll() as $importURL) {

			$site = $siteRepo->loadById($importURL->getSiteID());

			if ($verbose) print date("c")." ImportURL ".$importURL->getId()." ".$importURL->getTitle()." Site ".$site->getTitle()."\n";

			if (!$site->getIsFeatureImporter()) {
				if ($verbose) print " - site feature disabled\n";
			} else if ($importURL->getExpiredAt()) {
				if ($verbose) print " - expired\n";
			} else if (!$importURL->getIsEnabled()) {
				if ($verbose) print " - not enabled\n";
			} else if ($importURL->isShouldExpireNow()) {
				if ($verbose) print " - expiring\n";
				$importURLRepo->expire($importURL);

				configureAppForSite($site);

				$uwsb = new UserWatchesSiteRepositoryBuilder();
				$uwsb->setSite($site);
				foreach ($uwsb->fetchAll() as $userWatchesSite) {
					$user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
					if ($userWatchesSite->getIsWatching()) { 
						
						/// Notification Class 
						$userNotification = $userNotificationType->getNewNotification($user, $site);
						$userNotification->setImportURL($importURL);

						////// Save Notification Class
						$userNotificationRepo->create($userNotification);

						////// Send Email
						if ($userNotification->getIsEmail()) {
						
							configureAppForUser($user);
							$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
							$userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);

							$message = \Swift_Message::newInstance();
							$message->setSubject("Please confirm this is still valid: ".$importURL->getTitle());
							$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
							$message->setTo($user->getEmail());

							$messageText = $app['twig']->render('email/importURLExpired.watchesSite.txt.twig', array(
								'user'=>$user,
								'importurl'=>$importURL,
								'stopCode'=>$userWatchesSiteStop->getAccessKey(),
								'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							));
							if ($CONFIG->isDebug) file_put_contents('/tmp/importURLExpired.watchesSite.txt', $messageText);
							$message->setBody($messageText);

							$messageHTML = $app['twig']->render('email/importURLExpired.watchesSite.html.twig', array(
								'user'=>$user,
								'importurl'=>$importURL,
								'stopCode'=>$userWatchesSiteStop->getAccessKey(),
								'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							));
							if ($CONFIG->isDebug) file_put_contents('/tmp/importURLExpired.watchesSite.html', $messageHTML);
							$message->addPart($messageHTML,'text/html');

							if (!$CONFIG->isDebug) {
								$app['mailer']->send($message);
							}		
							$userNotificationRepo->markEmailed($userNotification);
							
						}
					}
				}

				if ($importURL->getGroupId()) {
					$group = $groupRepo->loadById($importURL->getGroupId());
					$uwgb = new UserWatchesGroupRepositoryBuilder();
					$uwgb->setGroup($group);
					foreach ($uwgb->fetchAll() as $userWatchesGroup) {
						$user = $userRepo->loadByID($userWatchesGroup->getUserAccountId());
						if ($userWatchesGroup->getIsWatching()) { 
							/// Notification Class 
							$userNotification = $userNotificationType->getNewNotification($user, $site);
							$userNotification->setGroup($group);

							////// Save Notification Class
							$userNotificationRepo->create($userNotification);

							////// Send Email
							if ($userNotification->getIsEmail()) {
								$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
								$userWatchesGroupStop = $userWatchesGroupStopRepository->getForUserAndGroup($user, $group);

								$message = \Swift_Message::newInstance();
								$message->setSubject("Please confirm this is still valid: ".$importURL->getTitle());
								$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
								$message->setTo($user->getEmail());

								$messageText = $app['twig']->render('email/importURLExpired.watchesGroup.txt.twig', array(
									'user'=>$user,
									'importurl'=>$importURL,
									'stopCode'=>$userWatchesGroupStop->getAccessKey(),
									'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
									'group'=>$group,
								));
								if ($CONFIG->isDebug) file_put_contents('/tmp/importURLExpired.watchesGroup.txt', $messageText);
								$message->setBody($messageText);

								$messageHTML = $app['twig']->render('email/importURLExpired.watchesGroup.html.twig', array(
									'user'=>$user,
									'importurl'=>$importURL,
									'stopCode'=>$userWatchesGroupStop->getAccessKey(),
									'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
									'group'=>$group,
								));
								if ($CONFIG->isDebug) file_put_contents('/tmp/importURLExpired.watchesGroup.html', $messageHTML);
								$message->addPart($messageHTML,'text/html');

								if (!$CONFIG->isDebug) {
									$app['mailer']->send($message);
								}	
								$userNotificationRepo->markEmailed($userNotification);	
							}
						}
					}
				}


			} else {
				$lastRunDate = $importURLRepo->getLastRunDateForImportURL($importURL);
				$nowDate = \TimeSource::getDateTime();
				if (!$lastRunDate || ($lastRunDate->getTimestamp() < $nowDate->getTimestamp() - $CONFIG->importURLSecondsBetweenImports)) {
					if ($verbose) print " - importing\n";
					$runner = new ImportURLRunner();
					$runner->go($importURL);
				} else {
					if ($verbose) print " - already done on ".$lastRunDate->format("c")."\n";
				}
			}


		}

		if ($verbose) print "Finished ".date("c")."\n";
		
	}
	
}

