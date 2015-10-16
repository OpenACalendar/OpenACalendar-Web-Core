<?php

namespace repositories\builders;

use models\SiteModel;
use models\UserAccountModel;
use models\VenueModel;
use models\CountryModel;
use models\AreaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}

	/** @var CountryModel **/
	protected $country;
	
	public function setCountry(CountryModel $country) {
		$this->country = $country;
	}

	/** @var AreaModel **/
	protected $area;
	
	public function setArea(AreaModel $area) {
		$this->area = $area;
	}

	
	protected $freeTextSearch;

	public function setFreeTextsearch($freeTextSearch) {
		$this->freeTextSearch = $freeTextSearch;
	}

	protected $freeTextSearchTitle;
	protected $freeTextSearchAddress;
	protected $freeTextSearchAddressCode;
	protected $freeTextSearchAddressCodeRemoveSpaces = true;

	/**
	 * @param mixed $freeTextSearchAddress
	 */
	public function setFreeTextSearchAddress($freeTextSearchAddress)
	{
		$this->freeTextSearchAddress = $freeTextSearchAddress;
	}

	/**
	 * @param mixed $freeTextSearchAddressCode
	 */
	public function setFreeTextSearchAddressCode($freeTextSearchAddressCode, $freeTextSearchAddressCodeRemoveSpaces = true)
	{
		$this->freeTextSearchAddressCode = $freeTextSearchAddressCode;
		$this->freeTextSearchAddressCodeRemoveSpaces = $freeTextSearchAddressCodeRemoveSpaces;
	}

	/**
	 * @param mixed $freeTextSearchTitle
	 */
	public function setFreeTextSearchTitle($freeTextSearchTitle)
	{
		$this->freeTextSearchTitle = $freeTextSearchTitle;
	}



	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}

	protected $includeMediasSlugs = false;

	/**
	 * @param boolean $includeMediasSlugs
	 */
	public function setIncludeMediasSlugs($includeMediasSlugs)
	{
		$this->includeMediasSlugs = $includeMediasSlugs;
	}




	protected $must_have_lat_lng = false;


	public function setMustHaveLatLng($must_have_lat_lng) {
		$this->must_have_lat_lng = $must_have_lat_lng;
	}

	/** @var UserAccountModel  */
	protected $editedByUser = null;

	/**
	 * @param UserAccountModel $editedByUser
	 */
	public function setEditedByUser(UserAccountModel $editedByUser)
	{
		$this->editedByUser = $editedByUser;
	}


	protected function build() {
		global $DB;

		$this->select[] = "  venue_information.* ";

		if ($this->site) {
			$this->where[] =  " venue_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->country) {
			$this->where[] =  " venue_information.country_id = :country_id ";
			$this->params['country_id'] = $this->country->getId();
		}
		
		if ($this->area) {
			
			$areaids = array( $this->area->getId() );
			
			$this->statAreas = $DB->prepare("SELECT area_id FROM cached_area_has_parent WHERE has_parent_area_id=:id");
			$this->statAreas->execute(array('id'=>$this->area->getId()));
			while($d = $this->statAreas->fetch()) {
				$areaids[] = $d['area_id'];
			}
			
			$this->where[] =  " venue_information.area_id IN (".  implode(",", $areaids).")";
			
		}

		if ($this->freeTextSearch) {
			$this->where[] =  '(CASE WHEN venue_information.title IS NULL THEN \'\' ELSE venue_information.title END )  || \' \' || '.
					'(CASE WHEN venue_information.description IS NULL THEN \'\' ELSE venue_information.description END ) || \' \' || '.
					'(CASE WHEN venue_information.address IS NULL THEN \'\' ELSE venue_information.address END ) || \' \' || '.
					'(CASE WHEN venue_information.address_code IS NULL THEN \'\' ELSE venue_information.address_code END ) '.
					' ILIKE :free_text_search ';
			$this->params['free_text_search'] = "%".strtolower($this->freeTextSearch)."%";
		}

		if ($this->freeTextSearchAddressCode) {
			if ($this->freeTextSearchAddressCodeRemoveSpaces) {
				$this->where[] = 'replace(venue_information.address_code, \' \',\'\') ILIKE :free_text_search_address_code ';
				$this->params['free_text_search_address_code'] = "%" . strtolower(str_replace($this->freeTextSearchAddressCode, " ","")) . "%";
			} else {
				$this->where[] = 'venue_information.address_code ILIKE :free_text_search_address_code ';
				$this->params['free_text_search_address_code'] = "%" . strtolower($this->freeTextSearchAddressCode) . "%";
			}
		}

		if ($this->freeTextSearchAddress) {
			$this->where[] =  'venue_information.address ILIKE :free_text_search_address ';
			$this->params['free_text_search_address'] = "%".strtolower($this->freeTextSearchAddress)."%";
		}

		if ($this->freeTextSearchTitle) {
			$this->where[] =  'venue_information.title ILIKE :free_text_search_title ';
			$this->params['free_text_search_title'] = "%".strtolower($this->freeTextSearchTitle)."%";
		}

		if (!$this->include_deleted) {
			$this->where[] = " venue_information.is_deleted = '0' ";
		}

		if ($this->includeMediasSlugs) {
			$this->select[] = "  (SELECT  array_to_string(array_agg(media_information.slug), ',') FROM media_information ".
				" JOIN media_in_venue ON media_information.id = media_in_venue.media_id ".
				" WHERE media_information.deleted_at IS NULL AND media_information.is_file_lost='0' ".
				" AND media_in_venue.removal_approved_at IS NULL AND media_in_venue.venue_id = venue_information.id ".
				" GROUP BY venue_information.id ) AS media_venue_slugs ";
		}

		if ($this->must_have_lat_lng) {
				$this->where[] = " venue_information.lat IS NOT NULL ";
				$this->where[] = " venue_information.lng IS NOT NULL ";
		}

		if ($this->editedByUser) {
			$this->where[] = " venue_information.id IN (SELECT venue_id FROM venue_history WHERE user_account_id = :editedByUser) ";
			$this->params['editedByUser'] = $this->editedByUser->getId();
		}
	}
	
	protected function buildStat() {
		global $DB;
		
		$sql = "SELECT".  implode(",", $this->select)." FROM venue_information ".
			($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
				" ORDER BY venue_information.title ASC ".
				( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$venue = new VenueModel();
			$venue->setFromDataBaseRow($data);
			$results[] = $venue;
		}
		return $results;
		
	}

}

