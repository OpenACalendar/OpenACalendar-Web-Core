<?php


namespace models;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportResultModel {
	
	protected $import_id;
	protected $new_count = 0;
	protected $existing_count = 0;
	protected $saved_count = 0;
	protected $in_past_count = 0;
	protected $to_far_in_future_count = 0;
	protected $not_valid_count = 0;
	protected $created_at;
	protected $is_success = false;
	protected $message;


	public function setFromDataBaseRow($data) {
		$this->import_id = $data['import_url_id'];
		$this->new_count = $data['new_count'];
		$this->existing_count = $data['existing_count'];
		$this->saved_count = $data['saved_count'];
		$this->in_past_count = $data['in_past_count'];
		$this->to_far_in_future_count = $data['to_far_in_future_count'];
		$this->not_valid_count = $data['not_valid_count'];
		$this->created_at = $data['created_at'];
		$this->is_success = $data['is_success'];
		$this->message = $data['message'];
	}
	
	
	public function getImportId() {
		return $this->import_id;
	}

	public function setImportId($import_id) {
		$this->import_id = $import_id;
		return $this;
	}

	public function getNewCount() {
		return $this->new_count;
	}

	public function setNewCount($new_count) {
		$this->new_count = $new_count;
		return $this;
	}

	public function getExistingCount() {
		return $this->existing_count;
	}

	public function setExistingCount($existing_count) {
		$this->existing_count = $existing_count;
		return $this;
	}

	public function getSavedCount() {
		return $this->saved_count;
	}

	public function setSavedCount($saved_count) {
		$this->saved_count = $saved_count;
		return $this;
	}

	public function getInPastCount() {
		return $this->in_past_count;
	}

	public function setInPastCount($in_past_count) {
		$this->in_past_count = $in_past_count;
		return $this;
	}

	public function getToFarInFutureCount() {
		return $this->to_far_in_future_count;
	}

	public function setToFarInFutureCount($to_far_in_future_count) {
		$this->to_far_in_future_count = $to_far_in_future_count;
		return $this;
	}

	public function getNotValidCount() {
		return $this->not_valid_count;
	}

	public function setNotValidCount($not_valid_count) {
		$this->not_valid_count = $not_valid_count;
		return $this;
	}

	public function getCreatedAt() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
		return $this;
	}

	public function getIsSuccess() {
		return $this->is_success;
	}

	public function setIsSuccess($is_success) {
		$this->is_success = $is_success;
		return $this;
	}

	public function getMessage() {
		return $this->message;
	}

	public function setMessage($message) {
		$this->message = $message;
		return $this;
	}


}

