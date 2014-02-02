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
class SiteAccessRequestModel {
	
	
	protected $id;
	protected $site_id;
	protected $user_account_id;
	protected $answer;
	protected $created_at;
	protected $created_by;
	protected $granted_at;
	protected $granted_by;
	protected $rejected_at;
	protected $rejected_by;

	public function setFromDataBaseRow($data) {
		$this->user_account_id = $data['user_account_id'];
		$this->site_id = $data['site_id'];
		$this->answer = $data['answer'];
		$this->created_by = $data['created_by'];
		$this->granted_by = $data['granted_by'];
		$this->rejected_by = $data['rejected_by'];
		$utc = new \DateTimeZone("UTC");
		$this->granted_at = $data['granted_at'] ? new \DateTime($data['granted_at'], $utc) : null;
		$this->rejected_at = $data['rejected_at'] ? new \DateTime($data['rejected_at'], $utc) : null;
		$this->created_at = new \DateTime($data['created_at'], $utc);
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
		return $this;
	}

	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
		return $this;
	}

	public function getAnswer() {
		return $this->answer;
	}

	public function setAnswer($answer) {
		$this->answer = $answer;
		return $this;
	}

	public function getCreatedat() {
		return $this->created_at;
	}

	public function setCreatedAt($created_at) {
		$this->created_at = $created_at;
		return $this;
	}

	public function getCreatedBy() {
		return $this->created_by;
	}

	public function setCreatedBy($created_by) {
		$this->created_by = $created_by;
		return $this;
	}

	public function getGrantedAt() {
		return $this->granted_at;
	}

	public function setGrantedAt($granted_at) {
		$this->granted_at = $granted_at;
		return $this;
	}

	public function getGrantedBy() {
		return $this->granted_by;
	}

	public function setGrantedBy($granted_by) {
		$this->granted_by = $granted_by;
		return $this;
	}

	public function getRejectedAt() {
		return $this->rejected_at;
	}

	public function setRejectedAt($rejected_at) {
		$this->rejected_at = $rejected_at;
		return $this;
	}

	public function getRejectedBy() {
		return $this->rejected_by;
	}

	public function setRejectedBy($rejected_by) {
		$this->rejected_by = $rejected_by;
		return $this;
	}


	
}

