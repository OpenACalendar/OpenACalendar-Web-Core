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
	protected $is_cancelled;
	protected $event_recur_set_id;
	protected $timezone = 'Europe/London';
	protected $venue_id;
	protected $country_id;
	protected $area_id;
	protected $import_url_id;
	protected $import_id;
	protected $url;
	protected $ticket_url;
	protected $is_virtual = false;
	protected $is_physical = true;
	protected $is_duplicate_of_id;
	protected $media_event_slugs;
	protected $media_group_slugs;
	protected $media_venue_slugs;


	/** @var DateTime **/
	protected $start_at;
	/** @var DateTime **/
	protected $end_at;
	/** @var DateTime **/
	protected $created_at;

	/** @var VenueModel **/
	protected $venue;
	
	/** @var AreaModel **/
	protected $area;

	/** @var Country */
	protected $country;
	
	protected $user_is_plan_attending  = false;
	protected $user_is_plan_maybe_attending = false;

	protected $is_event_in_curated_list = false;
	protected $in_curated_list_group_id;
	protected $in_curated_list_group_slug;
	protected $in_curated_list_group_title;

	protected $custom_fields = array();

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
		$this->created_at = $data['created_at'] ? new \DateTime($data['created_at'], $utc) : null;
		$this->group_id = isset($data['group_id']) ? $data['group_id'] : null;
		$this->group_title = isset($data['group_title']) ? $data['group_title'] : null;
		$this->is_deleted = $data['is_deleted'];
		$this->is_cancelled = $data['is_cancelled'];
		$this->event_recur_set_id = $data['event_recur_set_id'];
		$this->country_id = $data['country_id'];
		$this->venue_id = $data['venue_id'];
		$this->area_id = $data['area_id'];
		$this->timezone = $data['timezone'];
		$this->import_id = $data['import_id'];
		$this->import_url_id = $data['import_url_id'];
		$this->url = $data['url'];
		$this->ticket_url = $data['ticket_url'];
		if (isset($data['venue_slug'])) {
			$this->venue = new VenueModel();
			$this->venue->setTitle($data['venue_title']);
			$this->venue->setSlug($data['venue_slug']);
			$this->venue->setLat($data['venue_lat']);
			$this->venue->setLng($data['venue_lng']);
			$this->venue->setDescription($data['venue_description']);
			$this->venue->setAddress($data['venue_address']);
			$this->venue->setAddressCode($data['venue_address_code']);
		}
		
		if (isset($data['area_slug'])) {
			$this->area = new AreaModel();
			$this->area->setId($data['area_information_id']);
			$this->area->setTitle($data['area_title']);
			$this->area->setSlug($data['area_slug']);
		}
		if (isset($data['country_two_char_code'])) {
			$this->country = new CountryModel();
			$this->country->setTwoCharCode($data['country_two_char_code']);
			$this->country->setTitle($data['country_title']);
		}
		$this->user_is_plan_attending = isset($data['user_is_plan_attending']) ? (boolean)$data['user_is_plan_attending'] : false;
		$this->user_is_plan_maybe_attending = isset($data['user_is_plan_maybe_attending']) ? (boolean)$data['user_is_plan_maybe_attending'] : false;
		$this->is_virtual = (boolean)$data['is_virtual'];
		$this->is_physical = (boolean)$data['is_physical'];
		$this->is_duplicate_of_id = $data['is_duplicate_of_id'];
		$this->is_event_in_curated_list = isset($data['is_event_in_curated_list']) ? (bool)$data['is_event_in_curated_list'] : false;
		$this->in_curated_list_group_id = isset($data['in_curated_list_group_id']) ? $data['in_curated_list_group_id'] : null;
		$this->in_curated_list_group_slug = isset($data['in_curated_list_group_slug']) ? $data['in_curated_list_group_slug'] : null;
		$this->in_curated_list_group_title = isset($data['in_curated_list_group_title']) ? $data['in_curated_list_group_title'] : null;
		$this->media_event_slugs = isset($data['media_event_slugs']) ? $data['media_event_slugs'] : null;
		$this->media_group_slugs = isset($data['media_group_slugs']) ? $data['media_group_slugs'] : null;
		$this->media_venue_slugs = isset($data['media_venue_slugs']) ? $data['media_venue_slugs'] : null;
		if ($data['custom_fields'] && $data['custom_fields'] != '[]') {
			$obj = json_decode($data['custom_fields']);
			foreach(get_object_vars($obj) as $k=>$v) {
				$this->custom_fields[$k] = $v;
			}
		}
	}
	
	public function setFromHistory(EventHistoryModel $ehm) {
		$this->summary = $ehm->getSummary();
		$this->description = $ehm->getDescription();
		$this->start_at = clone $ehm->getStartAt();
		$this->end_at = clone $ehm->getEndAt();
		$this->is_deleted = false;
		$this->is_cancelled = false;
        $this->custom_fields = $ehm->custom_fields;
	}
	
	protected $validateErrors = array();
	
	public function getValidateErrors() {
		return $this->validateErrors;
	}
	
	public function validate() {
		$this->validateErrors = array();
		if (!$this->start_at) {
			$this->validateErrors[] = 'Start not set!';
		}
		if (!$this->end_at) {
			$this->validateErrors[] = 'End not set!';
		}
		if ($this->start_at && $this->end_at && $this->start_at->getTimestamp() > $this->end_at->getTimestamp()) {
			$this->validateErrors[] = 'The Start can not be after the end!';
		}
		return $this->validateErrors ? false : true;
	}
	
	public function setFromImportedEventModel(ImportedEventModel $importedEvent, $startAt = null, $endAt = null) {
		$changesToSave = false;
		if ($importedEvent->getTitle() != $this->getSummary()) {
			$this->setSummary($importedEvent->getTitle());
			$changesToSave = true;
		}
		if ($importedEvent->getDescription() != $this->getDescription()) {
			$this->setDescription($importedEvent->getDescription());
			$changesToSave = true;
		}
		if (!$startAt) {
			$startAt = $importedEvent->getStartAtInUTC();
		}
		if (!$this->getStartAt() || $startAt->getTimeStamp() != $this->getStartAtInUTC()->getTimeStamp()) {
			$this->setStartAt(clone $startAt);
			$changesToSave = true;
		}
		if (!$endAt) {
			$endAt = $importedEvent->getEndAtInUTC();
		}
		if (!$this->getEndAt() || $endAt->getTimeStamp() != $this->getEndAtInUTC()->getTimeStamp()) {
			$this->setEndAt(clone $endAt);
			$changesToSave = true;
		}
		if ($importedEvent->getUrl() != $this->getUrl()) {
			$this->setUrl($importedEvent->getUrl());
			$changesToSave = true;
		}			
		if ($importedEvent->getTicketUrl() != $this->getTicketUrl()) {
			$this->setTicketUrl($importedEvent->getTicketUrl());
			$changesToSave = true;
		}			
		return $changesToSave;
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
		$extraSlug = strtr( trim($this->summary), $unwanted_array );
		$extraSlug = preg_replace("/[^a-zA-Z0-9\-]+/", "", str_replace(" ", "-",strtolower($extraSlug)));
		// Do it twice to get ---'s turned to -'s to.
		$extraSlug = str_replace("--", "-", $extraSlug);
		$extraSlug = str_replace("--", "-", $extraSlug);
		return $this->slug.($extraSlug?"-".$extraSlug:'');
	}
	
	public function setSlug($slug) {
		$this->slug = $slug;
	}

	public function getSummary() {
		return $this->summary;
	}

	public function getSummaryDisplay() {
		if ($this->group_title && $this->summary &&  strtolower($this->group_title) == strtolower($this->summary)) {
			return $this->summary;
		} else if ($this->group_title && $this->summary &&  strtolower($this->group_title) != strtolower($this->summary)) {
			if (stripos($this->summary, $this->group_title) === false) {
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

	public function setSummaryIfDifferent($summary) {
		if ($this->summary != $summary) {
			$this->summary = $summary;
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

	public function getStartAt() {
		return $this->start_at;
	}
	
	
	public function getStartAtInUTC() {
		if ($this->start_at->getTimezone() == 'UTC') {
			return $this->start_at;
		} else {
			$sa = clone $this->start_at;
			$sa->setTimezone(new \DateTimeZone("UTC"));
			return $sa;
		}
	}

	public function getStartAtInTimezone() {
		if ($this->start_at->getTimezone() == $this->timezone) {
			return $this->start_at;
		} else {
			$sa = clone $this->start_at;
			$sa->setTimezone(new \DateTimeZone($this->timezone));
			return $sa;
		}
	}

	public function setStartAt(\DateTime $start_at) {
		$this->start_at = $start_at;
	}

	public function getEndAt() {
		return $this->end_at;
	}

	
	public function getEndAtInUTC() {
		if ($this->end_at->getTimezone() == 'UTC') {
			return $this->end_at;
		} else {
			$ea = clone $this->end_at;
			$ea->setTimezone(new \DateTimeZone("UTC"));
			return $ea;
		}
	}

	public function getEndAtInTimezone() {
		if ($this->end_at->getTimezone() == $this->timezone) {
			return $this->end_at;
		} else {
			$ea = clone $this->end_at;
			$ea->setTimezone(new \DateTimeZone($this->timezone));
			return $ea;
		}
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

	public function setIsCancelled($is_cancelled)
	{
		$this->is_cancelled = $is_cancelled;
	}

	public function getIsCancelled()
	{
		return $this->is_cancelled;
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

	public function setUrlIfDifferent($url) {
		if ($this->url != $url) {
			$this->url = $url;
			return true;
		}
		return false;
	}

	public function getTicketUrl() {
		return $this->ticket_url;
	}

	public function setTicketUrl($ticket_url) {
		$this->ticket_url = $ticket_url;
	}

	public function setTicketUrlIfDifferent($ticket_url) {
		if ($this->ticket_url != $ticket_url) {
			$this->ticket_url = $ticket_url;
			return true;
		}
		return false;
	}

		
	/**
	 * @return VenueModel
	 */
	public function getVenue() {
		return $this->venue;
	}

	/** 
	 * @return AreaModel 
	 **/
	public function getArea() {
		return $this->area;
	}

	/**
	 * @return \models\Country
	 */
	public function getCountry()
	{
		return $this->country;
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

	public function setIsDuplicateOfId($is_duplicate_of_id)
	{
		$this->is_duplicate_of_id = $is_duplicate_of_id;
	}

	public function getIsDuplicateOfId()
	{
		return $this->is_duplicate_of_id;
	}

	/**
	 * @return mixed
	 */
	public function getInCuratedListGroupId()
	{
		return $this->in_curated_list_group_id;
	}

	/**
	 * @return mixed
	 */
	public function getInCuratedListGroupSlug()
	{
		return $this->in_curated_list_group_slug;
	}

	/**
	 * @return mixed
	 */
	public function getInCuratedListGroupTitle()
	{
		return $this->in_curated_list_group_title;
	}

	/**
	 * @return boolean
	 */
	public function getIsEventInCuratedList()
	{
		return $this->is_event_in_curated_list;
	}

	/**
	 * @return boolean
	 */
	public function hasMediaSlugs()
	{
		return (bool)$this->media_event_slugs || (bool)$this->media_group_slugs || (bool)$this->media_venue_slugs;
	}


	/**
	 * @return mixed
	 */
	public function getMediaSlugsAsList($maxCount = 1000)
	{
		$out = array();
		if ($this->media_event_slugs) {
			foreach(explode(",",$this->media_event_slugs) as $slug) {
				if ($slug && !in_array($slug, $out)) {
					$out[] = $slug;
					if (count($out) == $maxCount) {
						return $out;
					}
				}
			}
		}
		if ($this->media_group_slugs) {
			foreach(explode(",",$this->media_group_slugs) as $slug) {
				if ($slug && !in_array($slug, $out)) {
					$out[] = $slug;
				}
				if (count($out) == $maxCount) {
					return $out;
				}
			}
		}
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

	public function getIsAllowedForAfterGetUser() {
		return !$this->is_deleted; // TODO add check for events in past to
	}

	/**
	 * @return mixed
	 */
	public function getCustomFields()
	{
		return $this->custom_fields;
	}

	/**
	 * @param mixed $custom_fields
	 */
	public function setCustomField(EventCustomFieldDefinitionModel $customField , $value)
	{
		$this->custom_fields[$customField->getId()] = $value;
	}

	public function hasCustomField(EventCustomFieldDefinitionModel $customField) {
		return isset($this->custom_fields[$customField->getId()]) && $this->custom_fields[$customField->getId()];
	}

	public function getCustomField(EventCustomFieldDefinitionModel $customField) {
		return isset($this->custom_fields[$customField->getId()]) ? $this->custom_fields[$customField->getId()] : null;
	}
	
}


