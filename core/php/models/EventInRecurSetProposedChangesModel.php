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
class EventInRecurSetProposedChangesModel {

	protected $summary_change_possible = false;
	protected $summary_change_selected = false;
	protected $description_change_possible = false;
	protected $description_change_selected = false;
	protected $timezone_change_possible = false;
	protected $timezone_change_selected = false;
	protected $country_area_venue_id_change_possible = false;
	protected $country_area_venue_id_change_selected = false;
	protected $url_change_possible = false;
	protected $url_change_selected = false;
	protected $is_virtual_change_possible = false;
	protected $is_virtual_change_selected = false;
	protected $is_physical_change_possible = false;
	protected $is_physical_change_selected = false;
	protected $start_end_at_change_possible = false;
	protected $start_end_at_change_selected = false;
	
	protected $summary;
	
	/** @var \DateTime **/
	protected $start_at;
	/** @var \DateTime **/
	protected $end_at;

	public function getSummaryChangePossible() {
		return $this->summary_change_possible;
	}

	public function setSummaryChangePossible($summary_change_possible) {
		$this->summary_change_possible = $summary_change_possible;
	}

	public function getSummaryChangeSelected() {
		return $this->summary_change_selected;
	}

	public function setSummaryChangeSelected($summary_change_selected) {
		$this->summary_change_selected = $summary_change_selected;
	}

	public function getDescriptionChangePossible() {
		return $this->description_change_possible;
	}

	public function setDescriptionChangePossible($description_change_possible) {
		$this->description_change_possible = $description_change_possible;
	}

	public function getDescriptionChangeSelected() {
		return $this->description_change_selected;
	}

	public function setDescriptionChangeSelected($description_change_selected) {
		$this->description_change_selected = $description_change_selected;
	}

	public function getTimezoneChangePossible() {
		return $this->timezone_change_possible;
	}

	public function setTimezoneChangePossible($timezone_change_possible) {
		$this->timezone_change_possible = $timezone_change_possible;
	}

	public function getTimezoneChangeSelected() {
		return $this->timezone_change_selected;
	}

	public function setTimezoneChangeSelected($timezone_change_selected) {
		$this->timezone_change_selected = $timezone_change_selected;
	}

	public function getCountryAreaVenueIdChangePossible() {
		return $this->country_area_venue_id_change_possible;
	}

	public function setCountryAreaVenueIdChangePossible($country_area_venue_id_change_possible) {
		$this->country_area_venue_id_change_possible = $country_area_venue_id_change_possible;
	}

	public function getCountryAreaVenueIdChangeSelected() {
		return $this->country_area_venue_id_change_selected;
	}

	public function setCountryAreaVenueIdChangeSelected($country_area_venue_id_change_selected) {
		$this->country_area_venue_id_change_selected = $country_area_venue_id_change_selected;
	}

	public function getUrlChangePossible() {
		return $this->url_change_possible;
	}

	public function setUrlChangePossible($url_change_possible) {
		$this->url_change_possible = $url_change_possible;
	}

	public function getUrlChangeSelected() {
		return $this->url_change_selected;
	}

	public function setUrlChangeSelected($url_change_selected) {
		$this->url_change_selected = $url_change_selected;
	}

	public function getIsVirtualChangePossible() {
		return $this->is_virtual_change_possible;
	}

	public function setIsVirtualChangePossible($is_virtual_change_possible) {
		$this->is_virtual_change_possible = $is_virtual_change_possible;
	}

	public function getIsVirtualChangeSelected() {
		return $this->is_virtual_change_selected;
	}

	public function setIsVirtualChangeSelected($is_virtual_change_selected) {
		$this->is_virtual_change_selected = $is_virtual_change_selected;
	}

	public function getIsPhysicalChangePossible() {
		return $this->is_physical_change_possible;
	}

	public function setIsPhysicalChangePossible($is_physical_change_possible) {
		$this->is_physical_change_possible = $is_physical_change_possible;
	}

	public function getIsPhysicalChangeSelected() {
		return $this->is_physical_change_selected;
	}

	public function setIsPhysicalChangeSelected($is_physical_change_selected) {
		$this->is_physical_change_selected = $is_physical_change_selected;
	}

	public function getStartEndAtChangePossible() {
		return $this->start_end_at_change_possible;
	}

	public function setStartEndAtChangePossible($start_end_at_change_possible) {
		$this->start_end_at_change_possible = $start_end_at_change_possible;
	}

	public function getStartEndAtChangeSelected() {
		return $this->start_end_at_change_selected;
	}

	public function setStartEndAtChangeSelected($start_end_at_change_selected) {
		$this->start_end_at_change_selected = $start_end_at_change_selected;
	}

	public function isAnyChangesPossible() {
		return $this->summary_change_possible ||
				$this->description_change_possible ||
				$this->timezone_change_possible ||
				$this->country_area_venue_id_change_possible ||
				$this->url_change_possible ||
				$this->is_virtual_change_possible ||
				$this->is_physical_change_possible ||
				$this->start_end_at_change_possible;
	}
			
	public function getSummary() {
		return $this->summary;
	}

	public function setSummary($summary) {
		$this->summary = $summary;
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

	public function applyToEvent(EventModel $event, EventModel $originalEvent) {
		$changes = false;
		
		if ($this->summary_change_possible && $this->summary_change_selected) {
			$event->setSummary($this->summary);
			$changes = true;
		}
		if ($this->description_change_possible && $this->description_change_selected) {
			$event->setDescription($originalEvent->getDescription());
			$changes = true;
		}
		if ($this->timezone_change_possible && $this->timezone_change_selected) {
			$event->setTimezone($originalEvent->getTimezone());
			$changes = true;
		}
		if ($this->country_area_venue_id_change_possible && $this->country_area_venue_id_change_selected) {
			$event->setCountryId($originalEvent->getCountryId());
			$event->setAreaId($originalEvent->getAreaId());
			$event->setVenueId($originalEvent->getVenueId());
			$changes = true;
		}
		if ($this->url_change_possible && $this->url_change_selected) {
			$event->setUrl($originalEvent->getUrl());
			$changes = true;
		}
		if ($this->is_virtual_change_possible && $this->is_virtual_change_selected) {
			$event->setIsVirtual($originalEvent->getIsVirtual());
			$changes = true;
		}
		if ($this->is_physical_change_possible && $this->is_physical_change_selected) {
			$event->setIsPhysical($originalEvent->getIsPhysical());
			$changes = true;
		}
		if ($this->start_end_at_change_possible && $this->start_end_at_change_selected) {
			$event->setStartAt($this->start_at);
			$event->setEndAt($this->end_at);
			$changes = true;
		}
		
		return $changes;
	}
	
	
}


