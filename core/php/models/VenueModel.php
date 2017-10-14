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
class VenueModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
    protected $slug_human;
	protected $title;
	protected $description;
	protected $address;
	protected $address_code;
	protected $lat;
	protected $lng;
	protected $country_id;
	protected $is_deleted;
	protected $area_id;
	protected $is_duplicate_of_id;
	protected $media_venue_slugs;
	protected $cached_future_events;


	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
        $this->slug_human = $data['slug_human'];
		$this->title = $data['title'];
		$this->description = $data['description'];
		$this->address = $data['address'];
		$this->address_code = $data['address_code'];
		$this->lat = $data['lat'];
		$this->lng = $data['lng'];
		$this->country_id = $data['country_id'];
		$this->is_deleted = $data['is_deleted'];
		$this->area_id = $data['area_id'];
		$this->is_duplicate_of_id = $data['is_duplicate_of_id'];
		$this->cached_future_events = $data['cached_future_events'];
		$this->media_venue_slugs = isset($data['media_venue_slugs']) ? $data['media_venue_slugs'] : null;
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

	public function setDescriptionIfDifferent($description) {
		if ($this->description != $description) {
			$this->description = $description;
			return true;
		}
		return false;
	}
	
	public function getAddress() {
		return $this->address;
	}

	public function setAddress($address) {
		$this->address = $address;
	}

	public function setAddressIfDifferent($address) {
		if ($this->address != $address) {
			$this->address = $address;
			return true;
		}
		return false;
	}

	public function getAddressCode() {
		return $this->address_code;
	}

	public function setAddressCode($address_code) {
		$this->address_code = $address_code;
	}

	public function setAddressCodeIfdifferent($address_code) {
		if ($this->address_code) {
			$this->address_code = $address_code;
			return true;
		}
		return false;
	}

	public function hasLatLng() {
		return $this->lat && $this->lng;
	}

	public function getLat() {
		return $this->lat;
	}

	public function setLat($lat) {
		if ($lat != '') {
			$this->lat = $lat;
		}
	}

	public function setLatIfDifferent($lat) {
		if ($this->lat != $lat) {
			$this->lat = $lat;
			return true;
		}
		return false;
	}

	public function getLng() {
		return $this->lng;
	}

	public function setLng($lng) {
		if ($lng != '') {
			$this->lng = $lng;
		}
	}

	public function setLngIfDifferent($lng) {
		if ($this->lng != $lng && $lng != '') {
			$this->lng = $lng;
			return true;
		}
		return false;
	}

	public function getCountryId() {
		return $this->country_id;
	}

	public function setCountryId($country_id) {
		$this->country_id = $country_id;
	}


	public function getIsDeleted() {
		return $this->is_deleted;
	}

	public function setIsDeleted($is_deleted) {
		$this->is_deleted = $is_deleted;
	}


	public function getAreaId() {
		return $this->area_id;
	}

	public function setAreaId($area_id) {
		$this->area_id = $area_id;
	}

	public function setIsDuplicateOfId($is_duplicate_of_id)
	{
		$this->is_duplicate_of_id = $is_duplicate_of_id;
	}

	public function getIsDuplicateOfId()
	{
		return $this->is_duplicate_of_id;
	}

	/**
	 * @return boolean
	 */
	public function hasMediaSlugs()
	{
		return (bool)$this->media_venue_slugs;
	}


	/**
	 * @return mixed
	 */
	public function getMediaSlugsAsList($maxCount = 1000)
	{
		$out = array();
		if ($this->media_venue_slugs) {
			foreach(explode(",",$this->media_venue_slugs) as $slug) {
				if ($slug && !in_array($slug, $out)) {
					$out[] = $slug;
				}
				if (count($out) == $maxCount) {
					return $out;
				}
			}
		}
		return $out;
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

}


