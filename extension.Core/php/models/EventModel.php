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
class EventModel {
	
	protected $id;
	protected $site_id;
	protected $site_slug;
	protected $slug;
	protected $summary;
	protected $description;
	protected $group_id;
	protected $group_title;
	protected $is_deleted;
	protected $event_recur_set_id;
	protected $timezone = 'Europe/London';
	protected $venue_id;
	protected $country_id;
	protected $area_id;
	protected $import_url_id;
	protected $import_id;
	protected $url;
	protected $is_virtual = false;
	protected $is_physical = true;


	/** @var DateTime **/
	protected $start_at;
	/** @var DateTime **/
	protected $end_at;
	/** @var DateTime **/
	protected $created_at;

	protected $venue_lat;
	protected $venue_lng;
	protected $venue_title;
	protected $venue_slug;
	protected $user_is_plan_attending  = false;
	protected $user_is_plan_maybe_attending = false;

	public function setDefaultOptionsFromSite(SiteModel $site) {
		if ($site->getIsFeaturePhysicalEvents() && !$site->getIsFeatureVirtualEvents()) {
			$this->is_physical = true;
			$this->is_virtual = false;
		} else if (!$site->getIsFeaturePhysicalEvents() && $site->getIsFeatureVirtualEvents()) {
			$this->is_physical = false;
			$this->is_virtual = true;
		}				
	}	

	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->site_slug = isset($data['site_slug']) ? $data['site_slug'] : null;
		$this->slug = $data['slug'];
		$this->summary = $data['summary'];
		$this->description = $data['description'];
		$utc = new \DateTimeZone("UTC");
		$this->start_at = new \DateTime($data['start_at'], $utc);
		$this->end_at = new \DateTime($data['end_at'], $utc);
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->group_id = isset($data['group_id']) ? $data['group_id'] : null;
		$this->group_title = isset($data['group_title']) ? $data['group_title'] : null;
		$this->is_deleted = $data['is_deleted'];
		$this->event_recur_set_id = $data['event_recur_set_id'];
		$this->country_id = $data['country_id'];
		$this->venue_id = $data['venue_id'];
		$this->area_id = $data['area_id'];
		$this->timezone = $data['timezone'];
		$this->import_id = $data['import_id'];
		$this->import_url_id = $data['import_url_id'];
		$this->url = $data['url'];
		$this->venue_title = isset($data['venue_title']) ? $data['venue_title'] : null;
		$this->venue_lat = isset($data['venue_lat']) ? $data['venue_lat'] : null;
		$this->venue_lng = isset($data['venue_lng']) ? $data['venue_lng'] : null;
		$this->venue_slug = isset($data['venue_slug']) ? $data['venue_slug'] : null;
		$this->user_is_plan_attending = isset($data['user_is_plan_attending']) ? (boolean)$data['user_is_plan_attending'] : false;
		$this->user_is_plan_maybe_attending = isset($data['user_is_plan_maybe_attending']) ? (boolean)$data['user_is_plan_maybe_attending'] : false;
		$this->is_virtual = (boolean)$data['is_virtual'];
		$this->is_physical = (boolean)$data['is_physical'];
	}
	
	public function setFromHistory(EventHistoryModel $ehm) {
		$this->summary = $ehm->getSummary();
		$this->description = $ehm->getDescription();
		$this->start_at = clone $ehm->getStartAt();
		$this->end_at = clone $ehm->getEndAt();
		$this->is_deleted = false;
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
	
	public function getSiteSlug() {
		return $this->site_slug;
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
		$extraSlug = strtr( $this->summary, $unwanted_array );
		$extraSlug = preg_replace("/[^a-zA-Z0-9\-]+/", "", str_replace(" ", "-",strtolower($extraSlug)));
		return $this->slug.($extraSlug?"-".$extraSlug:'');
	}
	
	public function setSlug($slug) {
		$this->slug = $slug;
	}

	public function getSummary() {
		return $this->summary;
	}

	public function getSummaryDisplay() {
		if ($this->group_title && $this->summary &&  $this->group_title == $this->summary) {
			return $this->summary;
		} else if ($this->group_title && $this->summary &&  $this->group_title != $this->summary) {
			if (strpos($this->summary, $this->group_title) === false) {
				return $this->group_title.": ".$this->summary;
			} else {
				return $this->summary;
			}
		} else if ($this->group_title) {
			return $this->group_title;
		} else if ($this->summary) {
			return $this->summary;
		} else {
			return 'Event';
		}	
	} 
	
	public function setSummary($summary) {
		$this->summary = $summary;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getStartAt() {
		return $this->start_at;
	}

	public function setStartAt(\DateTime $start_at) {
		$this->start_at = $start_at;
	}

	public function getEndAt() {
		return $this->end_at;
	}

	public function setEndAt(\DateTime $end_at) {
		$this->end_at = $end_at;
	}
	
	public function getCreatedAt() {
		return $this->created_at;
	}
	
	public function getGroupId() {
		return $this->group_id;
	}

	public function setGroupId($group_id) {
		$this->group_id = $group_id;
	}

	public function setGroup(GroupModel $group) {
		$this->group_id = $group->getId();
	}

	public function getGroupTitle() {
		return $this->group_title;
	}

	public function setGroupTitle($group_title) {
		$this->group_title = $group_title;
	}
	
	public function getIsDeleted() {
		return $this->is_deleted;
	}

	public function setIsDeleted($is_deleted) {
		$this->is_deleted = $is_deleted;
	}

	public function getEventRecurSetId() {
		return $this->event_recur_set_id;
	}

	public function setEventRecurSetId($event_recur_set_id) {
		$this->event_recur_set_id = $event_recur_set_id;
	}

	public function getTimezone() {
		return $this->timezone;
	}

	public function setTimezone($timezone) {
		$this->timezone = $timezone;
	}

	public function getCountryId() {
		return $this->country_id;
	}

	public function setCountryId($country_id) {
		$this->country_id = $country_id;
	}

		public function isInPast() {
		return $this->end_at->getTimeStamp() < \TimeSource::time();
	}
	
	public function getVenueId() {
		return $this->venue_id;
	}

	public function setVenueId($venue_id) {
		$this->venue_id = $venue_id;
	}
	
	public function getAreaId() {
		return $this->area_id;
	}

	public function setAreaId($area_id) {
		$this->area_id = $area_id;
	}

	public function getImportUrlId() {
		return $this->import_url_id;
	}

	public function setImportUrlId($import_url_id) {
		$this->import_url_id = $import_url_id;
		return $this;
	}

	public function getImportId() {
		return $this->import_id;
	}
	
	public function getIsImported() {
		return $this->import_id;
	}

	public function setImportId($import_id) {
		$this->import_id = $import_id;
		return $this;
	}
	
	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}
	
	public function getVenueLat() {
		return $this->venue_lat;
	}

	public function getVenueSlug() {
		return $this->venue_slug;
	}

	public function getVenueLng() {
		return $this->venue_lng;
	}

	public function getVenueTitle() {
		return $this->venue_title;
	}

	public function getUserIsPlanAttending() {
		return $this->user_is_plan_attending;
	}

	public function getUserIsPlanMaybeAttending() {
		return $this->user_is_plan_maybe_attending;
	}

	public function getIsVirtual() {
		return $this->is_virtual;
	}

	public function setIsVirtual($is_virtual) {
		$this->is_virtual = $is_virtual;
		return $this;
	}

	public function getIsPhysical() {
		return $this->is_physical;
	}

	public function setIsPhysical($is_physical) {
		$this->is_physical = $is_physical;
		return $this;
	}


	
}


