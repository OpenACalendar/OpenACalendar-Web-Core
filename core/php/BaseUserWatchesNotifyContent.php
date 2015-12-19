<?php

use models\UserAccountModel;
use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;
use repositories\SiteRepository;
use repositories\VenueRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseUserWatchesNotifyContent {

	protected $histories;

	protected $watchedThingURL;

	protected $watchedThingTitle;

	protected $unwatchURL;

	/** @var  UserAccountModel */
	protected $userAccount;

	abstract public function markNotificationSent(\DateTime $checkTime);

	/**
	 * @param mixed $histories
	 */
	public function setHistories($histories)
	{
		$this->histories = $histories;
	}

	/**
	 * @return mixed
	 */
	public function getHistories()
	{
		return $this->histories;
	}

	/**
	 * @param mixed $unwatchURL
	 */
	public function setUnwatchURL($unwatchURL)
	{
		$this->unwatchURL = $unwatchURL;
	}

	/**
	 * @return mixed
	 */
	public function getUnwatchURL()
	{
		return $this->unwatchURL;
	}

	/**
	 * @param \models\UserAccountModel $userAccount
	 */
	public function setUserAccount($userAccount)
	{
		$this->userAccount = $userAccount;
	}

	/**
	 * @return \models\UserAccountModel
	 */
	public function getUserAccount()
	{
		return $this->userAccount;
	}

	/**
	 * @param mixed $watchedThingTitle
	 */
	public function setWatchedThingTitle($watchedThingTitle)
	{
		$this->watchedThingTitle = $watchedThingTitle;
	}

	/**
	 * @return mixed
	 */
	public function getWatchedThingTitle()
	{
		return $this->watchedThingTitle;
	}

	/**
	 * @param mixed $watchedThingURL
	 */
	public function setWatchedThingURL($watchedThingURL)
	{
		$this->watchedThingURL = $watchedThingURL;
	}

	/**
	 * @return mixed
	 */
	public function getWatchedThingURL()
	{
		return $this->watchedThingURL;
	}

}
