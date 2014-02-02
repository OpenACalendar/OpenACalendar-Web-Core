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
class GroupHistoryModel extends GroupModel {

	protected $group_id;
	protected $group_slug;
	//protected $user_account_id;
	protected $created_at; 
	protected $user_account_id;
	protected $user_account_username;
	
	
	public function setFromDataBaseRow($data) {
		$this->group_id = $data['group_id'];
		$this->group_slug = $data['group_slug'];
		$this->title = $data['title'];
		$this->url = $data['url'];
		$this->description = $data['description'];
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
	
	public function getGroupSlug() {
		return $this->group_slug;
	}

	public function setGroupSlug($group_slug) {
		$this->group_slug = $group_slug;
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

