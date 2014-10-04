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
class CuratedListHistoryModel extends CuratedListModel {
	

	protected $title_changed   = 0;
	protected $description_changed   = 0;
	protected $is_deleted_changed   = 0;

	protected $is_new = 0;

	public function setFromDataBaseRow($data) {
		$this->id = $data['curated_list_id'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);		
		
		$this->title = $data['title'];
		$this->description = $data['description'];
		$this->is_deleted = $data['is_deleted'];

		$this->title_changed  = isset($data['title_changed']) ? $data['title_changed'] : 0;
		$this->description_changed  = isset($data['description_changed']) ? $data['description_changed'] : 0;
		$this->is_deleted_changed  = isset($data['is_deleted_changed']) ? $data['is_deleted_changed'] : 0;
		$this->is_new = isset($data['is_new']) ? $data['is_new'] : 0;		
	}
	
	public function isAnyChangeFlagsUnknown() {
		return $this->title_changed == 0 ||
			$this->description_changed == 0 ||
			$this->is_deleted_changed == 0;
	}
	
	public function setChangedFlagsFromNothing() {
		$this->title_changed = $this->title ? 1 : -1;
		$this->description_changed = $this->description ? 1 : -1;
		$this->is_deleted_changed = $this->is_deleted ?  1 : -1;
		$this->is_new = 1;
	}	
	
	public function setChangedFlagsFromLast(CuratedListModel $last) {
		if ($this->title_changed == 0 && $last->title_changed != -2) {
			$this->title_changed  = ($this->title != $last->title  )? 1 : -1;
		}
		if ($this->description_changed == 0 && $last->description_changed != -2) {
			$this->description_changed  = ($this->description  != $last->description  )? 1 : -1;
		}
		if ($this->is_deleted_changed == 0 && $last->is_deleted_changed != -2) {
			$this->is_deleted_changed  = ($this->is_deleted != $last->is_deleted  )? 1 : -1;
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

	public function getIsDeletedChanged() {
		return ($this->is_deleted_changed > -1);
	}

	public function getIsDeletedChangedknown() {
		return ($this->is_deleted_changed > -2);
	}

	public function getIsNew() {
		return ($this->is_new == 1);
	}

}

