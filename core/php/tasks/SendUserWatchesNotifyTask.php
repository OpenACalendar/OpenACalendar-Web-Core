<?php

namespace tasks;

use models\SiteModel;
use models\UserAccountModel;
use repositories\AreaHistoryRepository;
use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;
use repositories\EventHistoryRepository;
use repositories\GroupHistoryRepository;
use repositories\ImportURLHistoryRepository;
use repositories\SiteRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\UserNotificationRepository;
use repositories\VenueHistoryRepository;
use repositories\VenueRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesNotifyTask extends \BaseTask {


	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'SendUserWatchesNotify';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskSendUserWatchesNotifyAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskSendUserWatchesNotifyAutomaticUpdateInterval;
	}

	protected function run()
	{
		$siteRepoBuilder = new SiteRepositoryBuilder();
		$siteRepoBuilder->setIsOpenBySysAdminsOnly(true);
		$countCheck = 0;
		$countSend = 0;
		foreach($siteRepoBuilder->fetchAll() as $site) {
			$this->logVerbose("Site ".$site->getSlug());
			$userRepoBuilder = new UserAccountRepositoryBuilder();
			$userRepoBuilder->setIsOpenBySysAdminsOnly(true);
			foreach($userRepoBuilder->fetchAll() as $userAccount) {
				$this->logVerbose("User ".$userAccount->getId());
				++$countCheck;

				$checkTime = \TimeSource::getDateTime();
				$contentsToSend = array();
				foreach($this->app['extensions']->getExtensionsIncludingCore() as $extension) {
					$contentsToSend = array_merge($contentsToSend, $extension->getUserNotifyContents($site, $userAccount));
				}

				if ($contentsToSend) {
					$this->logVerbose("Found contents!");
					++$countSend;
					$this->makeSureHistoriesAreCorrect($contentsToSend);
					$this->sendFor($site, $userAccount, $contentsToSend);
					foreach($contentsToSend as $contentToSend) {
						$contentToSend->markNotificationSent($checkTime);
					}
				} else {
					$this->logVerbose("found nothing");
				}

			}
		}
		return array('result'=>'ok','countCheck'=>$countCheck, 'countSend'=>$countSend);
	}

	protected function makeSureHistoriesAreCorrect($contentsToSend) {
		$eventHistoryRepository =  new EventHistoryRepository;
		$groupHistoryRepository = new GroupHistoryRepository;
		$areaHistoryRepository = new AreaHistoryRepository;
		$venueHistoryRepository = new VenueHistoryRepository;
		$importURLHistoryRepository = new ImportURLHistoryRepository;
		foreach($contentsToSend as $contentToSend) {
			foreach($contentToSend->getHistories() as $history) {
				if ($history instanceof EventHistoryModel) {
					$eventHistoryRepository->ensureChangedFlagsAreSet($history);
				} elseif ($history instanceof GroupHistoryModel) {
					$groupHistoryRepository->ensureChangedFlagsAreSet($history);
				} elseif ($history instanceof VenueHistoryModel) {
					$venueHistoryRepository->ensureChangedFlagsAreSet($history);
				} elseif ($history instanceof AreaHistoryModel) {
					$areaHistoryRepository->ensureChangedFlagsAreSet($history);
				} elseif ($history instanceof ImportURLHistoryModel) {
					$importURLHistoryRepository->ensureChangedFlagsAreSet($history);
				}
			}
		}
	}

	protected function getNewAndHistoriesForContentsToSend($contentsToSend) {
		$histories = array();
		foreach($contentsToSend as $contentToSend) {
			foreach($contentToSend->getHistories() as $history) {
				if (true) { // TODO only if not already there!!!!!!!!!!
					$histories[] = $history;
				}
			}
		}
		// sort
		$usort = function($a, $b) {
			if ($a->getCreatedAt()->getTimestamp() == $b->getCreatedAt()->getTimestamp()) {
				return 0;
			} else if ($a->getCreatedAt()->getTimestamp() > $b->getCreatedAt()->getTimestamp()) {
				return -1;
			} else {
				return 1;
			}
		};
		usort($histories, $usort);
		// TODO pick out new data items!
		return array(array(),$histories);
	}

	protected function sendFor(SiteModel $siteModel, UserAccountModel $userAccountModel, $contentsToSend) {

		$userNotificationType = $this->app['extensions']->getCoreExtension()->getUserNotificationType('UserWatchesNotify');
		$userNotificationRepo = new UserNotificationRepository();

		///// Notification Class
		$userNotification = $userNotificationType->getNewNotification($userAccountModel, $siteModel);
		foreach($contentsToSend as $contentToSend) {
			$userNotification->addContent($contentToSend);
		}

		////// Save Notification Class
		$userNotificationRepo->create($userNotification);

		////// Send Email
		if ($userNotification->getIsEmail()) {


			list($newDataItems, $histories) = $this->getNewAndHistoriesForContentsToSend($contentsToSend);

			$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
			$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($userAccountModel);
			$unsubscribeURL = $this->app['config']->getWebIndexDomainSecure().'/you/emails/'.$userAccountModel->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey();

			configureAppForSite($siteModel);
			configureAppForUser($userAccountModel);

			$message = \Swift_Message::newInstance();
			$message->setSubject($this->getEmailSubject($siteModel, $userAccountModel, $contentsToSend));
			$message->setFrom(array($this->app['config']->emailFrom => $this->app['config']->emailFromName));
			$message->setTo($userAccountModel->getEmail());

			$messageText = $this->app['twig']->render('email/userWatchesNotifyEmail.txt.twig', array(
				'user'=>$userAccountModel,
				'newDataItems'=>$newDataItems,
				'histories'=>$histories,
				'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
				'unsubscribeURL'=>$unsubscribeURL,
				'contents'=>$contentsToSend,
			));
			if ($this->app['config']->isDebug) file_put_contents('/tmp/userWatchesNotifyEmail.txt', $messageText);
			$message->setBody($messageText);

			$messageHTML = $this->app['twig']->render('email/userWatchesNotifyEmail.html.twig', array(
				'user'=>$userAccountModel,
				'newDataItems'=>$newDataItems,
				'histories'=>$histories,
				'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
				'unsubscribeURL'=>$unsubscribeURL,
				'contents'=>$contentsToSend,
			));
			if ($this->app['config']->isDebug) file_put_contents('/tmp/userWatchesNotifyEmail.html', $messageHTML);
			$message->addPart($messageHTML,'text/html');

			$headers = $message->getHeaders();
			$headers->addTextHeader('List-Unsubscribe', $unsubscribeURL);

			$this->logVerbose("Sending ...");
			if (!$this->app['config']->isDebug) {
				$this->app['mailer']->send($message);
			}
			$userNotificationRepo->markEmailed($userNotification);
		}


	}

	protected  function getEmailSubject(SiteModel $siteModel, UserAccountModel $userAccountModel, $contentsToSend) {
		if (count($contentsToSend) ==1) {
			return "Changes in ". $contentsToSend[0]->getWatchedThingTitle();
		} else if (count($contentsToSend) == 2) {
			return "Changes in ". $contentsToSend[0]->getWatchedThingTitle(). " and ".$contentsToSend[1]->getWatchedThingTitle();
		} else {
			return "Changes in ". $siteModel->getTitle();
		}
	}

	public function getResultDataAsString(\models\TaskLogModel $taskLogModel) {
		if ($taskLogModel->getIsResultDataHaveKey("result") && $taskLogModel->getResultDataValue("result") == "ok") {
			return "Ok";
		} else {
			return "Fail";
		}

	}



}

