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
class AreaRepositoryBuilder extends BaseRepositoryBuilder {
	

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
	protected $parentArea;
	
	public function setParentArea(AreaModel $area) {
		$this->parentArea = $area;
	}

	protected $noParentArea = false;
	
	public function setNoParentArea($noParentArea) {
		$this->noParentArea = $noParentArea;
	}

	
	protected $cacheNeedsBuildingOnly = false;
	
	public function setCacheNeedsBuildingOnly($cacheNeedsBuildingOnly) {
		$this->cacheNeedsBuildingOnly = $cacheNeedsBuildingOnly;
	}

		

	protected $freeTextSearch;

	public function setFreeTextSearch($freeTextSearch) {
		$this->freeTextSearch = $freeTextSearch;
	}



	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}


	public $include_parent_levels = 0;

	/**
	 * @param int $include_parent_levels
	 */
	public function setIncludeParentLevels($include_parent_levels)
	{
		$this->include_parent_levels = $include_parent_levels;
	}



	protected function build() {

		$this->select[] = 'area_information.*';

		if ($this->site) {
			$this->where[] =  " area_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->country) {
			$this->where[] =  " area_information.country_id = :country_id ";
			$this->params['country_id'] = $this->country->getId();
		}
		
		if (!$this->include_deleted) {
			$this->where[] = " area_information.is_deleted = '0' ";
		}
		
		if ($this->noParentArea) {
			$this->where[] = ' area_information.parent_area_id IS null ';
		} else if ($this->parentArea) {
			$this->where[] =  " area_information.parent_area_id = :parent_id ";
			$this->params['parent_id'] = $this->parentArea->getId();
		}
		
		if ($this->cacheNeedsBuildingOnly) {
			$this->where[] = " area_information.cache_area_has_parent_generated = '0'";
		}

		if ($this->freeTextSearch) {
			$this->where[] =  ' area_information.title ILIKE :free_text_search ';
			$this->params['free_text_search'] = "%".strtolower($this->freeTextSearch)."%";
		}

		if ($this->include_parent_levels > 0) {
			$this->joins[] = " LEFT JOIN area_information AS area_information_parent_1 ON area_information.parent_area_id = area_information_parent_1.id ";
			$this->select[] = " area_information_parent_1.title AS parent_1_title";
		}
	}
	
	protected function buildStat() {
		global $DB;
		
		
		$sql = "SELECT " . implode(", ",$this->select) . " FROM area_information ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
				" ORDER BY area_information.title ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
	
		$results = array();
		while($data = $this->stat->fetch()) {
			$area = new AreaModel();
			$area->setFromDataBaseRow($data);
			$results[] = $area;
		}
		return $results;
		
	}

}

