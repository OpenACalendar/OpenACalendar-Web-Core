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
class ImportedEventModel {
	
	protected $id;
	protected $import_url_id;
	protected $import_id;
	protected $title;
	protected $description;
	/** @var \DateTime **/
	protected $start_at;
	/** @var \DateTime **/
	protected $end_at;
	protected $timezone;
	protected $is_deleted;
	protected $url;
	protected $ticket_url;

	protected $ics_rrule_1;


	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->import_url_id = $data['import_url_id'];
		$this->import_id = $data['import_id'];
		$this->title = $data['title'];
		$this->description = $data['description'];
		$utc = new \DateTimeZone("UTC");
		$this->start_at = new \DateTime($data['start_at'], $utc);
		$this->end_at = new \DateTime($data['end_at'], $utc);
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->is_deleted = $data['is_deleted'];
		$this->url = $data['url'];
		$this->ticket_url = $data['ticket_url'];
		$this->timezone = $data['timezone'];
		if ($data['ics_rrule_1']) {
			$this->ics_rrule_1 = get_object_vars(json_decode($data['ics_rrule_1']));
		}
	}
	

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getImportUrlId() {
		return $this->import_url_id;
	}

	public function setImportUrlId($import_url_id) {
		$this->import_url_id = $import_url_id;
	}

	public function getImportId() {
		return $this->import_id;
	}

	public function setImportId($import_id) {
		$this->import_id = $import_id;
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

	public function getStartAt() {
		return $this->start_at;
	}

	public function setStartAt($start_at) {
		$this->start_at = $start_at;
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
	
	public function setEndAt($end_at) {
		$this->end_at = $end_at;
	}

	public function getIsDeleted() {
		return $this->is_deleted;
	}

	public function setIsDeleted($is_deleted) {
		$this->is_deleted = $is_deleted;
	}

	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
	}
	
	public function getTimezone() {
		return $this->timezone;
	}

	public function setTimezone($timezone) {
		$this->timezone = $timezone;
	}

	public function getTicketUrl() {
		return $this->ticket_url;
	}

	public function setTicketUrl($ticket_url) {
		$this->ticket_url = $ticket_url;
	}

	/**
	 * @param mixed $ics_rrule_1
	 */
	public function setIcsRrule1($ics_rrule_1)
	{
		$this->ics_rrule_1 = $ics_rrule_1;
	}

	/**
	 * @param mixed $ics_rrule_1
	 */
	public function setIcsRrule1IfDifferent($ics_rrule_1)
	{
		if (is_null($this->ics_rrule_1)) {
			$this->ics_rrule_1 = $ics_rrule_1;
			return true;
		}

		if (count(array_keys($ics_rrule_1)) != count(array_keys($this->ics_rrule_1))) {
			$this->ics_rrule_1 = $ics_rrule_1;
			return true;
		}

		foreach($ics_rrule_1 as $k=>$v) {
			if (!array_key_exists($k, $this->ics_rrule_1) || $this->ics_rrule_1[$k] != $ics_rrule_1[$k]) {
				$this->ics_rrule_1 = $ics_rrule_1;
				return true;
			}
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	public function getIcsRrule1()
	{
		return $this->ics_rrule_1;
	}

	/**
	 * @return mixed
	 */
	public function getIcsRrule1AsString()
	{
		if ($this->ics_rrule_1) {
			$out = array();
			foreach($this->ics_rrule_1 as $k=>$v) {
				$out[] = $k."=".$v;
			}
			return implode(";", $out);
		} else {
			return "";
		}
	}

	public function hasReoccurence() {
		return (boolean)$this->ics_rrule_1;
	}





}

