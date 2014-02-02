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
class VenueHistoryModel extends VenueModel {

	protected $venue_id;
	protected $venue_slug;
	//protected $user_account_id;
	protected $created_at; 
	protected $user_account_id;
	protected $user_account_username;
	
	
	public function setFromDataBaseRow($data) {
		$this->venue_id = $data['venue_id'];
		$this->venue_slug = $data['venue_slug'];
		$this->title = $data['title'];
		$this->description = $data['description'];
		$this->lat = $data['lat'];
		$this->lng = $data['lng'];
		$this->country_id = $data['country_id'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->user_account_id = $data['user_account_id'];
		$this->user_account_username = $data['user_account_username'];
	}
	
	public function getCreatedAt() {
		return $this->created_at;
	}


	public function getCreatedAtTimeStamp() {
		return $this->created_at->getTimestamp();
	}
	
	public function getVenueSlug() {
		return $this->venue_slug;
	}

	public function setVenueSlug($group_slug) {
		$this->venue_slug = $group_slug;
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

