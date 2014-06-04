<?php

namespace models;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class UserNotificationPreferenceModel {
	
	protected $is_email;
	
	public function setFromDataBaseRow($data) {
		$this->is_email = (boolean)$data['is_email'];
	}
	
	public function getIsEmail() {
		return $this->is_email;
	}

	public function setIsEmail($is_email) {
		$this->is_email = $is_email;
	}
	
}

