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

	protected $reoccur;


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
		if (isset($data['reoccur']) && $data['reoccur']) {
			// we want to turn stdClass to arrays, all of it
			$this->reoccur = $this->get_object_vars_recursive(json_decode($data['reoccur']));
		}
	}

	protected function get_object_vars_recursive($in) {
		if (is_a($in, "stdClass")) {
			return $this->get_object_vars_recursive(get_object_vars($in));
		} else if (is_array($in)){
			foreach($in as $k=>$v) {
				$in[$k] = $this->get_object_vars_recursive($in[$k]);
			}
			return $in;
		}
		return $in;
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

	public function setReoccur($reoccur)
	{
		$this->reoccur = $reoccur;
	}

	/**
	 * @param mixed $ics_rrule_1
	 * @return boolean Was it different?
	 */
	public function setReoccurIfDifferent($reoccur)
	{
		if (is_null($this->reoccur)) {
			$this->reoccur = $reoccur;
			return true;
		}

		if (count(array_keys($reoccur)) != count(array_keys($this->reoccur))) {
			$this->reoccur = $reoccur;
			return true;
		}

		foreach($reoccur as $k=>$v) {
			if (!array_key_exists($k, $this->reoccur) || $this->isTwoJSONVariablesDifferent($this->reoccur[$k],$reoccur[$k])) {
				$this->reoccur = $reoccur;
				return true;
			}
		}

		return false;
	}

	protected function isTwoJSONVariablesDifferent($in1, $in2) {
		if (is_string($in1)) {
			return $in1 == $in2;
		} elseif (is_array($in1) && is_array($in2)) {
			if (count($in1) != count($in2)) {
				return true;
			}
			foreach($in1 as $k=>$v) {
				if (!array_key_exists($k, $in2) || $this->isTwoJSONVariablesDifferent($in1[$k], $in2[$k])) {
					return true;
				}
			}
			return false;
		}
		// Not sure what is happening here. Return true to be safe.
		return true;
	}

	/**
	 * @return mixed
	 */
	public function getReoccur()
	{
		return $this->reoccur;
	}


	public function hasReoccurence() {
		return $this->reoccur != null && is_array($this->reoccur)
		&& isset($this->reoccur['ical_rrule']) && is_array($this->reoccur['ical_rrule']);
	}





}

