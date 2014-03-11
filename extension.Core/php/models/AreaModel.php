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
class AreaModel {

	protected $id;
	protected $site_id;
	protected $slug;
	protected $title;
	protected $description;
	protected $country_id;
	protected $parent_area_id;
	protected $is_deleted;
	protected $cache_area_has_parent_generated;
	protected $created_at;
	protected $cached_future_events;
	protected $cached_min_lat;
	protected $cached_max_lat;
	protected $cached_min_lng;
	protected $cached_max_lng;


	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->title = $data['title'];
		$this->description = $data['description'];
		$this->country_id = $data['country_id'];
		$this->parent_area_id = $data['parent_area_id'];
		$this->is_deleted = $data['is_deleted'];
		$this->cache_area_has_parent_generated = $data['cache_area_has_parent_generated'];
		$this->created_at = $data['created_at'];
		$this->cached_future_events = $data['cached_future_events'];
		$this->cached_min_lat = $data['cached_min_lat'];
		$this->cached_max_lat = $data['cached_max_lat'];
		$this->cached_min_lng = $data['cached_min_lng'];
		$this->cached_max_lng = $data['cached_max_lng'];
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function getSlugForUrl() {
		$extraSlug = preg_replace("/[^a-zA-Z0-9\-]+/", "", str_replace(" ", "-",strtolower($this->title)));
		return $this->slug.($extraSlug?"-".$extraSlug:'');
	}
	
	public function setSlug($slug) {
		$this->slug = $slug;
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

	public function getCountryId() {
		return $this->country_id;
	}

	public function setCountryId($country_id) {
		$this->country_id = $country_id;
	}

	public function getParentAreaId() {
		return $this->parent_area_id;
	}

	public function setParentAreaId($parent_area_id) {
		$this->parent_area_id = $parent_area_id;
	}

	public function getIsDeleted() {
		return $this->is_deleted;
	}

	public function setIsDeleted($is_deleted) {
		$this->is_deleted = $is_deleted;
	}

	public function getCacheAreaHasParentGenerated() {
		return $this->cache_area_has_parent_generated;
	}

	public function setCacheAreaHasParentGenerated($cache_area_has_parent_generated) {
		$this->cache_area_has_parent_generated = $cache_area_has_parent_generated;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
	}

	public function getCachedFutureEvents() {
		return $this->cached_future_events;
	}
	
	
	public function getCachedMinLat() {
		return $this->cached_min_lat;
	}

	public function getCachedMaxLat() {
		return $this->cached_max_lat;
	}

	public function getCachedMinLng() {
		return $this->cached_min_lng;
	}

	public function getCachedMaxLng() {
		return $this->cached_max_lng;
	}
	
	
	public function getMinLat() {
		return $this->cached_min_lat;
	}

	public function getMaxLat() {
		return $this->cached_max_lat;
	}

	public function getMinLng() {
		return $this->cached_min_lng;
	}

	public function getMaxLng() {
		return $this->cached_max_lng;
	}

	public function getHasBounds() {
		return (boolean)$this->cached_max_lat;
	}
}

