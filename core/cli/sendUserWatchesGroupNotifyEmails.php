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
print "Actually Send: ". ($actuallySend ? "YES":"nah")."\n";


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

$b = new UserWatchesGroupRepositoryBuilder();
foreach($b->fetchAll() as $userWatchesGroup) {
	
	$user = $userRepo->loadByID($userWatchesGroup->getUserAccountId());
	$group = $groupRepo->loadById($userWatchesGroup->getGroupId());
	$site = $siteRepo->loadById($group->getSiteID());
	
	print date("c")." User ".$user->getEmail()." Group ".$group->getTitle()."\n";
		
	// UserWatchesGroupRepositoryBuilder() should only return instances where site is not also watched
	
	// Technically UserWatchesGroupRepositoryBuilder() should only return getIsWatching() == true but lets double check
	if ($userWatchesGroup->getIsWatching() && $user->getIsCanSendNormalEmails() && $user->getIsEmailWatchNotify()) {

		print " ... searching for data\n";
		
		$dateSince = $userWatchesGroup->getSinceDateForNotifyChecking();
		$checkTime = TimeSource::getDateTime();
		
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
				}
			}
			$userWatchesGroupStop = $userWatchesGroupStopRepository->getForUserAndGroup($user, $group);
			
			print " ... found data\n";
			
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
			
			
			
			if ($actuallySend) {
				print " ... sending\n";
				if (!$CONFIG->isDebug) {
					$app['mailer']->send($message);	
				}
				$userWatchesGroupRepository->markNotifyEmailSent($userWatchesGroup, $checkTime);
			}
		}
		
	}
	
}


print "Finished ".date("c")."\n";
