<?php


namespace models;

use repositories\ImportURLRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLHistoryModel extends ImportURLModel implements \InterfaceHistoryModel {
	
	
	protected $import_url_slug;
	protected $user_account_id;
	protected $user_account_username;
	
	protected $title_changed = 0;
	protected $is_enabled_changed  = 0;
	protected $expired_at_changed  = 0;
	protected $country_id_changed  = 0;
	protected $area_id_changed  = 0;
	protected $group_id_changed  = 0;
	protected $is_manual_events_creation_changed  = 0;

	public function setFromDataBaseRow($data) {
		$this->id = $data['import_url_id'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);		
		
		$this->country_id = $data['country_id'];
		$this->area_id = $data['area_id'];
		$this->group_id = $data['group_id'];
		$this->title = $data['title'];
		$this->is_enabled = $data['is_enabled'];
		$this->is_manual_events_creation = $data['is_manual_events_creation'];
		$this->expired_at = $data['expired_at'] ? new \DateTime($data['expired_at'], $utc) : null;
		
		$this->title_changed  = isset($data['title_changed']) ? $data['title_changed'] : 0;
		$this->is_enabled_changed  = isset($data['is_enabled_changed']) ? $data['is_enabled_changed'] : 0;
		$this->expired_at_changed  = isset($data['expired_at_changed']) ? $data['expired_at_changed'] : 0;
		$this->country_id_changed  = isset($data['country_id_changed']) ? $data['country_id_changed'] : 0;
		$this->area_id_changed  = isset($data['area_id_changed']) ? $data['area_id_changed'] : 0;
		$this->group_id_changed  = isset($data['group_id_changed']) ? $data['group_id_changed'] : 0;
		$this->is_manual_events_creation_changed = isset($data['is_manual_events_creation_changed']) ? $data['is_manual_events_creation_changed'] : 0;

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
			$this->group_id_changed == 0 ||
			$this->area_id_changed == 0 ||
			$this->is_manual_events_creation_changed == 0;
	}
		
	public function setChangedFlagsFromNothing() {
		$this->title_changed = $this->title ? 1 : -1;
		$this->is_enabled_changed = $this->is_enabled ? 1 : -1;
		$this->is_manual_events_creation_changed = $this->is_manual_events_creation ? 1 : -1;
		$this->expired_at_changed = $this->expired_at ?  1 : -1;
		$this->country_id_changed = $this->country_id ?  1 : -1;
		$this->area_id_changed = $this->area_id ?  1 : -1;
		$this->group_id_changed = $this->group_id ?  1 : -1;
		$this->is_new = 1;
	}	
	
	public function setChangedFlagsFromLast(ImportURLModel $last) {
		if ($this->title_changed == 0 && $last->title_changed != -2) {
			$this->title_changed  = ($this->title != $last->title  )? 1 : -1;
		}
		if ($this->is_enabled_changed == 0 && $last->is_enabled_changed != -2) {
			$this->is_enabled_changed  = ($this->is_enabled  != $last->is_enabled  )? 1 : -1;
		}
		if ($this->is_manual_events_creation_changed == 0 && $last->is_manual_events_creation_changed != -2) {
			$this->is_manual_events_creation_changed  = ($this->is_manual_events_creation  != $last->is_manual_events_creation  )? 1 : -1;
		}
		if ($this->expired_at_changed == 0 && $last->expired_at_changed != -2) {
			$this->expired_at_changed  = ($this->expired_at  != $last->expired_at  )? 1 : -1;
		}
		if ($this->country_id_changed == 0 && $last->country_id_changed != -2) {
			$this->country_id_changed  = ($this->country_id  != $last->country_id  )? 1 : -1;
		}
		if ($this->area_id_changed == 0 && $last->area_id_changed != -2) {
			$this->area_id_changed  = ($this->area_id  != $last->area_id  )? 1 : -1;
		}
		if ($this->group_id_changed == 0 && $last->group_id_changed != -2) {
			$this->group_id_changed  = ($this->group_id  != $last->group_id  )? 1 : -1;
		}
		$this->is_new = 0;
	}
	
	
	public function getCreatedAt() {
		return $this->created_at;
	}


	public function getCreatedAtTimeStamp() {
		return $this->created_at->getTimestamp();
	}
	
	public function getTitleChanged() {
		return ($this->title_changed > -1);
	}

	public function getTitleChangedKnown() {
		return ($this->title_changed > -2);
	}

	public function getIsEnabledChanged() {
		return ($this->is_enabled_changed > -1);
	}

	public function getIsEnabledChangedKnown() {
		return ($this->is_enabled_changed > -2);
	}

	public function getIsManualEventsCreationChanged() {
		return ($this->is_manual_events_creation_changed > -1);
	}

	public function getIsManualEventsCreationChangedKnown() {
		return ($this->is_manual_events_creation_changed > -2);
	}

	public function getExpiredAtChanged() {
		return ($this->expired_at_changed > -1);
	}

	public function getExpiredAtChangedKnown() {
		return ($this->expired_at_changed > -2);
	}

	public function getCountryIdChanged() {
		return ($this->country_id_changed > -1);
	}
	
	public function getCountryIdChangedKnown() {
		return ($this->country_id_changed > -2);
	}

	public function getAreaIdChanged() {
		return ($this->area_id_changed > -1);
	}
	
	public function getAreaIdChangedKnown() {
		return ($this->area_id_changed > -2);
	}

	public function getGroupIdChanged() {
		return ($this->group_id_changed > -1);
	}

	public function getGroupIdChangedKnown() {
		return ($this->group_id_changed > -2);
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

	public function getSiteEmailTemplate() {
		return '/email/common/importURLHistoryItem.html.twig';
	}

	public function getSiteWebTemplate() {
		return '/site/common/importURLHistoryItem.html.twig';
	}



}

