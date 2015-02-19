<?php

namespace usernotifications;

use BaseUserWatchesNotifyContent;
use models\SiteModel;
use models\VenueModel;
use models\UserAccountModel;
use repositories\UserWatchesGroupRepository;
use repositories\UserWatchesSiteRepository;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesGroupNotifyContent extends BaseUserWatchesNotifyContent {

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

	/** @var GroupModel */
	protected $group;

	/**
	 * @param mixed $group
	 */
	public function setGroup($group)
	{
		$this->group = $group;
	}

	/**
	 * @return mixed
	 */
	public function getGroup()
	{
		return $this->group;
	}

	public function markNotificationSent(\DateTime $checkTime)
	{
		$userWatchesGroupRepository = new UserWatchesGroupRepository();
		$userWatchesGroup = $userWatchesGroupRepository->loadByUserAndGroup($this->userAccount, $this->group);
		$userWatchesGroupRepository->markNotifyEmailSent($userWatchesGroup, $checkTime);
	}
}

