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
class API2ApplicationUserAuthorisationTokenModel {
	
	protected $api2_application_id;
	protected $user_id;
	protected $request_token;
	protected $authorisation_token;
	protected $created_at;
	protected $used_at;
	
	public function setFromDataBaseRow($data) {
		$this->api2_application_id   = $data['api2_application_id'];
		$this->user_id   = $data['user_id'];
		$this->request_token   = $data['request_token'];
		$this->authorisation_token   = $data['authorisation_token'];
		$this->created_at   = $data['created_at'];
		$this->used_at  = $data['used_at'];
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

	public function getRequestToken() {
		return $this->request_token;
	}

	public function setRequestToken($request_token) {
		$this->request_token = $request_token;
	}

	public function getAuthorisationToken() {
		return $this->authorisation_token;
	}

	public function setAuthorisationToken($authorisation_token) {
		$this->authorisation_token = $authorisation_token;
	}

		
	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}

	public function getUsedAt() {
		return $this->used_at;
	}

	public function setUsedAt($used_at) {
		$this->used_at = $used_at;
	}
	
	public function getIsUsed() {
		return (boolean)$this->used_at;
	}
	
}

