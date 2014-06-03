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

$siteRepo = new SiteRepository();
$groupRepo = new GroupRepository();
$importURLRepo = new ImportURLRepository();
$userRepo = new UserAccountRepository();
$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
$userWatchesGroupStopRepository = new UserWatchesGroupStopRepository();
$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();

$iurlBuilder = new ImportURLRepositoryBuilder();

foreach($iurlBuilder->fetchAll() as $importURL) {

	$site = $siteRepo->loadById($importURL->getSiteID());
	
	print date("c")." ImportURL ".$importURL->getId()." ".$importURL->getTitle()." Site ".$site->getTitle()."\n";
	
	if (!$site->getIsFeatureImporter()) {
		print " - site feature disabled\n";
	} else if ($importURL->getExpiredAt()) {
		print " - expired\n";
	} else if (!$importURL->getIsEnabled()) {
		print " - not enabled\n";
	} else if ($importURL->isShouldExpireNow()) {
		print " - expiring\n";
		$importURLRepo->expire($importURL);

		configureAppForSite($site);
		
		$uwsb = new UserWatchesSiteRepositoryBuilder();
		$uwsb->setSite($site);
		foreach ($uwsb->fetchAll() as $userWatchesSite) {
			$user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
			if ($user->getIsEmailVerified() && $user->getIsEmailWatchImportExpired()) { 
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
			}
		}
		
		if ($importURL->getGroupId()) {
			$group = $groupRepo->loadById($importURL->getGroupId());
			$uwgb = new UserWatchesGroupRepositoryBuilder();
			$uwgb->setGroup($group);
			foreach ($uwgb->fetchAll() as $userWatchesGroup) {
				$user = $userRepo->loadByID($userWatchesGroup->getUserAccountId());
				if ($user->getIsEmailVerified() && $user->getIsEmailWatchImportExpired()) { 
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
						$mailer = getSwiftMailer();
						$mailer->send($message);
					}		
				}
			}
		}
		
		
	} else {
		$lastRunDate = $importURLRepo->getLastRunDateForImportURL($importURL);
		$nowDate = \TimeSource::getDateTime();
		if (!$lastRunDate || ($lastRunDate->getTimestamp() < $nowDate->getTimestamp() - $CONFIG->importURLSecondsBetweenImports)) {
			print " - importing\n";
			$runner = new ImportURLRunner();
			$runner->go($importURL);
		} else {
			print " - already done on ".$lastRunDate->format("c")."\n";
		}
	}
	
	
}

print "Finished ".date("c")."\n";
