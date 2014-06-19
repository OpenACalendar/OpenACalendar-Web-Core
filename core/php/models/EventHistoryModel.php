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
class EventHistoryModel extends EventModel {

	protected $event_slug;
	//protected $user_account_id;
	protected $created_at; 
	protected $reverted_from_created_at;
	protected $user_account_id;
	protected $user_account_username;
	

	protected $summary_changed   = 0;
	protected $description_changed   = 0;
	protected $start_at_changed   = 0;
	protected $end_at_changed   = 0;
	protected $is_deleted_changed   = 0;
	protected $country_id_changed   = 0;
	protected $timezone_changed   = 0;
	protected $venue_id_changed   = 0;
	protected $url_changed   = 0;
	protected $ticket_url_changed   = 0;
	protected $is_virtual_changed   = 0;
	protected $is_physical_changed   = 0;
	protected $area_id_changed   = 0;

	protected $is_new = 0;

	


	public function setFromDataBaseRow($data) {
		$this->id = $data['event_id'];
		$this->event_slug = isset($data['event_slug']) ? $data['event_slug'] : null;
		$this->summary = $data['summary'];
		$this->group_title = isset($data['group_title']) ? $data['group_title'] : null;
		$this->description = $data['description'];
		$utc = new \DateTimeZone("UTC");
		$this->start_at = new \DateTime($data['start_at'], $utc);
		$this->end_at = new \DateTime($data['end_at'], $utc);
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->group_id = isset($data['group_id']) ? $data['group_id'] : null;
		$this->is_deleted = $data['is_deleted'];
		$this->country_id  = $data['country_id'];
		$this->timezone  = $data['timezone'];
		$this->venue_id  = $data['venue_id'];
		$this->url  = $data['url'];
		$this->ticket_url  = $data['ticket_url'];
		$this->is_virtual  = $data['is_virtual'];
		$this->is_physical  = $data['is_physical'];
		$this->area_id  = $data['area_id'];
		$this->user_account_id = $data['user_account_id'];
		$this->user_account_username = isset($data['user_account_username']) ? $data['user_account_username'] : null;
		$this->summary_changed  = isset($data['summary_changed']) ? $data['summary_changed'] : 0;
		$this->description_changed  = isset($data['description_changed']) ? $data['description_changed'] : 0;
		$this->start_at_changed  = isset($data['start_at_changed']) ? $data['start_at_changed'] : 0;
		$this->end_at_changed  = isset($data['end_at_changed']) ? $data['end_at_changed'] : 0;
		$this->is_deleted_changed  = isset($data['is_deleted_changed']) ? $data['is_deleted_changed'] : 0;
		$this->country_id_changed = isset($data['country_id_changed']) ? $data['country_id_changed'] : 0;
		$this->timezone_changed  = isset($data['timezone_changed']) ? $data['timezone_changed'] : 0;
		$this->venue_id_changed  = isset($data['venue_id_changed']) ? $data['venue_id_changed'] : 0;
		$this->url_changed  = isset($data['url_changed']) ? $data['url_changed'] : 0;
		$this->ticket_url_changed  = isset($data['ticket_url_changed']) ? $data['ticket_url_changed'] : 0;
		$this->is_virtual_changed  = isset($data['is_virtual_changed']) ? $data['is_virtual_changed'] : 0;
		$this->is_physical_changed  = isset($data['is_physical_changed']) ? $data['is_physical_changed'] : 0;
		$this->area_id_changed  = isset($data['area_id_changed']) ? $data['area_id_changed'] : 0;
		$this->is_new = isset($data['is_new']) ? $data['is_new'] : 0;
	}
	
	public function isAnyChangeFlagsUnknown() {
		return $this->summary_changed == 0 ||
			$this->description_changed == 0 ||
			$this->start_at_changed == 0 ||
			$this->end_at_changed == 0 ||
			$this->is_deleted_changed == 0 ||
			$this->country_id_changed == 0 ||
			$this->timezone_changed == 0 ||
			$this->venue_id_changed == 0 ||
			$this->url_changed == 0 ||
			$this->ticket_url_changed == 0 ||
			$this->is_virtual_changed == 0 ||
			$this->is_physical_changed == 0 ||
			$this->area_id_changed == 0;
	}
	
	public function setChangedFlagsFromNothing() {
		$this->summary_changed = $this->summary ? 1 : -1;
		$this->description_changed = $this->description ? 1 : -1;
		$this->start_at_changed = $this->start_at ? 1 : -1;
		$this->end_at_changed = $this->end_at ? 1 : -1;
		$this->is_deleted_changed = $this->is_deleted ?  1 : -1;
		$this->country_id_changed = $this->country_id ? 1 : -1;
		$this->timezone_changed = $this->timezone ? 1 : -1;
		$this->venue_id_changed = $this->venue_id ? 1 : -1;
		$this->url_changed = $this->url ? 1 : -1;
		$this->ticket_url_changed = $this->ticket_url ? 1 : -1;
		$this->is_virtual_changed = 1;
		$this->is_physical_changed = 1;
		$this->area_id_changed = $this->area_id ? 1 : -1;
		$this->is_new = 1;
	}
	
	public function setChangedFlagsFromLast(EventHistoryModel $last) {		
		$this->summary_changed  = ($this->summary != $last->summary  )? 1 : -1;
		$this->description_changed  = ($this->description  != $last->description  )? 1 : -1;
		$this->start_at_changed  = ($this->start_at  != $last->start_at  )? 1 : -1;
		$this->end_at_changed  = ($this->end_at  != $last->end_at  )? 1 : -1;
		$this->is_deleted_changed  = ($this->is_deleted != $last->is_deleted  )? 1 : -1;
		$this->country_id_changed  = ($this->country_id  != $last->country_id  )? 1 : -1;
		$this->timezone_changed  = ($this->timezone  != $last->timezone  )? 1 : -1;
		$this->venue_id_changed  = ($this->venue_id  != $last->venue_id  )? 1 : -1;
		$this->url_changed = ($this->url  != $last->url  )? 1 : -1;
		$this->ticket_url_changed = ($this->ticket_url  != $last->ticket_url  )? 1 : -1;
		$this->is_virtual_changed  = ($this->is_virtual  != $last->is_virtual  )? 1 : -1;
		$this->is_physical_changed  = ($this->is_physical  != $last->is_physical  )? 1 : -1;
		$this->area_id_changed  = ($this->area_id  != $last->area_id  )? 1 : -1;
		$this->is_new = 0;
	}
	
	
	public function getCreatedAt() {
		return $this->created_at;
	}


	public function getCreatedAtTimeStamp() {
		return $this->created_at->getTimestamp();
	}
	
	public function getEventSlug() {
		return $this->event_slug;
	}

	public function setEventSlug($event_slug) {
		$this->event_slug = $event_slug;
	}

	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getUserAccountUsername() {
		return $this->user_account_username;
	}

	public function setUserAccountUsername($user_account_username) {
		$this->user_account_username = $user_account_username;
	}

	
	public function getSummaryChanged() {
		return ($this->summary_changed != -1);
	}

	public function getDescriptionChanged() {
		return ($this->description_changed != -1);
	}

	public function getStartAtChanged() {
		return ($this->start_at_changed != -1);
	}

	public function getEndAtChanged() {
		return ($this->end_at_changed != -1);
	}

	public function getIsDeletedChanged() {
		return ($this->is_deleted_changed != -1);
	}

	public function getCountryIdChanged() {
		return ($this->country_id_changed != -1);
	}

	public function getTimezoneChanged() {
		return ($this->timezone_changed != -1);
	}

	public function getVenueIdChanged() {
		return ($this->venue_id_changed != -1);
	}

	public function getUrlChanged() {
		return ($this->url_changed != -1);
	}

	public function getTicketUrlChanged() {
		return ($this->ticket_url_changed != -1);
	}
		
	public function getIsVirtualChanged() {
		return ($this->is_virtual_changed != -1);
	}

	public function getIsPhysicalChanged() {
		return ($this->is_physical_changed != -1);
	}

	public function getAreaIdChanged() {
		return ($this->area_id_changed != -1);
	}


	public function getIsNew() {
		return ($this->is_new == 1);
	}


}

