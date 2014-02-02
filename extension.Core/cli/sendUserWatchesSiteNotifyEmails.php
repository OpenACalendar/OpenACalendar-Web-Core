<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/extension.Core/php/autoload.php';
require_once APP_ROOT_DIR.'/extension.Core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$actuallySend = isset($argv[1]) && strtolower($argv[1]) == 'yes';
print "Actually Send: ". ($actuallySend ? "YES":"nah")."\n";


use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\UserWatchesSiteRepository;
use repositories\UserWatchesSiteStopRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\builders\UserWatchesSiteRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;

$userRepo = new UserAccountRepository();
$siteRepo = new SiteRepository();
$userWatchesSiteRepository = new UserWatchesSiteRepository();
$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();

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
			
			$userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);
			
			print " ... found data\n";
			
			configureAppForSite($site);
			configureAppForUser($user);
			
			$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
			
			$message = \Swift_Message::newInstance();
			$message->setSubject("Changes on ".$site->getTitle());
			$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
			$message->setTo($user->getEmail());
			
			$messageText = $app['twig']->render('email/userWatchesSiteNotifyEmail.txt.twig', array(
				'user'=>$user,
				'histories'=>$histories,
				'stopCode'=>$userWatchesSiteStop->getAccessKey(),
				'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
			));
			if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSiteNotifyEmail.txt', $messageText);
			$message->setBody($messageText);
			
			$messageHTML = $app['twig']->render('email/userWatchesSiteNotifyEmail.html.twig', array(
				'user'=>$user,
				'histories'=>$histories,
				'stopCode'=>$userWatchesSiteStop->getAccessKey(),
				'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
			));
			if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSiteNotifyEmail.html', $messageHTML);
			$message->addPart($messageHTML,'text/html');
			
			if ($actuallySend) {
				print " ... sending\n";
				if (!$CONFIG->isDebug) {
					$mailer = getSwiftMailer();
					$mailer->send($message);	
				}
				$userWatchesSiteRepository->markNotifyEmailSent($userWatchesSite, $checkTime);
			}
		}
		
	}
	
}
