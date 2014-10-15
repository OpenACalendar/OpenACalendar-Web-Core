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
class API2ApplicationUserTokenModel {
	
	protected $api2_application_id;
	protected $user_id;
	protected $user_token;
	protected $user_secret;
	protected $is_editor;

	public function setFromDataBaseRow($data) {
		$this->api2_application_id = $data['api2_application_id'];
		$this->user_id = $data['user_id'];
		$this->user_token = $data['user_token'];
		$this->user_secret = $data['user_secret'];
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

	public function getUserToken() {
		return $this->user_token;
	}

	public function setUserToken($user_token) {
		$this->user_token = $user_token;
	}

	public function getUserSecret() {
		return $this->user_secret;
	}

	public function setUserSecret($user_secret) {
		$this->user_secret = $user_secret;
	}

	/**
	 * @param mixed $is_editor
	 */
	public function setIsEditor($is_editor)
	{
		$this->is_editor = $is_editor;
	}

	/**
	 * @return mixed
	 */
	public function getIsEditor()
	{
		return $this->is_editor;
	}


	
			
	
	
}

