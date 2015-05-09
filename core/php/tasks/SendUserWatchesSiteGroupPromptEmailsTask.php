<?php

namespace tasks;

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
use repositories\builders\GroupRepositoryBuilder;
use repositories\UserNotificationRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesSiteGroupPromptEmailsTask  extends \BaseTask  {



	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'SendUserWatchesSiteGroupPromptEmails';
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
		$eventRepo = new EventRepository();
		$userWatchesSiteRepository = new UserWatchesSiteRepository();
		$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
		$userNotificationRepo = new UserNotificationRepository();

		/** @var usernotifications/UserWatchesSiteGroupPromptNotificationType **/
		$userNotificationType = $this->app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesSiteGroupPrompt');

		$b = new UserWatchesSiteRepositoryBuilder();
		foreach($b->fetchAll() as $userWatchesSite) {

			$user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
			$site = $siteRepo->loadById($userWatchesSite->getSiteId());
			// to avoid flooding user we only send one group email per run
			$anyGroupNotificationsSent = false;

			$this->logVerbose(" User ".$user->getEmail()." Site ".$site->getTitle());

			if ($site->getIsClosedBySysAdmin()) {
				$this->logVerbose( " ... site is closed");
			// Technically UserWatchesSiteRepositoryBuilder() should only return getIsWatching() == true but lets double check
			} else if ($userWatchesSite->getIsWatching()) {

				$groupRepoBuilder = new GroupRepositoryBuilder();
				$groupRepoBuilder->setSite($site);
				$groupRepoBuilder->setIncludeDeleted(false);
				foreach($groupRepoBuilder->fetchAll() as $group) {

					if (!$anyGroupNotificationsSent) {

						$this->logVerbose( " ... searching group ".$group->getSlug()." for data");

						$lastEvent = $eventRepo->loadLastNonDeletedNonImportedByStartTimeInGroupId($group->getId());
						$data = $userWatchesSite->getGroupPromptEmailData($site, $group, $lastEvent);

						if ($data['moreEventsNeeded']) {


							$this->logVerbose (" ... found data ");
							///// Notification Class 
							$userNotification = $userNotificationType->getNewNotification($user, $site);
							$userNotification->setGroup($group);

							////// Save Notification Class
							$userNotificationRepo->create($userNotification);

							////// Send Email
							if ($userNotification->getIsEmail()) {
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

								$messageText = $this->app['twig']->render('email/userWatchesSiteGroupPromptEmail.txt.twig', array(
									'user'=>$user,
									'group'=>$group,
									'lastEvents'=>$lastEvents,
									'stopCode'=>$userWatchesSiteStop->getAccessKey(),
									'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
									'unsubscribeURL'=>$unsubscribeURL,
								));
								if ($CONFIG->isDebug) file_put_contents('/tmp/userWatchesSiteGroupPromptEmail.txt', $messageText);
								$message->setBody($messageText);

								$messageHTML = $this->app['twig']->render('email/userWatchesSiteGroupPromptEmail.html.twig', array(
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


								$this->logVerbose(" ... sending" );
								if (!$CONFIG->isDebug) {
									$this->app['mailer']->send($message);
								}
								
								$userNotificationRepo->markEmailed($userNotification);
							}
							
							$userWatchesSiteRepository->markGroupPromptEmailSent($userWatchesSite, $group, $data['checkTime']);
							$anyGroupNotificationsSent = true;
							
						}

					}
				}

			}

		}


		return array('result'=>'ok');
	}
	
}

