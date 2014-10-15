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
	protected $is_editor;
	protected $state_from_user;

	public function setFromDataBaseRow($data) {
		$this->api2_application_id   = $data['api2_application_id'];
		$this->request_token   = $data['request_token'];
		$this->created_at   = $data['created_at'];
		$this->used_at   = $data['used_at'];
		$this->user_id   = $data['user_id'];
		$this->callback_url   = $data['callback_url'];
		$this->is_callback_display   = $data['is_callback_display'];
		$this->is_callback_javascript   = $data['is_callback_javascript'];
		$this->is_editor  = (boolean)$data['is_editor'];
		$this->state_from_user  = $data['state_from_user'];
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
	
	public function getCallbackUrlWithParams($params) {
		$url = $this->callback_url;
		if (strpos($url, "?")) {
			if (substr($url, -1) != '&') {
				$url .= '&';
			}
		} else {
			$url .= '?';
		}
		$paramsBits = array();
		foreach($params as $k=>$v) {
			$paramsBits[] =  $k.'='.urlencode($v);
		}
		return $url . implode('&', $paramsBits);
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



	public function isAnyCallbackSet() {
		return $this->is_callback_display || $this->is_callback_javascript || $this->callback_url;
	}

	public function getStateFromUser() {
		return $this->state_from_user;
	}

	public function setStateFromUser($state_from_user) {
		$this->state_from_user = $state_from_user;
	}
	
	public function getIsUsed() {
		return (boolean)$this->used_at;
	}


	
}

