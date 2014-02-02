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

	protected $event_id;
	protected $event_slug;
	//protected $user_account_id;
	protected $created_at; 
	protected $reverted_from_created_at;
	protected $user_account_id;
	protected $user_account_username;
	




	public function setFromDataBaseRow($data) {
		$this->event_id = $data['event_id'];
		$this->event_slug = isset($data['event_slug']) ? $data['event_slug'] : null;
		$this->summary = $data['summary'];
		$this->group_title = isset($data['group_title']) ? $data['group_title'] : null;
		$this->description = $data['description'];
		$utc = new \DateTimeZone("UTC");
		$this->start_at = new \DateTime($data['start_at'], $utc);
		$this->end_at = new \DateTime($data['end_at'], $utc);
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->group_id = $data['group_id'];
		$this->is_deleted = $data['is_deleted'];
		$this->user_account_id = $data['user_account_id'];
		$this->user_account_username = isset($data['user_account_username']) ? $data['user_account_username'] : null;
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


}

