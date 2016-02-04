<?php

namespace usernotifications\notifycontent;

use BaseUserWatchesNotifyContent;
use models\SiteModel;
use models\VenueModel;
use models\UserAccountModel;
use repositories\UserWatchesAreaRepository;
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
class UserWatchesAreaNotifyContent extends BaseUserWatchesNotifyContent {

	/** @var  SiteModel */
	protected $site;

	/**
	 * @param \models\SiteModel $site
	 */
	public function setSite(\models\SiteModel $site)
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

	/** @var AreaModel */
	protected $area;

	/**
	 * @param mixed $area
	 */
	public function setArea($area)
	{
		$this->area = $area;
	}

	/**
	 * @return mixed
	 */
	public function getArea()
	{
		return $this->area;
	}

	public function markNotificationSent(\DateTime $checkTime)
	{
        global $app;
		$userWatchesAreaRepository = new UserWatchesAreaRepository($app);
		$userWatchesArea = $userWatchesAreaRepository->loadByUserAndArea($this->userAccount, $this->area);
		$userWatchesAreaRepository->markNotifyEmailSent($userWatchesArea, $checkTime);
	}
}

