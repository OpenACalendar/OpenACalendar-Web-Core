<?php

namespace tasks;


use Silex\Application;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\UserWatchesSiteRepository;
use repositories\UserWatchesSiteStopRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\builders\UserWatchesSiteRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\AreaHistoryModel;
use models\VenueHistoryModel;
use repositories\EventHistoryRepository;
use repositories\GroupHistoryRepository;
use repositories\AreaHistoryRepository;
use repositories\VenueHistoryRepository;
use repositories\UserNotificationRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesSiteNotifyEmailsTask {

	public static function run(Application $app, $verbose = false) {
		global $CONFIG;
		
		if ($verbose) print "Starting ".date("c")."\n";
		
		$userRepo = new UserAccountRepository();
		$siteRepo = new SiteRepository();
		$userWatchesSiteRepository = new UserWatchesSiteRepository();
		$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
		$eventHistoryRepository =  new EventHistoryRepository;
		$groupHistoryRepository = new GroupHistoryRepository;
		$areaHistoryRepository = new AreaHistoryRepository;
		$venueHistoryRepository = new VenueHistoryRepository;
		$userNotificationRepo = new UserNotificationRepository();

		$userNotificationType = $app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesSiteNotify');

		$b = new UserWatchesSiteRepositoryBuilder();
		foreach($b->fetchAll() as $userWatchesSite) {

			$user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
			$site = $siteRepo->loadById($userWatchesSite->getSiteId());

			if ($verbose) print date("c")." User ".$user->getEmail()." Site ".$site->getTitle()."\n";

			// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
			if ($userWatchesSite->getIsWatching()) {

				if ($verbose) print " ... searching for data\n";

				$dateSince = $userWatchesSite->getSinceDateForNotifyChecking();
				$checkTime = \TimeSource::getDateTime();

				$historyRepositoryBuilder = new HistoryRepositoryBuilder();
				$historyRepositoryBuilder->setSite($site);
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
						}
					}

					$userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);

					if ($verbose) print " ... found data\n";

					///// Notification Class 
					$userNotification = $userNotificationType->getNewNotification($user, $site);

					////// Save Notification Class
					$userNotificationRepo->create($userNotification);

					////// Send Email
					if ($userNotification->getIsEmail()) {
						configureAppForSite($site);
						configureAppForUser($user);

						$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
						$unsubscribeURL = $CONFIG->getWebIndexDomainSecure().'/you/emails/'.$user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();

						$message = \Swift_Message::newInstance();
						$message->setSubject("Changes on ".$site->getTitle());
						$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
						$message->setTo($user->getEmail());

						$messageText = $app['twig']->render('email/userWatchesSiteNotifyEmail.txt.twig', array(
							'user'=>$user,
							'histories'=>$histories,
							'stopCode'=>$userWatchesSiteStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSiteNotifyEmail.txt', $messageText);
						$message->setBody($messageText);

						$messageHTML = $app['twig']->render('email/userWatchesSiteNotifyEmail.html.twig', array(
							'user'=>$user,
							'histories'=>$histories,
							'stopCode'=>$userWatchesSiteStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSiteNotifyEmail.html', $messageHTML);
						$message->addPart($messageHTML,'text/html');

						$headers = $message->getHeaders();
						$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);

						if ($verbose) print " ... sending\n";
						if (!$CONFIG->isDebug) {
							$app['mailer']->send($message);	
						}
						$userNotificationRepo->markEmailed($userNotification);
					}
					$userWatchesSiteRepository->markNotifyEmailSent($userWatchesSite, $checkTime);
				}

			}

		}

		if ($verbose) print "Finished ".date("c")."\n";

	}
	
}

