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
class GroupHistoryModel extends GroupModel implements \InterfaceHistoryModel {

	protected $group_slug;
	//protected $user_account_id;
	protected $created_at; 
	protected $user_account_id;
	protected $user_account_username;
	
	protected $title_changed = 0;
	protected $description_changed = 0;
	protected $url_changed = 0;
	protected $twitter_username_changed = 0;
	protected $is_deleted_changed = 0;
	protected $is_duplicate_of_id_changed = 0;

	protected $is_new = 0;


	protected $edit_comment;

	public function setFromDataBaseRow($data) {
		$this->id = $data['group_id'];
		$this->group_slug = isset($data['group_slug']) ? $data['group_slug'] : null;
		$this->title = $data['title'];
		$this->url = $data['url'];
		$this->description = $data['description'];
		$this->twitter_username = $data['twitter_username'];
		$this->is_deleted = $data['is_deleted'];
		$this->edit_comment = isset($data['edit_comment']) ? $data['edit_comment'] : null;
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->user_account_id = $data['user_account_id'];
		$this->user_account_username = isset($data['user_account_username']) ? $data['user_account_username'] : null;
		$this->title_changed = $data['title_changed'];
		$this->description_changed = $data['description_changed'];
		$this->url_changed = $data['url_changed'];
		$this->twitter_username_changed = $data['twitter_username_changed'];
		$this->is_deleted_changed = $data['is_deleted_changed'];
		$this->is_new = isset($data['is_new']) ? $data['is_new'] : 0;
		$this->is_duplicate_of_id_changed = isset($data['is_duplicate_of_id_changed']) ? $data['is_duplicate_of_id_changed'] : 0;
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

	public function isAnyChangeFlagsUnknown() {
		return $this->title_changed == 0 || $this->description_changed == 0 || $this->url_changed == 0 ||
				$this->twitter_username_changed == 0 || $this->is_deleted_changed == 0 ||
		$this->is_duplicate_of_id_changed == 0;
	}
	
	public function setChangedFlagsFromNothing() {
		$this->title_changed = $this->title ? 1 : -1;
		$this->description_changed = $this->description ? 1 : -1;
		$this->url_changed = $this->url ? 1 : -1;
		$this->twitter_username_changed = $this->twitter_username ? 1 : -1;
		$this->is_deleted_changed = $this->is_deleted ? 1 : -1;
		$this->is_duplicate_of_id_changed = $this->is_duplicate_of_id ? 1 : -1;
		$this->is_new = 1;
	}
	
	public function setChangedFlagsFromLast(GroupHistoryModel $last) {
		if ($this->title_changed == 0 && $last->title_changed != -2) {
			$this->title_changed  = ($this->title  != $last->title  )? 1 : -1;
		}
		if ($this->description_changed == 0 && $last->description_changed != -2) {
			$this->description_changed  = ($this->description  != $last->description  )? 1 : -1;
		}
		if ($this->url_changed == 0 && $last->url_changed != -2) {
			$this->url_changed  = ($this->url  != $last->url  )? 1 : -1;
		}
		if ($this->twitter_username_changed == 0 && $last->twitter_username_changed != -2) {
			$this->twitter_username_changed  = ($this->twitter_username  != $last->twitter_username  )? 1 : -1;
		}
		if ($this->is_deleted_changed == 0 && $last->is_deleted_changed != -2) {
			$this->is_deleted_changed  = ($this->is_deleted  != $last->is_deleted  )? 1 : -1;
		}
		if ($this->is_duplicate_of_id_changed == 0 && $last->is_duplicate_of_id_changed != -2) {
			$this->is_duplicate_of_id_changed = ($this->is_duplicate_of_id != $last->is_duplicate_of_id) ? 1 : -1;
		}
		$this->is_new = 0;
	}
	
	public function getTitleChanged() {
		return ($this->title_changed > -1);
	}

	public function getTitleChangedKnown() {
		return ($this->title_changed > -2);
	}

	public function getDescriptionChanged() {
		return ($this->description_changed > -1);
	}

	public function getDescriptionChangedKnown() {
		return ($this->description_changed > -2);
	}

	public function getUrlChanged() {
		return ($this->url_changed > -1);
	}

	public function getUrlChangedKnown() {
		return ($this->url_changed > -2);
	}

	public function getTwitterUsernameChanged() {
		return ($this->twitter_username_changed > -1);
	}

	public function getTwitterUsernameChangedKnown() {
		return ($this->twitter_username_changed > -2);
	}

	public function getIsDeletedChanged() {
		return ($this->is_deleted_changed > -1);
	}

	public function getIsDeletedChangedKnown() {
		return ($this->is_deleted_changed > -2);
	}

	public function getIsDuplicateOfIdChanged() {
		return ($this->is_duplicate_of_id_changed > -1);
	}

	public function getIsDuplicateOfIdChangedKnown() {
		return ($this->is_duplicate_of_id_changed > -2);
	}

	public function getIsNew() {
		return ($this->is_new == 1);
	}

	public function getSiteEmailTemplate() {
		return '/email/common/groupHistoryItem.html.twig';
	}

	public function getSiteWebTemplate() {
		return '/site/common/groupHistoryItem.html.twig';
	}


	/** @return boolean */
	public function isEqualTo(\InterfaceHistoryModel $otherHistoryModel) {
		return $otherHistoryModel instanceof $this &&
		$otherHistoryModel->getCreatedAtTimeStamp() == $this->getCreatedAtTimeStamp() &&
		$otherHistoryModel->getId() == $this->getId();
	}

	public function getEditComment()
	{
		return $this->edit_comment;
	}


}

