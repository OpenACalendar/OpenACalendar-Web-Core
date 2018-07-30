<?php

namespace repositories;

use models\SiteModel;
use models\UserAccountModel;
use models\AreaModel;
use models\UserWatchesAreaModel;
use repositories\builders\AreaRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use Silex\Application;
use usernotifications\notifycontent\UserWatchesAreaNotifyContent;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesAreaRepository {

    /** @var Application */
    private  $app;


    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function loadByUserAndArea(UserAccountModel $user, AreaModel $area) {
		return $this->loadByUserAndAreaId($user, $area->getId());
	}
	
	public function loadByUserAndAreaId(UserAccountModel $user, int $areaID) {

		$stat = $this->app['db']->prepare("SELECT user_watches_area_information.* FROM user_watches_area_information WHERE user_account_id =:user_account_id AND area_id=:area_id");
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

	
		$uws = $this->loadByUserAndArea($user, $area);
		if ($uws && $uws->getIsWatching()) {
			// all done!
		} else if ($uws && !$uws->getIsWatching()) {
			$stat = $this->app['db']->prepare("UPDATE user_watches_area_information SET is_watching='1',last_watch_started=:last_watch_started WHERE user_account_id =:user_account_id AND area_id=:area_id");
			$stat->execute(array( 'user_account_id'=>$user->getId(), 'area_id'=>$area->getId(), 'last_watch_started'=> $this->app['timesource']->getFormattedForDataBase()));
		} else {
			$stat = $this->app['db']->prepare("INSERT INTO user_watches_area_information (user_account_id,area_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:area_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'area_id'=>$area->getId(),
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'last_watch_started'=>  $this->app['timesource']->getFormattedForDataBase(),
				));			
		}
		
	}
	
	public function stopUserWatchingArea(UserAccountModel $user, AreaModel $area) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_area_information SET is_watching='0' WHERE user_account_id =:user_account_id AND area_id=:area_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'area_id'=>$area->getId() ));
	}

		
	
	public function markNotifyEmailSent(UserWatchesAreaModel $userWatchesArea, $emailTime) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_area_information SET last_notify_email_sent=:sent WHERE user_account_id =:user_account_id AND area_id=:area_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesArea->getUserAccountId(), 'area_id'=>$userWatchesArea->getAreaId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
	
	public function markPromptEmailSent(UserWatchesAreaModel $userWatchesArea, $emailTime) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_area_information SET last_prompt_email_sent=:sent WHERE user_account_id =:user_account_id AND area_id=:area_id");
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
	public function startUserWatchingAreaIdIfNotWatchedBefore(UserAccountModel $user, int $areaID) {

		$uws = $this->loadByUserAndAreaId($user, $areaID);
		if ($uws) {
			// all done! They are already watching or they once were watching.
		} else {
			$stat = $this->app['db']->prepare("INSERT INTO user_watches_area_information (user_account_id,area_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:area_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'area_id'=>$areaID,
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'last_watch_started'=>  $this->app['timesource']->getFormattedForDataBase(),
				));			
		}
		
	}

	/**
	 * @return array
	 */
	public function getUserNotifyContentForSiteAndUser(SiteModel $siteModel, UserAccountModel $userAccountModel) {

		$out = array();

		$grb = new AreaRepositoryBuilder($this->app);
		$grb->setSite($siteModel);
		$grb->setLimit(0); // all! No limit

		// TODO  don't we still want to do this? How will user A get a notification if user B deletes area? but then so far most area deletetions are by admins.
		$grb->setIncludeDeleted(false);

		foreach($grb->fetchAll() as $area) {

			$uwg = $this->loadByUserAndArea($userAccountModel, $area);
			if ($uwg && $uwg->getIsWatching()) {

				$dateSince = $uwg->getSinceDateForNotifyChecking();

				$historyRepositoryBuilder = new HistoryRepositoryBuilder($this->app);
				$historyRepositoryBuilder->getHistoryRepositoryBuilderConfig()->setArea($area);
				$historyRepositoryBuilder->setSince($dateSince);
				$historyRepositoryBuilder->setNotUser($userAccountModel);
				// Only admins can change tags at the moment so don't include
				$historyRepositoryBuilder->setIncludeTagHistory(false);

				$histories = $historyRepositoryBuilder->fetchAll();

				if ($histories) {

					$content = new UserWatchesAreaNotifyContent();
					$content->setHistories($histories);

					$userWatchesAreaStopRepository = new UserWatchesAreaStopRepository($this->app);
					$userWatchesAreaStop = $userWatchesAreaStopRepository->getForUserAndArea($userAccountModel, $area);
					$content->setUnwatchURL($this->app['config']->getWebSiteDomainSecure($siteModel->getSlug()).
						'/area/'. $area->getSlugForURL().
						'/stopWatchingFromEmail/'. $userAccountModel->getId().'/'.$userWatchesAreaStop->getAccessKey());

					$content->setUserAccount($userAccountModel);
					$content->setSite($siteModel);
					$content->setArea($area);
					$content->setWatchedThingTitle($area->getTitle());
					$content->setWatchedThingURL($this->app['config']->getWebSiteDomainSecure($siteModel->getSlug()).'/area/'. $area->getSlugForURL().'/history');

					$out[] = $content;

				}

			}

		}

		return $out;

	}



}

