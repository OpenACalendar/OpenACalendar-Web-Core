<?php

namespace tasks;

use Silex\Application;
use repositories\GroupRepository;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\UserWatchesGroupRepository;
use repositories\UserWatchesGroupStopRepository;
use repositories\builders\UserWatchesGroupRepositoryBuilder;
use repositories\builders\UserWatchesSiteRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\EventHistoryRepository;
use repositories\GroupHistoryRepository;
use repositories\AreaHistoryRepository;
use repositories\VenueHistoryRepository;
use repositories\UserNotificationRepository;
use repositories\ImportURLHistoryRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesGroupNotifyEmailsTask {

	public static function run(Application $app, $verbose = false) {
		global $CONFIG;
		
		if ($verbose) print "Starting ".date("c")."\n";
		
		$userRepo = new UserAccountRepository();
		$groupRepo = new GroupRepository();
		$siteRepo = new SiteRepository();
		$userWatchesGroupRepository = new UserWatchesGroupRepository();
		$userWatchesGroupStopRepository = new UserWatchesGroupStopRepository();
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
		$eventHistoryRepository =  new EventHistoryRepository;
		$groupHistoryRepository = new GroupHistoryRepository;
		$areaHistoryRepository = new AreaHistoryRepository;
		$venueHistoryRepository = new VenueHistoryRepository;
		$importURLHistoryRepository = new ImportURLHistoryRepository;
		$userNotificationRepo = new UserNotificationRepository();

		$userNotificationType = $app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesGroupNotify');

		$b = new UserWatchesGroupRepositoryBuilder();
		foreach($b->fetchAll() as $userWatchesGroup) {

			$user = $userRepo->loadByID($userWatchesGroup->getUserAccountId());
			$group = $groupRepo->loadById($userWatchesGroup->getGroupId());
			$site = $siteRepo->loadById($group->getSiteID());

			if ($verbose) print date("c")." User ".$user->getEmail()." Group ".$group->getTitle()."\n";

			// UserWatchesGroupRepositoryBuilder() should only return instances where site is not also watched

			if ($site->getIsClosedBySysAdmin()) {
				if ($verbose) print " ... site is closed\n";
			// Technically UserWatchesGroupRepositoryBuilder() should only return getIsWatching() == true but lets double check
			} else if ($userWatchesGroup->getIsWatching()) {

				if ($verbose) print " ... searching for data\n";

				$dateSince = $userWatchesGroup->getSinceDateForNotifyChecking();
				$checkTime = \TimeSource::getDateTime();

				$historyRepositoryBuilder = new HistoryRepositoryBuilder();
				$historyRepositoryBuilder->setGroup($group);
				$historyRepositoryBuilder->setSince($dateSince);
				$historyRepositoryBuilder->setNotUser($user);

				$histories = $historyRepositoryBuilder->fetchAll();

				//var_dump($histories);

				if ($histories) {

					// lets make sure histories are correct
					foreach($histories as $history) {
						if ($history instanceof models\EventHistoryModel) {
							$eventHistoryRepository->ensureChangedFlagsAreSet($history);
						} elseif ($history instanceof models\GroupHistoryModel) {
							$groupHistoryRepository->ensureChangedFlagsAreSet($history);
						} elseif ($history instanceof models\VenueHistoryModel) {
							$venueHistoryRepository->ensureChangedFlagsAreSet($history);
						} elseif ($history instanceof models\AreaHistoryModel) {
							$areaHistoryRepository->ensureChangedFlagsAreSet($history);
						} elseif ($history instanceof models\ImportURLHistoryModel) {
							$importURLHistoryRepository->ensureChangedFlagsAreSet($history);
						}
					}
					$userWatchesGroupStop = $userWatchesGroupStopRepository->getForUserAndGroup($user, $group);

					if ($verbose) print " ... found data\n";

					///// Notification Class 
					$userNotification = $userNotificationType->getNewNotification($user, $site);
					$userNotification->setGroup($group);

					////// Save Notification Class
					$userNotificationRepo->create($userNotification);

					////// Send Email
					if ($userNotification->getIsEmail()) {
						configureAppForSite($site);
						configureAppForUser($user);

						$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
						$unsubscribeURL = $CONFIG->getWebIndexDomainSecure().'/you/emails/'.$user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();

						$message = \Swift_Message::newInstance();
						$message->setSubject("Changes on ".$group->getTitle());
						$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
						$message->setTo($user->getEmail());

						$messageText = $app['twig']->render('email/userWatchesGroupNotifyEmail.txt.twig', array(
							'user'=>$user,
							'group'=>$group,
							'histories'=>$histories,
							'stopCode'=>$userWatchesGroupStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesGroupNotifyEmail.txt', $messageText);
						$message->setBody($messageText);

						$messageHTML = $app['twig']->render('email/userWatchesGroupNotifyEmail.html.twig', array(
							'user'=>$user,
							'group'=>$group,
							'histories'=>$histories,
							'stopCode'=>$userWatchesGroupStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesGroupNotifyEmail.html', $messageHTML);
						$message->addPart($messageHTML,'text/html');

						$headers = $message->getHeaders();
						$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);



						if ($verbose) print " ... sending\n";
						if (!$CONFIG->isDebug) {
							$app['mailer']->send($message);	
						}
						$userNotificationRepo->markEmailed($userNotification);
					}
					$userWatchesGroupRepository->markNotifyEmailSent($userWatchesGroup, $checkTime);
				}

			}

		}


		if ($verbose) print "Finished ".date("c")."\n";

	}
	
}

