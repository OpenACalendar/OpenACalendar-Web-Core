<?php

namespace repositories;

use models\SiteModel;
use models\UserAccountModel;
use models\AreaModel;
use models\UserWatchesAreaModel;
use repositories\builders\AreaRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use usernotifications\notifycontent\UserWatchesAreaNotifyContent;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesAreaRepository {

	public function loadByUserAndArea(UserAccountModel $user, AreaModel $area) {
		return $this->loadByUserAndAreaId($user, $area->getId());
	}
	
	public function loadByUserAndAreaId(UserAccountModel $user, $areaID) {
		global $DB;
		$stat = $DB->prepare("SELECT user_watches_area_information.* FROM user_watches_area_information WHERE user_account_id =:user_account_id AND area_id=:area_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'area_id'=>$areaID ));
		if ($stat->rowCount() > 0) {
			$uws = new UserWatchesAreaModel();
			$uws->setFromDataBaseRow($stat->fetch());
			return $uws;
		}		
	}
	
	/**
	 * Note this does not check if user is watching site first! TODO?
	 */
	public function startUserWatchingArea(UserAccountModel $user, AreaModel $area) {
		global $DB;
	
		$uws = $this->loadByUserAndArea($user, $area);
		if ($uws && $uws->getIsWatching()) {
			// all done!
		} else if ($uws && !$uws->getIsWatching()) {
			$stat = $DB->prepare("UPDATE user_watches_area_information SET is_watching='1',last_watch_started=:last_watch_started WHERE user_account_id =:user_account_id AND area_id=:area_id");
			$stat->execute(array( 'user_account_id'=>$user->getId(), 'area_id'=>$area->getId(), 'last_watch_started'=> \TimeSource::getFormattedForDataBase()));
		} else {
			$stat = $DB->prepare("INSERT INTO user_watches_area_information (user_account_id,area_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:area_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'area_id'=>$area->getId(),
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  \TimeSource::getFormattedForDataBase(),
					'last_watch_started'=>  \TimeSource::getFormattedForDataBase(),
				));			
		}
		
	}
	
	public function stopUserWatchingArea(UserAccountModel $user, AreaModel $area) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_watches_area_information SET is_watching='0' WHERE user_account_id =:user_account_id AND area_id=:area_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'area_id'=>$area->getId() ));
	}

		
	
	public function markNotifyEmailSent(UserWatchesAreaModel $userWatchesArea, $emailTime) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_watches_area_information SET last_notify_email_sent=:sent WHERE user_account_id =:user_account_id AND area_id=:area_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesArea->getUserAccountId(), 'area_id'=>$userWatchesArea->getAreaId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
	
	public function markPromptEmailSent(UserWatchesAreaModel $userWatchesArea, $emailTime) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_watches_area_information SET last_prompt_email_sent=:sent WHERE user_account_id =:user_account_id AND area_id=:area_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesArea->getUserAccountId(), 'area_id'=>$userWatchesArea->getAreaId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
		
	/*
	 * Note this does not check if user is watching site first! TODO?
	 */
	public function startUserWatchingAreaIfNotWatchedBefore(UserAccountModel $user, AreaModel $area) {
		$this->startUserWatchingAreaIdIfNotWatchedBefore($user, $area->getId());
	}
	
	/*
	 * Note this does not check if user is watching site first! TODO?
	 */	
	public function startUserWatchingAreaIdIfNotWatchedBefore(UserAccountModel $user, $areaID) {
		global $DB;
		$uws = $this->loadByUserAndAreaId($user, $areaID);
		if ($uws) {
			// all done! They are already watching or they once were watching.
		} else {
			$stat = $DB->prepare("INSERT INTO user_watches_area_information (user_account_id,area_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:area_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'area_id'=>$areaID,
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  \TimeSource::getFormattedForDataBase(),
					'last_watch_started'=>  \TimeSource::getFormattedForDataBase(),
				));			
		}
		
	}

	/**
	 * @return array
	 */
	public function getUserNotifyContentForSiteAndUser(SiteModel $siteModel, UserAccountModel $userAccountModel) {
		global $CONFIG;

		$out = array();

		$grb = new AreaRepositoryBuilder();
		$grb->setSite($siteModel);
		$grb->setLimit(0); // all! No limit

		// TODO  don't we still want to do this? How will user A get a notification if user B deletes area? but then so far most area deletetions are by admins.
		$grb->setIncludeDeleted(false);

		foreach($grb->fetchAll() as $area) {

			$uwg = $this->loadByUserAndArea($userAccountModel, $area);
			if ($uwg && $uwg->getIsWatching()) {

				$dateSince = $uwg->getSinceDateForNotifyChecking();

				$historyRepositoryBuilder = new HistoryRepositoryBuilder();
				$historyRepositoryBuilder->getHistoryRepositoryBuilderConfig()->setArea($area);
				$historyRepositoryBuilder->setSince($dateSince);
				$historyRepositoryBuilder->setNotUser($userAccountModel);
				// Only admins can change tags at the moment so don't include
				$historyRepositoryBuilder->setIncludeTagHistory(false);

				$histories = $historyRepositoryBuilder->fetchAll();

				if ($histories) {

					$content = new UserWatchesAreaNotifyContent();
					$content->setHistories($histories);

					$userWatchesAreaStopRepository = new UserWatchesAreaStopRepository();
					$userWatchesAreaStop = $userWatchesAreaStopRepository->getForUserAndArea($userAccountModel, $area);
					$content->setUnwatchURL($CONFIG->getWebSiteDomainSecure($siteModel->getSlug()).
						'/area/'. $area->getSlugForURL().
						'/stopWatchingFromEmail/'. $userAccountModel->getId().'/'.$userWatchesAreaStop->getAccessKey());

					$content->setUserAccount($userAccountModel);
					$content->setSite($siteModel);
					$content->setArea($area);
					$content->setWatchedThingTitle($area->getTitle());
					$content->setWatchedThingURL($CONFIG->getWebSiteDomainSecure($siteModel->getSlug()).'/area/'. $area->getSlugForURL().'/history');

					$out[] = $content;

				}

			}

		}

		return $out;

	}



}

