<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

print "Starting ".date("c")."\n";


$actuallySend = isset($argv[1]) && strtolower($argv[1]) == 'yes';
if (!$actuallySend) {
	die("Flag not set, exiting with no work done\n");
}

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

$userNotificationType = $app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesGroupNotify');

$b = new UserWatchesSiteRepositoryBuilder();
foreach($b->fetchAll() as $userWatchesSite) {

	$user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
	$site = $siteRepo->loadById($userWatchesSite->getSiteId());
		
	print date("c")." User ".$user->getEmail()." Site ".$site->getTitle()."\n";
	
	// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
	if ($userWatchesSite->getIsWatching() && $user->getIsCanSendNormalEmails() && $user->getIsEmailWatchNotify()) {

		print " ... searching for data\n";
		
		$dateSince = $userWatchesSite->getSinceDateForNotifyChecking();
		$checkTime = TimeSource::getDateTime();
		
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
			
			print " ... found data\n";
			
			///// Notification Class 
			$userNotification = $userNotificationType->getNewNotification($user, $site, true);
			
			////// Save Notification Class
			$userNotificationRepo->create($userNotification);
			
			////// Send Email
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
			

			
			if ($actuallySend) {
				print " ... sending\n";
				if (!$CONFIG->isDebug) {
					$app['mailer']->send($message);	
				}
				$userWatchesSiteRepository->markNotifyEmailSent($userWatchesSite, $checkTime);
				$userNotificationRepo->markEmailed($userNotification);
			}
		}
		
	}
	
}

print "Finished ".date("c")."\n";
