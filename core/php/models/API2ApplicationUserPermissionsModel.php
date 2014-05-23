<?php

namespace models;

use models\API2ApplicationModel;

/**
 *
 * 
 *  This model does not relate to a DB table directly, but is used in login/auth process.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class API2ApplicationUserPermissionsModel {
	
	protected $is_write_user_actions = 0;
	protected $is_write_calendar = 0;

	public function setFromData($data) {
		$this->is_write_calendar  = isset($data['is_write_calendar']) ? ($data['is_write_calendar'] ? 1 : -1) : 0;
		$this->is_write_user_actions  = isset($data['is_write_user_actions']) ? ($data['is_write_user_actions'] ? 1 : -1) : 0;
	}
	
	public function setFromApp(API2ApplicationModel $app) {
		$this->is_write_calendar  = $app->getIsWriteCalendar() ? 1 : 0;
		$this->is_write_user_actions  = $app->getIsWriteUserActions() ? 1 : 0;
	}
	
	/** is_write_user_actions **/
	
	public function getIsWriteUserActionsGranted() {
		return ($this->is_write_user_actions == 1);
	}
	
	public function getIsWriteUserActionsRefused() {
		return ($this->is_write_user_actions == -1);
	}

	public function setWriteUserActionsGranted() {
		$this->is_write_user_actions = 1;
	}

	public function setWriteUserActionsRefused() {
		$this->is_write_user_actions = -1;
	}

	/** is_write_calendar **/
	
	public function getIsWriteCalendarGranted() {
		return ($this->is_write_calendar == 1);
	}

	public function getIsWriteCalendarRefused() {
		return ($this->is_write_calendar == -1);
	}

	public function setWriteCalendarGranted() {
		$this->is_write_calendar = 1;
	}

	public function setWriteCalendarRefused() {
		$this->is_write_calendar = -1;
	}

	
	
	
	
}

