<?php

namespace repositories\builders;

use models\SiteModel;
use models\GroupModel;
use models\ImportModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}

	/** @var GroupModel **/
	protected $group;
	
	public function setGroup(GroupModel $group) {
		$this->group = $group;
		return $this;
	}

	
	
	protected function build() {

		if ($this->site) {
			$this->where[] =  " import_url_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->group) {
			$this->where[] =  " import_url_information.group_id = :group_id ";
			$this->params['group_id'] = $this->group->getId();
		}
	}
	

	protected function buildStat() {
				global $DB;
		
		
		
		$sql = "SELECT import_url_information.* FROM import_url_information ".
				($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
				" ORDER BY import_url_information.title ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		$results = array();
		while($data = $this->stat->fetch()) {
			$importURL = new ImportModel();
			$importURL->setFromDataBaseRow($data);
			$results[] = $importURL;
		}
		return $results;
		
	}

}

