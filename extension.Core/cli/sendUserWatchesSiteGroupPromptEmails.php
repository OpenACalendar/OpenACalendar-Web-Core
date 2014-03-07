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
use repositories\builders\GroupRepositoryBuilder;

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
	// to avoid flooding user we only send one group email per run
	$anyGroupEmailsSent = false;	
	
	print date("c")." User ".$user->getEmail()." Site ".$site->getTitle()."\n";
	
	// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
	if ($userWatchesSite->getIsWatching() && $user->getIsCanSendNormalEmails() && $user->getIsEmailWatchPrompt()) {
		
		$groupRepoBuilder = new GroupRepositoryBuilder();
		$groupRepoBuilder->setSite($site);
		foreach($groupRepoBuilder->fetchAll() as $group) {

			if (!$anyGroupEmailsSent) {

				print " ... searching group ".$group->getSlug()." for data\n";

				$lastEvent = $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId());
				$data = $userWatchesSite->getGroupPromptEmailData($site, $group, $lastEvent);

				if ($data['moreEventsNeeded']) {


					print " ... found data \n";

					$userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);

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
					$lastEventsBuilder->setLimit($CONFIG->userWatchesSiteGroupPromptEmailShowEvents);
					$lastEvents = $lastEventsBuilder->fetchAll();

					$message = \Swift_Message::newInstance();
					$message->setSubject("Any news about ".$group->getTitle()."?");
					$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
					$message->setTo($user->getEmail());

					$messageText = $app['twig']->render('email/userWatchesSiteGroupPromptEmail.txt.twig', array(
						'user'=>$user,
						'group'=>$group,
						'lastEvents'=>$lastEvents,
						'stopCode'=>$userWatchesSiteStop->getAccessKey(),
						'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
						'unsubscribeURL'=>$unsubscribeURL,
					));
					if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSiteGroupPromptEmail.txt', $messageText);
					$message->setBody($messageText);

					$messageHTML = $app['twig']->render('email/userWatchesSiteGroupPromptEmail.html.twig', array(
						'user'=>$user,
						'group'=>$group,
						'lastEvents'=>$lastEvents,
						'stopCode'=>$userWatchesSiteStop->getAccessKey(),
						'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
						'unsubscribeURL'=>$unsubscribeURL,
					));
					if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSiteGroupPromptEmail.html', $messageHTML);
					$message->addPart($messageHTML,'text/html');
						
					$headers = $message->getHeaders();
					$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);
			

								
					if ($actuallySend) {
						print " ... sending\n";
						if (!$CONFIG->isDebug) {
							$mailer = getSwiftMailer();
							$mailer->send($message);	
						}
						$userWatchesSiteRepository->markGroupPromptEmailSent($userWatchesSite, $group, $data['checkTime']);
					}
					
					$anyGroupEmailsSent = true;

				}
		
			}
		}
		
	}
	
}
