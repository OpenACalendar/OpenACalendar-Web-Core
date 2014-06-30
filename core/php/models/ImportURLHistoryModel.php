<?php


namespace models;

use repositories\ImportURLRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLHistoryModel extends ImportURLModel {
	
	
	protected $import_url_slug;
	protected $user_account_id;
	protected $user_account_username;
	
	protected $title_changed = 0;
	protected $is_enabled_changed  = 0;
	protected $expired_at_changed  = 0;
	protected $country_id_changed  = 0;
	protected $area_id_changed  = 0;

	public function setFromDataBaseRow($data) {
		$this->id = $data['import_url_id'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);		
		
		$this->country_id = $data['country_id'];
		$this->area_id = $data['area_id'];
		$this->title = $data['title'];
		$this->is_enabled = $data['is_enabled'];
		$this->expired_at = $data['expired_at'] ? new \DateTime($data['expired_at'], $utc) : null;
		
		$this->title_changed  = isset($data['title_changed']) ? $data['title_changed'] : 0;
		$this->is_enabled_changed  = isset($data['is_enabled_changed']) ? $data['is_enabled_changed'] : 0;
		$this->expired_at_changed  = isset($data['expired_at_changed']) ? $data['expired_at_changed'] : 0;
		$this->country_id_changed  = isset($data['country_id_changed']) ? $data['country_id_changed'] : 0;
		$this->area_id_changed  = isset($data['area_id_changed']) ? $data['area_id_changed'] : 0;
		$this->is_new = isset($data['is_new']) ? $data['is_new'] : 0;	
		
		$this->user_account_id = $data['user_account_id'];
		$this->user_account_username = isset($data['user_account_username']) ? $data['user_account_username'] : null;
		
		$this->import_url_slug = isset($data['import_url_slug']) ? $data['import_url_slug'] : null;
		$this->slug = isset($data['import_url_slug']) ? $data['import_url_slug'] : null;
	}

	public function isAnyChangeFlagsUnknown() {
		return $this->title_changed == 0 ||
			$this->is_enabled_changed == 0 ||
			$this->expired_at_changed == 0 ||
			$this->country_id_changed == 0 ||
			$this->area_id_changed == 0;
	}
		
	public function setChangedFlagsFromNothing() {
		$this->title_changed = $this->title ? 1 : -1;
		$this->is_enabled_changed = $this->is_enabled ? 1 : -1;
		$this->expired_at_changed = $this->expired_at ?  1 : -1;
		$this->country_id_changed = $this->country_id ?  1 : -1;
		$this->area_id_changed = $this->area_id ?  1 : -1;
		$this->is_new = 1;
	}	
	
	public function setChangedFlagsFromLast(ImportURLModel $last) {		
		$this->title_changed  = ($this->title != $last->title  )? 1 : -1;
		$this->is_enabled_changed  = ($this->is_enabled  != $last->is_enabled  )? 1 : -1;
		$this->expired_at_changed  = ($this->expired_at  != $last->expired_at  )? 1 : -1;
		$this->country_id_changed  = ($this->country_id  != $last->country_id  )? 1 : -1;
		$this->area_id_changed  = ($this->area_id  != $last->area_id  )? 1 : -1;
		$this->is_new = 0;
	}
	
	
	public function getCreatedAt() {
		return $this->created_at;
	}


	public function getCreatedAtTimeStamp() {
		return $this->created_at->getTimestamp();
	}
	
	public function getTitleChanged() {
		return ($this->title_changed != -1);
	}

	public function getIsEnabledChanged() {
		return ($this->is_enabled_changed != -1);
	}

	public function getExpiredAtChanged() {
		return ($this->expired_at_changed != -1);
	}

	public function getCountryIdChanged() {
		return ($this->country_id_changed != -1);
	}
	
	public function getAreaIdChanged() {
		return ($this->area_id_changed != -1);
	}
	
	public function getIsNew() {
		return $this->is_new;
	}

	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function getUserAccountUsername() {
		return $this->user_account_username;
	}


	public function getImportURLSlug() {
		return $this->import_url_slug;
	}

}

