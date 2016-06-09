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
                        $unsubscribeURL = $this->app['config']->getWebIndexDomainSecure().'/you/emails/'.
                            $user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();

						$futureEventsBuilder = new EventRepositoryBuilder($this->app);
						$futureEventsBuilder->setSite($site);
						$futureEventsBuilder->setOrderByStartAt(false);
						$futureEventsBuilder->setIncludeDeleted(false);
						$futureEventsBuilder->setIncludeImported(true);
						$futureEventsBuilder->setLimit($this->app['config']->userWatchesSitePromptEmailShowEventsMax);
						$futureEvents = $futureEventsBuilder->fetchAll();

						$message = \Swift_Message::newInstance();
						$message->setFrom(array($this->app['config']->emailFrom => $this->app['config']->emailFromName));
						$message->setTo($user->getEmail());

						$templateData = array(
							'site' => $site,
							'user'=>$user,
							'futureEvents'=>$futureEvents,
							'stopCode'=>$userWatchesSiteStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						);

						$messageSubject = $this->app['twig']->render('email/userWatchesSitePromptEmail.subject.twig', $templateData);
						if ($this->app['config']->isDebug) {
							file_put_contents('/tmp/userWatchesSitePromptEmail.subject', $messageSubject);
						}
						$message->setSubject(trim($messageSubject));

						$message->setSubject($messageSubject);
						$messageText = $this->app['twig']->render('email/userWatchesSitePromptEmail.txt.twig', $templateData);
						if ($this->app['config']->isDebug) {
							file_put_contents('/tmp/userWatchesSitePromptEmail.txt', $messageText);
						}
						$message->setBody($messageText);

						$messageHTML = $this->app['twig']->render('email/userWatchesSitePromptEmail.html.twig', $templateData);
						if ($this->app['config']->isDebug) {
							file_put_contents('/tmp/userWatchesSitePromptEmail.html', $messageHTML);
						}
						$message->addPart($messageHTML,'text/html');

						$headers = $message->getHeaders();
						$headers->addTextHeader('List-Unsubscribe', '<'.$this->app['config']->getWebIndexDomainSecure().'/you/listunsub/'.
                            $user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey().'>');


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

