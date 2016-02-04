<?php

namespace repositories;

use models\UserAccountModel;
use models\GroupModel;
use models\UserWatchesGroupModel;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use Silex\Application;
use usernotifications\notifycontent\UserWatchesGroupNotifyContent;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesGroupRepository {

    /** @var Application */
    private  $app;


    function __construct(Application $app)
    {
        $this->app = $app;
    }

	public function loadByUserAndGroup(UserAccountModel $user, GroupModel $group) {
		return $this->loadByUserAndGroupId($user, $group->getId());
	}
	
	public function loadByUserAndGroupId(UserAccountModel $user, $groupID) {

		$stat = $this->app['db']->prepare("SELECT user_watches_group_information.* FROM user_watches_group_information WHERE user_account_id =:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'group_id'=>$groupID ));
		if ($stat->rowCount() > 0) {
			$uws = new UserWatchesGroupModel();
			$uws->setFromDataBaseRow($stat->fetch());
			return $uws;
		}		
	}
	
	/**
	 * Note this does not check if user is watching site first! TODO?
	 */
	public function startUserWatchingGroup(UserAccountModel $user, GroupModel $group) {

	
		$uws = $this->loadByUserAndGroup($user, $group);
		if ($uws && $uws->getIsWatching()) {
			// all done!
		} else if ($uws && !$uws->getIsWatching()) {
			$stat = $this->app['db']->prepare("UPDATE user_watches_group_information SET is_watching='1',last_watch_started=:last_watch_started WHERE user_account_id =:user_account_id AND group_id=:group_id");
			$stat->execute(array( 'user_account_id'=>$user->getId(), 'group_id'=>$group->getId(), 'last_watch_started'=> $this->app['timesource']->getFormattedForDataBase()));
		} else {
			$stat = $this->app['db']->prepare("INSERT INTO user_watches_group_information (user_account_id,group_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:group_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'group_id'=>$group->getId(),
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'last_watch_started'=>  $this->app['timesource']->getFormattedForDataBase(),
				));			
		}
		
	}
	
	public function stopUserWatchingGroup(UserAccountModel $user, GroupModel $group) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_group_information SET is_watching='0' WHERE user_account_id =:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'group_id'=>$group->getId() ));
	}

		
	
	public function markNotifyEmailSent(UserWatchesGroupModel $userWatchesGroup, $emailTime) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_group_information SET last_notify_email_sent=:sent WHERE user_account_id =:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesGroup->getUserAccountId(), 'group_id'=>$userWatchesGroup->getGroupId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
	
	public function markPromptEmailSent(UserWatchesGroupModel $userWatchesGroup, $emailTime) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_group_information SET last_prompt_email_sent=:sent WHERE user_account_id =:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesGroup->getUserAccountId(), 'group_id'=>$userWatchesGroup->getGroupId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
		
	/*
	 * Note this does not check if user is watching site first! TODO?
	 */
	public function startUserWatchingGroupIfNotWatchedBefore(UserAccountModel $user, GroupModel $group) {
		$this->startUserWatchingGroupIdIfNotWatchedBefore($user, $group->getId());
	}
	
	/*
	 * Note this does not check if user is watching site first! TODO?
	 */	
	public function startUserWatchingGroupIdIfNotWatchedBefore(UserAccountModel $user, $groupID) {

		$uws = $this->loadByUserAndGroupId($user, $groupID);
		if ($uws) {
			// all done! They are already watching or they once were watching.
		} else {
			$stat = $this->app['db']->prepare("INSERT INTO user_watches_group_information (user_account_id,group_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:group_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'group_id'=>$groupID,
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
	public function getUserNotifyContentForSiteAndUser(\models\SiteModel $siteModel, UserAccountModel $userAccountModel) {

		if (!$siteModel->getIsFeatureGroup()) {
			return array();
		}

		$out = array();

		$grb = new GroupRepositoryBuilder($this->app);
		$grb->setSite($siteModel);
		$grb->setLimit(0); // all! No limit

		// TODO  don't we still want to do this? How will user A get a notification if user B deletes group? but then so far most group deletetions are by admins.
		$grb->setIncludeDeleted(false);

		foreach($grb->fetchAll() as $group) {

			$uwg = $this->loadByUserAndGroup($userAccountModel, $group);
			if ($uwg && $uwg->getIsWatching()) {

				$dateSince = $uwg->getSinceDateForNotifyChecking();

				$historyRepositoryBuilder = new HistoryRepositoryBuilder($this->app);
				$historyRepositoryBuilder->setGroup($group);
				$historyRepositoryBuilder->setSince($dateSince);
				$historyRepositoryBuilder->setNotUser($userAccountModel);
				// Only admins can change tags at the moment so don't include
				$historyRepositoryBuilder->setIncludeTagHistory(false);

				$histories = $historyRepositoryBuilder->fetchAll();

				if ($histories) {

					$content = new UserWatchesGroupNotifyContent();
					$content->setHistories($histories);

					$userWatchesGroupStopRepository = new UserWatchesGroupStopRepository($this->app);
					$userWatchesGroupStop = $userWatchesGroupStopRepository->getForUserAndGroup($userAccountModel, $group);
					$content->setUnwatchURL($this->app['config']->getWebSiteDomainSecure($siteModel->getSlug()).
						'/group/'. $group->getSlugForURL().
						'/stopWatchingFromEmail/'. $userAccountModel->getId().'/'.$userWatchesGroupStop->getAccessKey());

					$content->setUserAccount($userAccountModel);
					$content->setSite($siteModel);
					$content->setGroup($group);
					$content->setWatchedThingTitle($group->getTitle());
					$content->setWatchedThingURL($this->app['config']->getWebSiteDomainSecure($siteModel->getSlug()).'/group/'. $group->getSlugForURL().'/history');

					$out[] = $content;

				}

			}

		}

		return $out;

	}



}


