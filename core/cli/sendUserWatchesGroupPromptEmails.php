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

$userRepo = new UserAccountRepository();
$siteRepo = new SiteRepository();
$groupRepo = new GroupRepository();
$eventRepo = new EventRepository();
$userWatchesGroupRepository = new UserWatchesGroupRepository();
$userWatchesGroupStopRepository = new UserWatchesGroupStopRepository();
$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();

$b = new UserWatchesGroupRepositoryBuilder();
foreach($b->fetchAll() as $userWatchesGroup) {

	$user = $userRepo->loadByID($userWatchesGroup->getUserAccountId());
	$group = $groupRepo->loadById($userWatchesGroup->getGroupId());
	$site = $siteRepo->loadById($group->getSiteID());
		
	print date("c")." User ".$user->getEmail()." Site ".$site->getTitle()." Group ".$group->getTitle()."\n";
	
	// UserWatchesGroupRepositoryBuilder() should only return instances where site is not also watched
	
	// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
	if ($userWatchesGroup->getIsWatching() && $user->getIsCanSendNormalEmails() && $user->getIsEmailWatchPrompt()) {

		print " ... searching for data\n";
		
		
		$lastEvent = $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId());
		$data = $userWatchesGroup->getPromptEmailData($site, $lastEvent);
		
		
		if ($data['moreEventsNeeded']) {
			
	
			print " ... found data\n";
			
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
			
			
			if ($actuallySend) {
				print " ... sending\n";
				if (!$CONFIG->isDebug) {
					$mailer = getSwiftMailer();
					$mailer->send($message);	
				}
				$userWatchesGroupRepository->markPromptEmailSent($userWatchesGroup, $data['checkTime']);
			}
			
			
			
		}
		
	}
	
}

print "Finished ".date("c")."\n";
