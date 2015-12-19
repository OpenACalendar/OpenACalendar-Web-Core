<?php

namespace repositories\builders\filterparams;

use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use repositories\builders\GroupRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupFilterParams {

	function __construct() {
		$this->groupRepositoryBuilder = new GroupRepositoryBuilder();
		$this->groupRepositoryBuilder->setLimit(100);
	}

	
	protected $groupRepositoryBuilder;
	
	public function getGroupRepositoryBuilder() {
		return $this->groupRepositoryBuilder;
	}

		
	// ############################### params
	
	protected $freeTextSearch = null;
	
	public function set($data) {
		if (isset($data['groupListFilterDataSubmitted'])) {
		
			// Free Text Search
			if (isset($data['freeTextSearch']) && trim($data['freeTextSearch'])) {
				$this->freeTextSearch = $data['freeTextSearch'];
			}
			
		}
		
		// apply to search
		if ($this->freeTextSearch) {
			$this->groupRepositoryBuilder->setFreeTextsearch($this->freeTextSearch);
		}
	}
	
	public function getFreeTextSearch() {
		return $this->freeTextSearch;
	}




	
}


