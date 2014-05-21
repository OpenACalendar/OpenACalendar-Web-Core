<?php

namespace models;

use models\API2ApplicationModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class API2ApplicationUserPermissionsModel {
	
	protected $is_write_user_actions = 0;
	protected $is_write_user_profile = 0;
	protected $is_write_calendar = 0;

	public function getIsWriteUserActions() {
		return $this->is_write_user_actions;
	}

	public function setIsWriteUserActions($is_write_user_actions) {
		$this->is_write_user_actions = $is_write_user_actions;
	}

	public function getIsWriteUserProfile() {
		return $this->is_write_user_profile;
	}

	public function setIsWriteUserProfile($is_write_user_profile) {
		$this->is_write_user_profile = $is_write_user_profile;
	}

	public function getIsWriteCalendar() {
		return $this->is_write_calendar;
	}

	public function setIsWriteCalendar($is_write_calendar) {
		$this->is_write_calendar = $is_write_calendar;
	}

	public function setFromApp(API2ApplicationModel $app) {
		$this->is_write_calendar  = $app->getIsWriteCalendar();
		$this->is_write_user_actions  = $app->getIsWriteUserActions();
		$this->is_write_user_profile  = $app->getIsWriteUserProfile();
	}
	
}

