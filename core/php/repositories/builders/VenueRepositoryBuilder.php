<?php

namespace repositories\builders;

use models\SiteModel;
use models\VenueModel;
use models\CountryModel;
use models\AreaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
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

	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}
	
	
	protected function build() {
		global $DB;

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
		
		if (!$this->include_deleted) {
			$this->where[] = " venue_information.is_deleted = '0' ";
		}
		
	}
	
	protected function buildStat() {
		global $DB;
		
		$sql = "SELECT venue_information.* FROM venue_information ".
				" WHERE ".implode(" AND ", $this->where).
				" ORDER BY venue_information.title ASC ";
	
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

