<?php

namespace repositories\builders\filterparams;

use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use repositories\builders\VenueRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueFilterParams {

	function __construct() {
		$this->venueRepositoryBuilder = new VenueRepositoryBuilder();
		$this->venueRepositoryBuilder->setLimit(100);
	}

	
	protected $venueRepositoryBuilder;
	
	public function getVenueRepositoryBuilder() {
		return $this->venueRepositoryBuilder;
	}

		
	// ############################### params
	
	protected $freeTextSearch = null;
	
	public function set($data) {
		if (isset($data['venueListFilterDataSubmitted'])) {
		
			// Free Text Search
			if (isset($data['freeTextSearch']) && trim($data['freeTextSearch'])) {
				$this->freeTextSearch = $data['freeTextSearch'];
			}
			
		}
		
		// apply to search
		if ($this->freeTextSearch) {
			$this->venueRepositoryBuilder->setFreeTextsearch($this->freeTextSearch);
		}
	}
	
	public function getFreeTextSearch() {
		return $this->freeTextSearch;
	}




	
}


