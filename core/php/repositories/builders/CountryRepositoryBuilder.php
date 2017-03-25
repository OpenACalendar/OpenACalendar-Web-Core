<?php

namespace repositories\builders;

use models\SiteModel;
use models\CountryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	protected $siteInformation = false;
	protected $siteIn = false;
	
	public function setSiteInformation(SiteModel $site) {
		$this->site = $site;
		$this->siteInformation = true;
	}

	public function setSiteIn(SiteModel $site) {
		$this->site = $site;
		$this->siteIn = true;		
	}


    protected $titleSearch;

    /**
     * @param mixed $titleSearch
     */
    public function setTitleSearch( $titleSearch ) {
        $this->titleSearch = $titleSearch;
    }

    protected function build() {

		$this->select[] = 'country.*';
		
		
		if ($this->site && $this->siteInformation) {
			$this->joins[] = " LEFT JOIN country_in_site_information ON country_in_site_information.country_id = country.id AND country_in_site_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
			$this->select[] = "  country_in_site_information.is_in AS site_is_in ";
		} else if ($this->site && $this->siteIn) {
			$this->joins[] = " JOIN country_in_site_information ON country_in_site_information.country_id = country.id ";
			$this->where[] = " country_in_site_information.site_id = :site_id";
			$this->where[] = " country_in_site_information.is_in = '1'";
			$this->params['site_id'] = $this->site->getId();
            $this->select[] = " country_in_site_information.cached_future_events AS cached_future_events_in_site ";
		}

        if ($this->titleSearch) {
            $this->where[] =  'country.title ILIKE :title_search ';
            $this->params['title_search'] = "%".strtolower($this->titleSearch)."%";
        }
	}
	
	protected function buildStat() {

		
		
		$sql = "SELECT ".  implode(",", $this->select)." FROM country ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY country.title ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
			
		$this->stat = $this->app['db']->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			$country = new CountryModel();
			$country->setFromDataBaseRow($data);
			$results[] = $country;
		}
		return $results;

	}

}

