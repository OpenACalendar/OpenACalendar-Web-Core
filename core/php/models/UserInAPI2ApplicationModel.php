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
class UserInAPI2ApplicationModel {

	protected $api2_application_id;
	protected $user_id;
	protected $is_in_app;
	protected $is_editor = false;

	public function setFromDataBaseRow($data) {
		$this->api2_application_id = $data['api2_application_id'];
		$this->user_id = $data['user_id'];
		$this->is_in_app = (boolean)$data['is_in_app'];
		$this->is_editor = (boolean)$data['is_editor'];
	}
	
	public function getApi2ApplicationId() {
		return $this->api2_application_id;
	}

	public function setApi2ApplicationId($api2_application_id) {
		$this->api2_application_id = $api2_application_id;
	}

	public function getUserId() {
		return $this->user_id;
	}

	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}

	public function getIsInApp() {
		return $this->is_in_app;
	}

	public function setIsInApp($is_in_app) {
		$this->is_in_app = $is_in_app;
	}

	/**
	 * @param boolean $is_editor
	 */
	public function setIsEditor($is_editor)
	{
		$this->is_editor = $is_editor;
	}

	/**
	 * @return boolean
	 */
	public function getIsEditor()
	{
		return $this->is_editor;
	}


	
}



