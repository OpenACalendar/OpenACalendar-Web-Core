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
class API2ApplicationRequestTokenModel {
	
	protected $api2_application_id;
	protected $request_token;
	protected $created_at;
	protected $used_at;
	protected $user_id;
	protected $callback_url;
	protected $is_callback_display;
	protected $is_callback_javascript;
	protected $is_write_user_actions;
	protected $is_write_user_profile;
	protected $is_write_calendar;

	public function setFromDataBaseRow($data) {
		$this->api2_application_id   = $data['api2_application_id'];
		$this->request_token   = $data['request_token'];
		$this->created_at   = $data['created_at'];
		$this->used_at   = $data['used_at'];
		$this->user_id   = $data['user_id'];
		$this->callback_url   = $data['callback_url'];
		$this->is_callback_display   = $data['is_callback_display'];
		$this->is_callback_javascript   = $data['is_callback_javascript'];
		$this->is_write_calendar  = (boolean)$data['is_write_calendar'];
		$this->is_write_user_actions  = (boolean)$data['is_write_user_actions'];
		$this->is_write_user_profile  = (boolean)$data['is_write_user_profile'];
	}
	
	
	public function getApi2ApplicationId() {
		return $this->api2_application_id;
	}

	public function setApi2ApplicationId($api2_application_id) {
		$this->api2_application_id = $api2_application_id;
	}

	public function getRequestToken() {
		return $this->request_token;
	}

	public function setRequestToken($request_token) {
		$this->request_token = $request_token;
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

	public function getUserId() {
		return $this->user_id;
	}

	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}


	public function getCallbackUrl() {
		return $this->callback_url;
	}

	public function setCallbackUrl($callback_url) {
		$this->callback_url = $callback_url;
	}

	public function getIsCallbackDisplay() {
		return $this->is_callback_display;
	}

	public function setIsCallbackDisplay($is_callback_display) {
		$this->is_callback_display = $is_callback_display;
	}

	public function getIsCallbackJavascript() {
		return $this->is_callback_javascript;
	}

	public function setIsCallbackJavascript($is_callback_javascript) {
		$this->is_callback_javascript = $is_callback_javascript;
	}

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

	public function isAnyCallbackSet() {
		return $this->is_callback_display || $this->is_callback_javascript || $this->callback_url;
	}

}

