<?php


namespace models;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
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
		$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 
                            'Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i', 'ş'=>'s', 'ü'=>'u', 
                            'ă'=>'a', 'Ă'=>'A', 'ș'=>'s', 'Ș'=>'S', 'ț'=>'t', 'Ț'=>'T'
                            );
		$extraSlug = strtr( trim($this->title), $unwanted_array );
		$extraSlug = preg_replace("/[^a-zA-Z0-9\-]+/", "", str_replace(" ", "-",strtolower($extraSlug)));
		// Do it twice to get ---'s turned to -'s to.
		$extraSlug = str_replace("--", "-", $extraSlug);
		$extraSlug = str_replace("--", "-", $extraSlug);
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
		if ($this->lng != $lng && $lng != '') {
			$this->lng = $lng;
			return true;
		}
		return false;
	}

	public function setLngIfDifferent($lng) {
		$this->lng = $lng;
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


