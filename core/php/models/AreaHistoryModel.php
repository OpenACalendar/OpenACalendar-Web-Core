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
class AreaHistoryModel extends AreaModel implements \InterfaceHistoryModel {
	
	protected $created_at; 
	protected $user_account_id;
	protected $user_account_username;
	
	protected $title_changed = 0;
	protected $description_changed = 0;
	protected $country_id_changed = 0;
	protected $parent_area_id_changed = 0;
	protected $is_deleted_changed = 0;
	protected $is_duplicate_of_id_changed = 0;

	protected $edit_comment;

	protected $is_new = 0;


	public function setFromDataBaseRow($data) {
		$this->id = $data['area_id'];
		$this->slug = isset($data['area_slug']) ? $data['area_slug'] : null;
		$this->title = $data['title'];
		$this->description = $data['description'];
		$this->country_id = $data['country_id'];
		$this->parent_area_id = $data['parent_area_id'];
		$this->is_deleted = $data['is_deleted'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->user_account_id = isset($data['user_account_id']) ? $data['user_account_id'] : null;
		$this->user_account_username = isset($data['user_account_username']) ? $data['user_account_username'] : null;
		$this->title_changed  = $data['title_changed'];
		$this->description_changed  = $data['description_changed'];
		$this->country_id_changed  = $data['country_id_changed'];
		$this->parent_area_id_changed  = $data['parent_area_id_changed'];
		$this->is_deleted_changed  = $data['is_deleted_changed'];
		$this->is_new = isset($data['is_new']) ? $data['is_new'] : 0;
		$this->is_duplicate_of_id_changed = isset($data['is_duplicate_of_id_changed']) ? $data['is_duplicate_of_id_changed'] : 0;
		$this->edit_comment = isset($data['edit_comment']) ? $data['edit_comment'] : null;
	}
	
		
	public function getCreatedAt() {
		return $this->created_at;
	}

	public function getCreatedAtTimeStamp() {
		return $this->created_at->getTimestamp();
	}

	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function getUserAccountUsername() {
		return $this->user_account_username;
	}
	
	public function isAnyChangeFlagsUnknown() {
		return $this->title_changed == 0 || $this->description_changed == 0 || $this->country_id_changed == 0 ||
				$this->parent_area_id_changed == 0 || $this->is_deleted_changed == 0 ||
		$this->is_duplicate_of_id_changed == 0;
	}
	
	public function setChangedFlagsFromNothing() {
		$this->title_changed = $this->title ? 1 : -1;
		$this->description_changed = $this->description ? 1 : -1;
		$this->country_id_changed = $this->country_id ? 1 : -1;
		$this->parent_area_id_changed = $this->parent_area_id ? 1 : -1;
		$this->is_deleted_changed = $this->is_deleted ? 1 : -1;
		$this->is_duplicate_of_id_changed = $this->is_duplicate_of_id ? 1 : -1;
		$this->is_new = 1;
	}

	public function setChangedFlagsFromLast(AreaHistoryModel $last) {
		if ($this->title_changed == 0 && $last->title_changed != -2) {
			$this->title_changed  = ($this->title  != $last->title  )? 1 : -1;
		}
		if ($this->description_changed == 0 && $last->description_changed != -2) {
			$this->description_changed  = ($this->description  != $last->description  )? 1 : -1;
		}
		if ($this->country_id_changed == 0 && $last->country_id_changed != -2) {
			$this->country_id_changed  = ($this->country_id  != $last->country_id  )? 1 : -1;
		}
		if ($this->parent_area_id_changed == 0 && $last->parent_area_id_changed != -2) {
			$this->parent_area_id_changed  = ($this->parent_area_id  != $last->parent_area_id  )? 1 : -1;
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

	public function getCountryIdChanged() {
		return ($this->country_id_changed > -1);
	}

	public function getCountryIdChangedKnown() {
		return ($this->country_id_changed > -2);
	}

	public function getParentAreaIdChanged() {
		return ($this->parent_area_id_changed > -1);
	}

	public function getParentAreaIdChangedKnown() {
		return ($this->parent_area_id_changed > -2);
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
		return '/email/common/areaHistoryItem.html.twig';
	}

	public function getSiteWebTemplate() {
		return '/site/common/areaHistoryItem.html.twig';
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

