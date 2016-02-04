<?php

namespace repositories;

use models\UserAccountModel;
use models\SiteModel;
use models\UserWatchesSiteModel;
use models\GroupModel;
use repositories\builders\HistoryRepositoryBuilder;
use Silex\Application;
use usernotifications\notifycontent\UserWatchesSiteNotifyContent;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class UserWatchesSiteRepository {

    /** @var Application */
    private  $app;


    function __construct(Application $app)
    {
        $this->app = $app;
    }


    public function loadByUserAndSite(UserAccountModel $user, SiteModel $site) {

		$stat = $this->app['db']->prepare("SELECT user_watches_site_information.* FROM user_watches_site_information WHERE user_account_id =:user_account_id AND site_id=:site_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'site_id'=>$site->getId() ));
		if ($stat->rowCount() > 0) {
			$uws = new UserWatchesSiteModel();
			$uws->setFromDataBaseRow($stat->fetch());
			return $uws;
		}		
	}
	
	public function startUserWatchingSite(UserAccountModel $user, SiteModel $site) {

	
		$uws = $this->loadByUserAndSite($user, $site);
		if ($uws && $uws->getIsWatching()) {
			// all done!
		} else if ($uws && !$uws->getIsWatching()) {
			$stat = $this->app['db']->prepare("UPDATE user_watches_site_information SET is_watching='1', last_watch_started=:last_watch_started WHERE user_account_id =:user_account_id AND site_id=:site_id");
			$stat->execute(array( 'user_account_id'=>$user->getId(), 'site_id'=>$site->getId(), 'last_watch_started'=> $this->app['timesource']->getFormattedForDataBase()));
		} else {
			$stat = $this->app['db']->prepare("INSERT INTO user_watches_site_information (user_account_id,site_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:site_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$user->getId(),
					'site_id'=>$site->getId(),
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'last_watch_started'=>  $this->app['timesource']->getFormattedForDataBase(),
				));			
		}
		
	}
	
	public function stopUserWatchingSite(UserAccountModel $user, SiteModel $site) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_site_information SET is_watching='0' WHERE user_account_id =:user_account_id AND site_id=:site_id");
		$stat->execute(array( 'user_account_id'=>$user->getId(), 'site_id'=>$site->getId() ));
	}
	
	public function markNotifyEmailSent(UserWatchesSiteModel $userWatchesSite, $emailTime) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_site_information SET last_notify_email_sent=:sent WHERE user_account_id =:user_account_id AND site_id=:site_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesSite->getUserAccountId(), 'site_id'=>$userWatchesSite->getSiteId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
		
	
	public function markPromptEmailSent(UserWatchesSiteModel $userWatchesSite, $emailTime) {

		$stat = $this->app['db']->prepare("UPDATE user_watches_site_information SET last_prompt_email_sent=:sent WHERE user_account_id =:user_account_id AND site_id=:site_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesSite->getUserAccountId(), 'site_id'=>$userWatchesSite->getSiteId(), 'sent'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
		
	
	public function markGroupPromptEmailSent(UserWatchesSiteModel $userWatchesSite, GroupModel $group, $emailTime) {

		$stat = $this->app['db']->prepare("INSERT INTO user_watches_site_group_prompt_email (user_account_id,group_id,sent_at) VALUES (:user_account_id,:group_id,:sent_at)");
		$stat->execute(array( 'user_account_id'=>$userWatchesSite->getUserAccountId(), 'group_id'=>$group->getId(), 'sent_at'=>$emailTime->format("Y-m-d H:i:s") ));		
	}
		
	public function getLastGroupPromptEmailSent(UserWatchesSiteModel $userWatchesSite, GroupModel $group) {

		$stat = $this->app['db']->prepare("SELECT MAX(sent_at) AS c FROM user_watches_site_group_prompt_email WHERE user_account_id=:user_account_id AND group_id=:group_id");
		$stat->execute(array( 'user_account_id'=>$userWatchesSite->getUserAccountId(), 'group_id'=>$group->getId(), ));		
		$data = $stat->fetch();
		return $data['c'] ? new \DateTime($data['c'], new \DateTimeZone("UTC")) : null;
	}

	/**
	 * @return BaseUserWatchesNotifyContent|null
	 */
	public function getUserNotifyContentForSiteAndUser(SiteModel $siteModel, UserAccountModel $userAccountModel) {

		$userWatchesSite = $this->loadByUserAndSite($userAccountModel, $siteModel);
		if ($userWatchesSite && $userWatchesSite->getIsWatching()) {

			$dateSince = $userWatchesSite->getSinceDateForNotifyChecking();
			$checkTime = $this->app['timesource']->getDateTime();

			$historyRepositoryBuilder = new HistoryRepositoryBuilder($this->app);
			$historyRepositoryBuilder->setSite($siteModel);
			$historyRepositoryBuilder->setSince($dateSince);
			$historyRepositoryBuilder->setNotUser($userAccountModel);
			// Only admins can change tags at the moment so don't include
			$historyRepositoryBuilder->setIncludeTagHistory(false);

			$histories = $historyRepositoryBuilder->fetchAll();

			if ($histories) {

				$content = new UserWatchesSiteNotifyContent();
				$content->setHistories($histories);

				$userWatchesSiteStopRepository = new UserWatchesSiteStopRepository($this->app);
				$userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($userAccountModel, $siteModel);
				$content->setUnwatchURL($this->app['config']->getWebSiteDomainSecure($siteModel->getSlug()).'/stopWatchingFromEmail/'. $userAccountModel->getId().'/'.$userWatchesSiteStop->getAccessKey());

				$content->setUserAccount($userAccountModel);
				$content->setSite($siteModel);
				$content->setWatchedThingTitle($siteModel->getTitle());
				$content->setWatchedThingURL($this->app['config']->getWebSiteDomainSecure($siteModel->getSlug()).'/history');

				return $content;

			}

		}

	}
	
}

