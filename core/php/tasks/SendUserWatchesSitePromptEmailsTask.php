<?php

namespace tasks;

use repositories\UserHasNoEditorPermissionsInSiteRepository;
use repositories\UserPermissionsRepository;
use Silex\Application;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\UserWatchesSiteRepository;
use repositories\UserWatchesSiteStopRepository;
use repositories\EventRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\builders\UserWatchesSiteRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;
use repositories\UserNotificationRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesSitePromptEmailsTask  extends \BaseTask  {



	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'SendUserWatchesSitePromptEmails';
	}

	public function getShouldRunAutomaticallyNow() {
		return !$this->hasRunToday();
	}

	public function getCanRunManuallyNow() {
		return !$this->hasRunToday();
	}

	protected function run() {

		$userRepo = new UserAccountRepository($this->app);
		$siteRepo = new SiteRepository($this->app);
		$eventRepo = new EventRepository($this->app);
		$userWatchesSiteRepository = new UserWatchesSiteRepository($this->app);
		$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository($this->app);
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository($this->app);
		$userNotificationRepo = new UserNotificationRepository($this->app);
		$userHasNoEditorPermissionsInSiteRepo = new UserHasNoEditorPermissionsInSiteRepository($this->app);
		$userPermissionsRepo = new UserPermissionsRepository($this->app);

		/** @var usernotifications/UserWatchesSiteGroupPromptNotificationType **/
		$userNotificationType = $this->app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesSitePrompt');

		$b = new UserWatchesSiteRepositoryBuilder($this->app);
		foreach($b->fetchAll() as $userWatchesSite) {

			$user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
			$site = $siteRepo->loadById($userWatchesSite->getSiteId());
			$siteRepo->loadLegacyFeaturesOnSite($site);
			// This is not the most efficient as it involves DB access and the results might not be used. But it'll do for now.
			$userPermissions = $userPermissionsRepo->getPermissionsForUserInSite($user, $site, false, true);

			$this->logVerbose(" User ".$user->getEmail()." Site ".$site->getTitle() );

			if ($site->getIsClosedBySysAdmin()) {
				$this->logVerbose(" ... site is closed");
			} else if ($userHasNoEditorPermissionsInSiteRepo->isUserInSite($user, $site)) {
				$this->logVerbose( " ... user does not have edit permissions allowed in site");
			} else if (!$userPermissions->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
				$this->logVerbose( " ... user does not have org.openacalendar/CALENDAR_CHANGE permission in site");
			// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
			} else if ($userWatchesSite->getIsWatching()) {

				$this->logVerbose( " ... searching for data");

				$lastEvent = $eventRepo->loadLastNonDeletedNonImportedByStartTimeInSiteId($site->getId());
				$data = $userWatchesSite->getPromptEmailData($site, $lastEvent);


				if ($data['moreEventsNeeded']) {


					$this->logVerbose( " ... found data");

					///// Notification Class 
					$userNotification = $userNotificationType->getNewNotification($user, $site);

					////// Save Notification Class
					$userNotificationRepo->create($userNotification);

					////// Send Email
					if ($userNotification->getIsEmail()) {
						$userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);

						configureAppForSite($site);
						configureAppForUser($user);

						$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
						$unsubscribeURL = $this->app['config']->getWebIndexDomainSecure().'/you/emails/'.$user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();

						$lastEventsBuilder = new EventRepositoryBuilder($this->app);
						$lastEventsBuilder->setSite($site);
						$lastEventsBuilder->setOrderByStartAt(true);
						$lastEventsBuilder->setIncludeDeleted(false);
						$lastEventsBuilder->setIncludeImported(false);
						$lastEventsBuilder->setLimit($this->app['config']->userWatchesGroupPromptEmailShowEvents);
						$lastEvents = $lastEventsBuilder->fetchAll();

						$message = \Swift_Message::newInstance();
						$message->setSubject("Any news about ".$site->getTitle()."?");
						$message->setFrom(array($this->app['config']->emailFrom => $this->app['config']->emailFromName));
						$message->setTo($user->getEmail());

						$messageText = $this->app['twig']->render('email/userWatchesSitePromptEmail.txt.twig', array(
							'user'=>$user,
							'lastEvents'=>$lastEvents,
							'stopCode'=>$userWatchesSiteStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($this->app['config']->isDebug) file_put_contents('/tmp/userWatchesSitePromptEmail.txt', $messageText);
						$message->setBody($messageText);

						$messageHTML = $this->app['twig']->render('email/userWatchesSitePromptEmail.html.twig', array(
							'user'=>$user,
							'lastEvents'=>$lastEvents,
							'stopCode'=>$userWatchesSiteStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($this->app['config']->isDebug) file_put_contents('/tmp/userWatchesSitePromptEmail.html', $messageHTML);
						$message->addPart($messageHTML,'text/html');

						$headers = $message->getHeaders();
						$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);


						$this->logVerbose( " ... sending");
						if (!$this->app['config']->isDebug) {
							$this->app['mailer']->send($message);
						}
						$userNotificationRepo->markEmailed($userNotification);
					}
					$userWatchesSiteRepository->markPromptEmailSent($userWatchesSite, $data['checkTime']);


				}

			}

		}


		return array('result'=>'ok');
	}
	
}

