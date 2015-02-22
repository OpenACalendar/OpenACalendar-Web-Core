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
class VenueHistoryModel extends VenueModel implements \InterfaceHistoryModel {

	protected $venue_slug;
	//protected $user_account_id;
	protected $created_at; 
	protected $user_account_id;
	protected $user_account_username;
	
	protected $title_changed = 0;
	protected $description_changed = 0;
	protected $lat_changed = 0;
	protected $lng_changed = 0;
	protected $country_id_changed = 0;
	protected $is_deleted_changed = 0;
	protected $area_id_changed = 0;
	protected $address_changed = 0;
	protected $address_code_changed = 0;
	protected $is_duplicate_of_id_changed = 0;

	protected $is_new = 0;



	public function setFromDataBaseRow($data) {
		$this->id = $data['venue_id'];
		$this->slug = isset($data['venue_slug']) ? $data['venue_slug'] : null;
		$this->title = $data['title'];
		$this->description = $data['description'];
		$this->address = $data['address'];
		$this->address_code = $data['address_code'];
		$this->lat = $data['lat'];
		$this->lng = $data['lng'];
		$this->country_id = $data['country_id'];
		$this->area_id = $data['area_id'];
		$this->is_deleted = $data['is_deleted'];
		$this->is_duplicate_of_id = $data['is_duplicate_of_id'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->user_account_id = isset($data['user_account_id']) ? $data['user_account_id'] : null;
		$this->user_account_username = isset($data['user_account_username']) ? $data['user_account_username'] : null;
		$this->title_changed = $data['title_changed'];
		$this->description_changed = $data['description_changed'];
		$this->lat_changed = $data['lat_changed'];
		$this->lng_changed = $data['lng_changed'];
		$this->country_id_changed = $data['country_id_changed'];
		$this->is_deleted_changed = $data['is_deleted_changed'];
		$this->area_id_changed = $data['area_id_changed'];
		$this->address_changed = $data['address_changed'];
		$this->address_code_changed = $data['address_code_changed'];
		$this->is_new = isset($data['is_new']) ? $data['is_new'] : 0;
		$this->is_duplicate_of_id_changed = isset($data['is_duplicate_of_id_changed']) ? $data['is_duplicate_of_id_changed'] : 0;
	}
	
	public function getCreatedAt() {
		return $this->created_at;
	}


	public function getCreatedAtTimeStamp() {
		return $this->created_at->getTimestamp();
	}
	
	/**
	 * @todo Is this used anywhere? Should we not be using getSlug instead?
	 */
	public function getVenueSlug() {
		return $this->slug;
	}

	/**
	 * @todo Is this used anywhere? Should we not be using setSlug instead? Why do we even set a slug here?
	 */
	public function setVenueSlug($group_slug) {
		$this->slug = $group_slug;
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
		return $this->title_changed == 0 || $this->description_changed == 0 || $this->lat_changed == 0 ||
				$this->lng_changed == 0 || $this->is_deleted_changed == 0 || $this->country_id_changed == 0 ||
				$this->area_id_changed == 0 || $this->address_changed == 0 || $this->address_code_changed == 0 ||
		$this->is_duplicate_of_id_changed == 0;
	}
	
	public function setChangedFlagsFromNothing() {
		$this->title_changed = $this->title ? 1 : -1;
		$this->description_changed = $this->description ? 1 : -1;
		$this->lat_changed = $this->lat ? 1 : -1;
		$this->lng_changed = $this->lng ? 1 : -1;
		$this->is_deleted_changed = $this->is_deleted ? 1 : -1;
		$this->country_id_changed = $this->country_id ? 1 : -1;
		$this->area_id_changed = $this->area_id ? 1 : -1;
		$this->address_changed = $this->address ? 1 : -1;
		$this->address_code_changed = $this->address_code ? 1 : -1;
		$this->is_duplicate_of_id_changed = $this->is_duplicate_of_id ? 1 : -1;
		$this->is_new = 1;	
	}
	
	public function setChangedFlagsFromLast(VenueHistoryModel $last) {
		if ($this->title_changed == 0 && $last->title_changed != -2) {
			$this->title_changed  = ($this->title  != $last->title  )? 1 : -1;
		}
		if ($this->description_changed == 0 && $last->title_changed != -2) {
			$this->description_changed  = ($this->description  != $last->description  )? 1 : -1;
		}
		if ($this->lat_changed == 0 && $last->lat_changed != -2) {
			$this->lat_changed  = ($this->lat  != $last->lat  )? 1 : -1;
		}
		if ($this->lng_changed == 0 && $last->lng_changed != -2) {
			$this->lng_changed  = ($this->lng  != $last->lng  )? 1 : -1;
		}
		if ($this->is_deleted_changed == 0 && $last->is_deleted_changed != -2) {
			$this->is_deleted_changed  = ($this->is_deleted  != $last->is_deleted  )? 1 : -1;
		}
		if ($this->country_id_changed == 0 && $last->country_id_changed != -2) {
			$this->country_id_changed  = ($this->country_id  != $last->country_id  )? 1 : -1;
		}
		if ($this->area_id_changed == 0 && $last->area_id_changed != -2) {
			$this->area_id_changed  = ($this->area_id  != $last->area_id  )? 1 : -1;
		}
		if ($this->address_changed == 0 && $last->address_changed != -2) {
			$this->address_changed  = ($this->address  != $last->address  )? 1 : -1;
		}
		if ($this->address_code_changed == 0 && $last->address_code_changed != -2) {
			$this->address_code_changed  = ($this->address_code  != $last->address_code  )? 1 : -1;
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

	public function getLatChanged() {
		return ($this->lat_changed > -1);
	}

	public function getLatChangedKnown() {
		return ($this->lat_changed > -2);
	}

	public function getLngChanged() {
		return ($this->lng_changed > -1);
	}

	public function getLngChangedKnown() {
		return ($this->lng_changed > -2);
	}

	public function getCountryIdChanged() {
		return ($this->country_id_changed > -1);
	}

	public function getCountryIdChangedKnown() {
		return ($this->country_id_changed > -2);
	}

	public function getIsDeletedChanged() {
		return ($this->is_deleted_changed > -1);
	}

	public function getIsDeletedChangedKnown() {
		return ($this->is_deleted_changed > -2);
	}

	public function getAreaIdChanged() {
		return ($this->area_id_changed > -1);
	}

	public function getAreaIdChangedKnown() {
		return ($this->area_id_changed > -2);
	}

	public function getAddressChanged() {
		return ($this->address_changed > -1);
	}

	public function getAddressChangedKnown() {
		return ($this->address_changed > -2);
	}

	public function getAddressCodeChanged() {
		return ($this->address_code_changed > -1);
	}

	public function getAddressCodeChangedKnown() {
		return ($this->address_code_changed > -2);
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
		return '/email/common/venueHistoryItem.html.twig';
	}

	public function getSiteWebTemplate() {
		return '/site/common/venueHistoryItem.html.twig';
	}


}

