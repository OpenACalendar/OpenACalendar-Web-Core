<?php

namespace tasks;


use repositories\UserHasNoEditorPermissionsInSiteRepository;
use repositories\UserPermissionsRepository;
use Silex\Application;
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
use repositories\UserNotificationRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesGroupPromptEmailsTask  extends \BaseTask  {



	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'SendUserWatchesGroupPromptEmails';
	}


	public function getShouldRunAutomaticallyNow() {
		return !$this->hasRunToday();
	}

	public function getCanRunManuallyNow() {
		return !$this->hasRunToday();
	}

	protected function run() {
		global $CONFIG;

		
		$userRepo = new UserAccountRepository();
		$siteRepo = new SiteRepository();
		$groupRepo = new GroupRepository();
		$eventRepo = new EventRepository();
		$userWatchesGroupRepository = new UserWatchesGroupRepository();
		$userWatchesGroupStopRepository = new UserWatchesGroupStopRepository();
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
		$userNotificationRepo = new UserNotificationRepository();
		$userHasNoEditorPermissionsInSiteRepo = new UserHasNoEditorPermissionsInSiteRepository();
		$userPermissionsRepo = new UserPermissionsRepository($this->app['extensions']);

		/** @var usernotifications/UserWatchesGroupPromptNotificationType **/
		$userNotificationType = $this->app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesGroupPrompt');

		$b = new UserWatchesGroupRepositoryBuilder();
		foreach($b->fetchAll() as $userWatchesGroup) {

			$user = $userRepo->loadByID($userWatchesGroup->getUserAccountId());
			$group = $groupRepo->loadById($userWatchesGroup->getGroupId());
			$site = $siteRepo->loadById($group->getSiteID());
			// This is not the most efficient as it involves DB access and the results might not be used. But it'll do for now.
			$userPermissions = $userPermissionsRepo->getPermissionsForUserInSite($user, $site, false, true);

			$this->logVerbose(" User ".$user->getEmail()." Site ".$site->getTitle()." Group ".$group->getTitle() );

			// UserWatchesGroupRepositoryBuilder() should only return instances where site is not also watched

			if ($site->getIsClosedBySysAdmin()) {
				$this->logVerbose( " ... site is closed" );
			} else if ($group->getIsDeleted()) {
				$this->logVerbose( " ... group is deleted" );
			} else if ($userHasNoEditorPermissionsInSiteRepo->isUserInSite($user, $site)) {
				$this->logVerbose( " ... user does not have edit permissions allowed in site" );
			} else if (!$userPermissions->hasPermission("org.openacalendar","CALENDAR_CHANGE")) {
				$this->logVerbose( " ... user does not have org.openacalendar/CALENDAR_CHANGE permission in site" );
			// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
			} else if ($userWatchesGroup->getIsWatching()) {

				$this->logVerbose( " ... searching for data" );

				$lastEvent = $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId());
				$data = $userWatchesGroup->getPromptEmailData($site, $lastEvent);


				if ($data['moreEventsNeeded']) {


					$this->logVerbose( " ... found data" );

					///// Notification Class 
					$userNotification = $userNotificationType->getNewNotification($user, $site);
					$userNotification->setGroup($group);

					////// Save Notification Class
					$userNotificationRepo->create($userNotification);

					////// Send Email
					if ($userNotification->getIsEmail()) {
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

						$messageText = $this->app['twig']->render('email/userWatchesGroupPromptEmail.txt.twig', array(
							'group'=>$group,
							'user'=>$user,
							'lastEvents'=>$lastEvents,
							'stopCode'=>$userWatchesGroupStop->getAccessKey(),
							'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
							'unsubscribeURL'=>$unsubscribeURL,
						));
						if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesGroupPromptEmail.txt', $messageText);
						$message->setBody($messageText);

						$messageHTML = $this->app['twig']->render('email/userWatchesGroupPromptEmail.html.twig', array(
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

						$this->logVerbose( " ... sending");
						if (!$CONFIG->isDebug) {
							$this->app['mailer']->send($message);
						}
						
						$userNotificationRepo->markEmailed($userNotification);
					}
					$userWatchesGroupRepository->markPromptEmailSent($userWatchesGroup, $data['checkTime']);
				}

			}

		}

		return array('result'=>'ok');
	}
	
}

