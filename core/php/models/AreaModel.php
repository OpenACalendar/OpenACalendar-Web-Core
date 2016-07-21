<?php


namespace models;

use Slugify;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class AreaModel {

	protected $id;
	protected $site_id;
	protected $slug;
    protected $slug_human;
	protected $title;
	protected $description;
	protected $country_id;
	protected $parent_area_id;
	protected $is_deleted;
	protected $cache_area_has_parent_generated;
	protected $created_at;
	protected $cached_future_events;
	protected $min_lat;
	protected $max_lat;
	protected $min_lng;
	protected $max_lng;
	protected $is_duplicate_of_id;

	protected $parent_1_title;


	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
        $this->slug_human = $data['slug_human'];
		$this->title = $data['title'];
		$this->description = $data['description'];
		$this->country_id = $data['country_id'];
		$this->parent_area_id = $data['parent_area_id'];
		$this->is_deleted = $data['is_deleted'];
		$this->cache_area_has_parent_generated = $data['cache_area_has_parent_generated'];
		$this->created_at = $data['created_at'];
		$this->cached_future_events = $data['cached_future_events'];
		$this->min_lat = $data['min_lat'];
		$this->max_lat = $data['max_lat'];
		$this->min_lng = $data['min_lng'];
		$this->max_lng = $data['max_lng'];
		$this->parent_1_title = isset($data['parent_1_title']) ? $data['parent_1_title'] : null;
		$this->is_duplicate_of_id = $data['is_duplicate_of_id'];
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
        global $app;
        if ($this->slug_human) {
            return $this->slug."-".$this->slug_human;
        } else {
            $slugify = new Slugify($app);
            $extraSlug = $slugify->process($this->title);
            return $this->slug.($extraSlug?"-".$extraSlug:'');
        }
    }
	
	public function setSlug($slug) {
		$this->slug = $slug;
	}

    /**
     * @param mixed $slug_human
     */
    public function setSlugHuman($slug_human)
    {
        $this->slug_human = $slug_human;
    }

    /**
     * @return mixed
     */
    public function getSlugHuman()
    {
        return $this->slug_human;
    }



	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setTitleIfDifferent($title) {
		if ($this->title != $title) {
			$this->title = $title;
			return true;
		}
		return false;
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

	/**
	 * @param mixed $cached_future_events
	 */
	public function setCachedFutureEvents($cached_future_events)
	{
		$this->cached_future_events = $cached_future_events;
	}

	public function getMinLat() {
		return $this->min_lat;
	}

	public function getMaxLat() {
		return $this->max_lat;
	}

	public function getMinLng() {
		return $this->min_lng;
	}

	public function getMaxLng() {
		return $this->max_lng;
	}

	public function getHasBounds() {
		return (boolean)$this->max_lat;
	}

    /**
     * @param mixed $max_lat
     */
    public function setMaxLat($max_lat)
    {
        $this->max_lat = $max_lat;
    }

    /**
     * @param mixed $max_lng
     */
    public function setMaxLng($max_lng)
    {
        $this->max_lng = $max_lng;
    }

    /**
     * @param mixed $min_lat
     */
    public function setMinLat($min_lat)
    {
        $this->min_lat = $min_lat;
    }

    /**
     * @param mixed $min_lng
     */
    public function setMinLng($min_lng)
    {
        $this->min_lng = $min_lng;
    }



	/**
	 * @return mixed
	 */
	public function getParent1Title()
	{
		return $this->parent_1_title;
	}

	public function setIsDuplicateOfId($is_duplicate_of_id)
	{
		$this->is_duplicate_of_id = $is_duplicate_of_id;
	}

	public function getIsDuplicateOfId()
	{
		return $this->is_duplicate_of_id;
	}

	public function getIsAllowedForAfterGetUser() {
		return !$this->is_deleted;
	}


}

