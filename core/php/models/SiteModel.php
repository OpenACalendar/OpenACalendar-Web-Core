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
class SiteModel {
	
	public static function  makeCanonicalSlug($slug) {
		return trim(strtolower($slug));
	}
	
	protected $id;
	protected $title;
	protected $slug;
	protected $description_text;
	protected $footer_text;
	protected $is_web_robots_allowed = true;
	protected $is_closed_by_sys_admin = false;
	protected $is_listed_in_index = true;
	protected $closed_by_sys_admin_reason;
	protected $is_feature_map = false;
	protected $is_feature_importer = false;
	protected $is_feature_curated_list =  false;
	protected $is_feature_virtual_events =  false;
	protected $is_feature_physical_events =  true;
	protected $is_feature_group =  true;
	protected $is_feature_tag =  false;
	protected $prompt_emails_days_in_advance = 30;

	protected $cached_is_multiple_timezones = false;
	protected $cached_is_multiple_countries = false;
	protected $cached_timezones;
	
	/** comes from site_profile_media_information table **/
	protected $logo_media_id;
	
	protected $site_quota_id;

	protected $created_at;

	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->title = $data['title'];
		$this->slug = $data['slug'];
		$this->description_text = $data['description_text'];
		$this->footer_text = $data['footer_text'];
		$this->is_web_robots_allowed = $data['is_web_robots_allowed'];
		$this->is_closed_by_sys_admin = $data['is_closed_by_sys_admin'];
		$this->closed_by_sys_admin_reason = $data['closed_by_sys_admin_reason'];
		$this->is_listed_in_index = $data['is_listed_in_index'];
		$this->cached_is_multiple_countries = $data['cached_is_multiple_countries'];
		$this->cached_is_multiple_timezones = $data['cached_is_multiple_timezones'];
		$this->cached_timezones = $data['cached_timezones'];
		$this->site_quota_id = $data['site_quota_id'];
		$this->logo_media_id = isset($data['logo_media_id']) ? $data['logo_media_id'] : null;
		$this->is_feature_map = (boolean)$data['is_feature_map'];
		$this->is_feature_importer = (boolean)$data['is_feature_importer'];
		$this->is_feature_curated_list = (boolean)$data['is_feature_curated_list'];
		$this->prompt_emails_days_in_advance = max(1,intval($data['prompt_emails_days_in_advance']));
		$this->is_feature_virtual_events = (boolean)$data['is_feature_virtual_events'];
		$this->is_feature_physical_events = (boolean)$data['is_feature_physical_events'];
		$this->is_feature_group = (boolean)$data['is_feature_group'];
		$this->is_feature_tag = (boolean)$data['is_feature_tag'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);		
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function setSlug($slug) {
		$this->slug = $slug;
	}
	
	public function getDescriptionText() {
		return $this->description_text;
	}

	public function setDescriptionText($description) {
		$this->description_text = $description;
	}

	public function getFooterText() {
		return $this->footer_text;
	}

	public function setFooterText($footer_text) {
		$this->footer_text = $footer_text;
	}
	
	public function getIsWebRobotsAllowed() {
		return $this->is_web_robots_allowed;
	}

	public function setIsWebRobotsAllowed($is_web_robots_allowed) {
		$this->is_web_robots_allowed = $is_web_robots_allowed;
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

	public function setClosedBySysAdminreason($closed_by_sys_admin_reason) {
		$this->closed_by_sys_admin_reason = $closed_by_sys_admin_reason;
	}


	
	public function getIsListedInIndex() {
		return $this->is_listed_in_index;
	}

	public function setIsListedInIndex($is_listed_in_index) {
		$this->is_listed_in_index = $is_listed_in_index;
	}
	
	public function getCachedIsMultipleTimezones() {
		return $this->cached_is_multiple_timezones;
	}

	public function setCachedIsMultipleTimezones($cached_is_multiple_timezones) {
		$this->cached_is_multiple_timezones = $cached_is_multiple_timezones;
	}

	public function getCachedIsMultipleCountries() {
		return $this->cached_is_multiple_countries;
	}

	public function setCachedIsMultipleCountries($cached_is_multiple_countries) {
		$this->cached_is_multiple_countries = $cached_is_multiple_countries;
	}

	public function getCachedTimezones() {
		return $this->cached_timezones;
	}

	public function setCachedTimezones($cached_timezones) {
		$this->cached_timezones = $cached_timezones;
	}

	public function getCachedTimezonesAsList() {
		return explode(",",$this->cached_timezones);
	}

	/** Also does setCachedIsMultipleTimezones() **/
	public function setCachedTimezonesAsList($timezones) {
		$array = array_unique($timezones);
		sort($array, SORT_STRING);
		$this->cached_timezones = implode(",", $array);
		$this->cached_is_multiple_timezones = (count($array) > 1);
	}

	public function getLogoCacheKey() {
		return $this->logo_media_id ? md5($this->logo_media_id) : 'null';
	}
	
	public function getLogoMediaId() {
		return $this->logo_media_id;
	}

	public function setLogoMediaId($logo_media_id) {
		$this->logo_media_id = $logo_media_id;
		return $this;
	}
	
	public function getIsFeatureMap() {
		return $this->is_feature_map;
	}

	public function setIsFeatureMap($is_feature_map) {
		$this->is_feature_map = $is_feature_map;
		return $this;
	}

	public function getIsFeatureImporter() {
		return $this->is_feature_importer;
	}

	public function setIsFeatureImporter($is_feature_importer) {
		$this->is_feature_importer = $is_feature_importer;
		return $this;
	}

	public function getIsFeatureCuratedList() {
		return $this->is_feature_curated_list;
	}

	public function setIsFeatureCuratedList($is_feature_curated_list) {
		$this->is_feature_curated_list = $is_feature_curated_list;
		return $this;
	}
	
	public function getPromptEmailsDaysInAdvance() {
		return $this->prompt_emails_days_in_advance;
	}

	public function setPromptEmailsDaysInAdvance($prompt_emails_days_in_advance) {
		$val = intval($prompt_emails_days_in_advance);
		$this->prompt_emails_days_in_advance = $val ? max(  1, min(60, $val) ): 30;
		return $this;
	}

	public function getIsFeatureVirtualEvents() {
		return $this->is_feature_virtual_events;
	}

	public function setIsFeatureVirtualEvents($is_feature_virtual_events) {
		$this->is_feature_virtual_events = $is_feature_virtual_events;
		return $this;
	}

	public function getIsFeaturePhysicalEvents() {
		return $this->is_feature_physical_events;
	}

	public function setIsFeaturePhysicalEvents($is_feature_physical_events) {
		$this->is_feature_physical_events = $is_feature_physical_events;
		return $this;
	}

	public function getIsFeatureGroup() {
		return $this->is_feature_group;
	}

	public function setIsFeatureGroup($is_feature_group) {
		$this->is_feature_group = $is_feature_group;
		return $this;
	}
	
	
	public function getIsFeatureTag() {
		return $this->is_feature_tag;
	}

	public function setIsFeatureTag($is_feature_tag) {
		$this->is_feature_tag = $is_feature_tag;
		return $this;
	}
	
	
	public function getSiteQuotaId() {
		return $this->site_quota_id;
	}

	public function setSiteQuotaId($site_quota_id) {
		$this->site_quota_id = $site_quota_id;
		return $this;
	}


	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}


}

