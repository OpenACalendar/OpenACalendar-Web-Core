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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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

		
		$userRepo = new UserAccountRepository($this->app);
		$siteRepo = new SiteRepository($this->app);
		$eventRepo = new EventRepository($this->app);
		$userWatchesSiteRepository = new UserWatchesSiteRepository($this->app);
		$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository($this->app);
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository($this->app);
		$userNotificationRepo = new UserNotificationRepository($this->app);

		/** @var usernotifications/UserWatchesSiteGroupPromptNotificationType **/
		$userNotificationType = $this->app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesSiteGroupPrompt');

		$b = new UserWatchesSiteRepositoryBuilder($this->app);
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

				$groupRepoBuilder = new GroupRepositoryBuilder($this->app);
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
                                $unsubscribeURL = $this->app['config']->getWebIndexDomainSecure().'/you/listunsub/'.
                                    $user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();
								$lastEventsBuilder = new EventRepositoryBuilder($this->app);
								$lastEventsBuilder->setSite($site);
								$lastEventsBuilder->setGroup($group);
								$lastEventsBuilder->setOrderByStartAt(true);
								$lastEventsBuilder->setIncludeDeleted(false);
								$lastEventsBuilder->setIncludeImported(false);
								$lastEventsBuilder->setLimit($this->app['config']->userWatchesSiteGroupPromptEmailShowEvents);
								$lastEvents = $lastEventsBuilder->fetchAll();

								$message = \Swift_Message::newInstance();
								$message->setSubject("Any news about ".$group->getTitle()."?");
								$message->setFrom(array($this->app['config']->emailFrom => $this->app['config']->emailFromName));
								$message->setTo($user->getEmail());

								$messageText = $this->app['twig']->render('email/userWatchesSiteGroupPromptEmail.txt.twig', array(
									'user'=>$user,
									'group'=>$group,
									'lastEvents'=>$lastEvents,
									'stopCode'=>$userWatchesSiteStop->getAccessKey(),
									'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
									'unsubscribeURL'=>$unsubscribeURL,
								));
								if ($this->app['config']->isDebug) file_put_contents('/tmp/userWatchesSiteGroupPromptEmail.txt', $messageText);
								$message->setBody($messageText);

								$messageHTML = $this->app['twig']->render('email/userWatchesSiteGroupPromptEmail.html.twig', array(
									'user'=>$user,
									'group'=>$group,
									'lastEvents'=>$lastEvents,
									'stopCode'=>$userWatchesSiteStop->getAccessKey(),
									'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
									'unsubscribeURL'=>$unsubscribeURL,
								));
								if ($this->app['config']->isDebug) file_put_contents('/tmp/userWatchesSiteGroupPromptEmail.html', $messageHTML);
								$message->addPart($messageHTML,'text/html');

								$headers = $message->getHeaders();
								$headers->addTextHeader('List-Unsubscribe', '<'.$unsubscribeURL.'>');


								$this->logVerbose(" ... sending" );
								if (!$this->app['config']->isDebug) {
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

