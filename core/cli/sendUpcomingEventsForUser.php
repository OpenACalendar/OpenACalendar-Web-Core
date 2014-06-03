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

use repositories\builders\UserAccountRepositoryBuilder;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\UserNotificationRepository;

$userRepoBuilder = new UserAccountRepositoryBuilder();
$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
$userNotificationRepo = new UserNotificationRepository();

/** @var usernotifications/UpcomingEventsUserNotificationType **/
$userNotificationType = $app['extensions']->getCoreExtension()->getUserNotificationType('UpcomingEvents');

foreach($userRepoBuilder->fetchAll() as $user) {
	
	print date("c")." User ".$user->getEmail()."\n";
	if (!$user->getIsCanSendNormalEmails()) {
		print " ... can't send normal emails for some reason\n";
	} else if ($user->getEmailUpcomingEvents() == 'n') {
		print " ... email turned off\n";
	} else {
		print " ... searching\n";
		list($upcomingEvents, $allEvents, $userAtEvent, $flag) = $user->getDataForUpcomingEventsEmail();
		if ($flag) {
			print " ... found data\n";
			
			/**  Notification Class 
			 * @var usernotifications/UpcomingEventsUserNotificationModel **/
			$userNotification = $userNotificationType->getNewNotification($user, null, true);
			$userNotification->setUpcomingEvents($upcomingEvents);
			$userNotification->setAllEvents($allEvents);
			
			////// Save Notification Class
			$userNotificationRepo->create($userNotification);
			
			////// Send Email
			configureAppForUser($user);
			
			$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
			$unsubscribeURL = $CONFIG->getWebIndexDomainSecure().'/you/emails/'.$user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();
						
			$message = \Swift_Message::newInstance();
			$message->setSubject("Events coming up");
			$message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
			$message->setTo($user->getEmail());
			
			$messageText = $app['twig']->render('email/upcomingEventsForUser.txt.twig', array(
				'user'=>$user,
				'upcomingEvents'=>$upcomingEvents,
				'allEvents'=>$allEvents,
				'userAtEvent'=>$userAtEvent,
				'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
				'currentTimeZone'=>'Europe/London',
				'unsubscribeURL'=>$unsubscribeURL,
			));
			if ($CONFIG->isDebug) file_put_contents('/tmp/upcomingEventsForUser.txt', $messageText);
			$message->setBody($messageText);
			
			$messageHTML = $app['twig']->render('email/upcomingEventsForUser.html.twig', array(
				'user'=>$user,
				'upcomingEvents'=>$upcomingEvents,
				'allEvents'=>$allEvents,
				'userAtEvent'=>$userAtEvent,
				'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
				'currentTimeZone'=>'Europe/London',
				'unsubscribeURL'=>$unsubscribeURL,
			));
			if ($CONFIG->isDebug) file_put_contents('/tmp/upcomingEventsForUser.html', $messageHTML);
			$message->addPart($messageHTML,'text/html');
						
			$headers = $message->getHeaders();
			$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);
			
			if ($actuallySend) {
				print " ... sending\n";
				if (!$CONFIG->isDebug) {
					$app['mailer']->send($message);	
				}
				$userNotificationRepo->markEmailed($userNotification);
			}
			
		}
	}
	
}


print "Finished ".date("c")."\n";
