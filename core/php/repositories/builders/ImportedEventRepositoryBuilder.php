<?php

namespace repositories\builders;

use models\ImportedEventModel;
use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use models\TagModel;
use models\VenueModel;
use models\UserAccountModel;
use models\CountryModel;
use models\ImportURLModel;
use models\AreaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventRepositoryBuilder extends BaseRepositoryBuilder {
	
	protected $orderBy = " start_at ";
	protected $orderDirection = " ASC ";

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}


	/** @var ImportURLModel **/
	protected $importURL;
	
	public function setImportURL(ImportURLModel $importURL) {
		$this->importURL = $importURL;
	}

	
	/** @var \DateTime **/
	protected $after;
	
	public function setAfter(\DateTime $a) {
		$this->after = $a;
		return $this;
	}
	
	public function setAfterNow() {
		$this->after = \TimeSource::getDateTime();
		return $this;
	}

	protected function build() {
		global $DB;

		$this->select[] = 'imported_event.*';

		if ($this->site) {
			$this->joins[] = " JOIN import_url_information ON imported_event.import_url_id = import_url_information.id ";
			$this->where[] =  " import_url_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}


		
		if ($this->importURL) {
			$this->where[] =  " imported_event.import_url_id = :import_url_id ";
			$this->params['import_url_id'] = $this->importURL->getId();
		}

		if ($this->after) {
			$this->where[] = ' imported_event.end_at > :after';
			$this->params['after'] = $this->after->format("Y-m-d H:i:s");
		}


	}
	
	protected function buildStat() {
		global $DB;
		
	
				
		$sql = "SELECT ".  implode(",", $this->select)." FROM imported_event ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY  ".$this->orderBy." ".$this->orderDirection .( $this->limit > 0 ? " LIMIT ". $this->limit : "");

		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		$results = array();
		while($data = $this->stat->fetch()) {
			$event = new ImportedEventModel();
			$event->setFromDataBaseRow($data);
			$results[] = $event;
		}
		return $results;
		
	}

}

