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
use repositories\EventRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\builders\UserWatchesSiteRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;

$userRepo = new UserAccountRepository();
$siteRepo = new SiteRepository();
$eventRepo = new EventRepository();
$userWatchesSiteRepository = new UserWatchesSiteRepository();
$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();

$b = new UserWatchesSiteRepositoryBuilder();
foreach($b->fetchAll() as $userWatchesSite) {

	$user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
	$site = $siteRepo->loadById($userWatchesSite->getSiteId());
		
	print date("c")." User ".$user->getEmail()." Site ".$site->getTitle()."\n";
	
	// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
	if ($userWatchesSite->getIsWatching() && $user->getIsCanSendNormalEmails() && $user->getIsEmailWatchPrompt()) {

		print " ... searching for data\n";
		
		
		$lastEvent = $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId());
		$data = $userWatchesSite->getPromptEmailData($site, $lastEvent);
		
		
		if ($data['moreEventsNeeded']) {
			
	
			print " ... found data\n";
			
			$userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);
			
			configureAppForSite($site);
			configureAppForUser($user);
			
			$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
			
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
			));
			if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSitePromptEmail.txt', $messageText);
			$message->setBody($messageText);
			
			$messageHTML = $app['twig']->render('email/userWatchesSitePromptEmail.html.twig', array(
				'user'=>$user,
				'lastEvents'=>$lastEvents,
				'stopCode'=>$userWatchesSiteStop->getAccessKey(),
				'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
			));
			if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSitePromptEmail.html', $messageHTML);
			$message->addPart($messageHTML,'text/html');
			
			if ($actuallySend) {
				print " ... sending\n";
				if (!$CONFIG->isDebug) {
					$mailer = getSwiftMailer();
					$mailer->send($message);	
				}
				$userWatchesSiteRepository->markPromptEmailSent($userWatchesSite, $data['checkTime']);
			}
			
		}
		
	}
	
}
