<?php

namespace usernotifications\notifycontent;
use BaseUserWatchesNotifyContent;
use models\SiteModel;
use models\VenueModel;
use models\UserAccountModel;
use repositories\UserWatchesSiteRepository;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSiteNotifyContent extends BaseUserWatchesNotifyContent {

	/** @var  SiteModel */
	protected $site;

	/**
	 * @param \models\SiteModel $site
	 */
	public function setSite($site)
	{
		$this->site = $site;
	}

	/**
	 * @return \models\SiteModel
	 */
	public function getSite()
	{
		return $this->site;
	}

	public function markNotificationSent(\DateTime $checkTime)
	{
		$userWatchesSiteRepository = new UserWatchesSiteRepository();
		$userWatchesSite = $userWatchesSiteRepository->loadByUserAndSite($this->userAccount, $this->site);
		$userWatchesSiteRepository->markNotifyEmailSent($userWatchesSite, $checkTime);
	}
}

