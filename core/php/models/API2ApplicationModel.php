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
class API2ApplicationModel {
	
	protected $id;
	protected $user_id;
	protected $title;
	protected $description;
	protected $app_token;
	protected $app_secret;
	protected $is_write_user_actions = 0;
	protected $is_write_user_profile = 0;
	protected $is_write_calendar = 0;
	protected $is_auto_approve = 0;
	protected $is_all_sites = 1;
	protected $is_callback_url = 1;
	protected $is_callback_display = 1;
	protected $is_callback_javascript = 1;
	protected $allowed_callback_urls;
	protected $is_closed_by_sys_admin = 0;
	protected $closed_by_sys_admin_reason;


	public function setFromDataBaseRow($data) {
		$this->id  = $data['id'];
		$this->user_id  = $data['user_id'];
		$this->title  = $data['title'];
		$this->description  = $data['description'];
		$this->app_token  = $data['app_token'];
		$this->app_secret  = $data['app_secret'];
		$this->is_write_user_actions  = $data['is_write_user_actions'];
		$this->is_write_user_profile  = $data['is_write_user_profile'];
		$this->is_write_calendar  = $data['is_write_calendar'];
		$this->is_auto_approve  = $data['is_auto_approve'];
		$this->is_all_sites  = $data['is_all_sites'];
		$this->is_callback_display  = $data['is_callback_display'];
		$this->is_callback_javascript  = $data['is_callback_javascript'];
		$this->is_callback_url  = $data['is_callback_url'];
		$this->allowed_callback_urls = $data['allowed_callback_urls'];
		$this->is_closed_by_sys_admin = $data['is_closed_by_sys_admin'];
		$this->closed_by_sys_admin_reason = $data['closed_by_sys_admin_reason'];
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getUserId() {
		return $this->user_id;
	}

	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}
		
	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getAppToken() {
		return $this->app_token;
	}

	public function setAppToken($app_token) {
		$this->app_token = $app_token;
	}

	public function getAppSecret() {
		return $this->app_secret;
	}

	public function setAppSecret($app_secret) {
		$this->app_secret = $app_secret;
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

	public function getIsAutoApprove() {
		return $this->is_auto_approve;
	}

	public function setIsAutoApprove($is_auto_approve) {
		$this->is_auto_approve = $is_auto_approve;
	}

	public function getIsAllSites() {
		return $this->is_all_sites;
	}

	public function setIsAllSites($is_all_sites) {
		$this->is_all_sites = $is_all_sites;
	}

	public function getIsCallbackUrl() {
		return $this->is_callback_url;
	}

	public function setIsCallbackUrl($is_callback_url) {
		$this->is_callback_url = $is_callback_url;
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
	
	public function getIsClosedBySysAdmin() {
		return $this->is_closed_by_sys_admin;
	}

	public function setIsClosedBySysAdmin($is_closed_by_sys_admin) {
		$this->is_closed_by_sys_admin = $is_closed_by_sys_admin;
	}

	public function getClosedBySysAdminReason() {
		return $this->closed_by_sys_admin_reason;
	}

	public function setClosedBySysAdminReason($closed_by_sys_admin_reason) {
		$this->closed_by_sys_admin_reason = $closed_by_sys_admin_reason;
	}
	
	public function getAllowedCallbackUrls() {
		return $this->allowed_callback_urls;
	}

	public function setAllowedCallbackUrls($allowed_callback_urls) {
		$this->allowed_callback_urls = $allowed_callback_urls;
	}

	public function addAllowedCallbackUrl($urlToAdd) {
		$this->allowed_callback_urls = trim($this->allowed_callback_urls."\n".$urlToAdd);
	}

	public function removeAllowedCallbackUrl($urlToRemove) {
		$urls = array();
		foreach(explode("\n", $this->allowed_callback_urls) as $url) {
			$urlTrimmed = trim($url);
			if ($urlTrimmed && filter_var($urlTrimmed, FILTER_VALIDATE_URL) && $urlTrimmed != $urlToRemove) {
				$urls[] = $urlTrimmed;
			}
		}
		$this->allowed_callback_urls = implode("\n", $urls);
	}

	public function hasAllowedCallbackUrls() {
		foreach(explode("\n", $this->allowed_callback_urls) as $url) {
			$urlTrimmed = trim($url);
			if ($urlTrimmed && filter_var($urlTrimmed, FILTER_VALIDATE_URL)) {
				return true;
			}
		}
		return false;
	}

	public function isCallbackUrlAllowed($urlToCheck) {
		$urlsCount = 0;
		foreach(explode("\n", $this->allowed_callback_urls) as $url) {
			$urlTrimmed = trim($url);
			if ($urlTrimmed && filter_var($urlTrimmed, FILTER_VALIDATE_URL)) {
				$urlsCount++;
				//print strlen($urlToCheck)." >= ".strlen($urlTrimmed) . "   ". substr($urlToCheck, 0, strlen($urlTrimmed)). "  =  ".$urlTrimmed."\n";
				if (strlen($urlToCheck) >= strlen($urlTrimmed) && substr($urlToCheck, 0, strlen($urlTrimmed)) == $urlTrimmed) {
					return true;
				}
			}
		}
		// if there are no URLs then we allow all URLs
		return !$urlsCount;
	}


	
}

