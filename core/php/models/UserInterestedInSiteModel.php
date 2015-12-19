<?php

namespace models;
use repositories\UserWatchesSiteRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserInterestedInSiteModel {
	
	protected $user_account_id;
	protected $site_id;
	protected $is_interested = false;

	public function setFromDataBaseRow($data) {
		$this->user_account_id = $data['user_account_id'];
		$this->site_id = $data['site_id'];
		$this->is_interested = $data['is_interested'];
	}
	
	
	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
	}

	/**
	 * @return boolean
	 */
	public function isInterested()
	{
		return $this->is_interested;
	}

	/**
	 * @param boolean $is_interested
	 */
	public function setIsInterested($is_interested)
	{
		$this->is_interested = $is_interested;
	}


	
}

