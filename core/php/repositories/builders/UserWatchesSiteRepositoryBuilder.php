<?php

namespace repositories\builders;

use models\SiteModel;
use models\UserWatchesSiteModel;
/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSiteRepositoryBuilder  extends BaseRepositoryBuilder {
	
	
	/** @var SiteModel **/
	protected $site;

	public function setSite(SiteModel $site) {
		$this->site = $site;
		return $this;
	}
	
	protected $onlyCurrent = true;

	protected function build() {
	
		if ($this->onlyCurrent) {
			$this->where[] = "user_watches_site_information.is_watching = '1'";
		}
		
		if ($this->site) {
			$this->where[] = " user_watches_site_information.site_id = :site_id";
			$this->params['site_id'] = $this->site->getId();
		}

	}
	
	protected function buildStat() {
		global $DB;
		
		
	
		$sql = "SELECT user_watches_site_information.* FROM user_watches_site_information ".
				implode(" ", $this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "");
	
		
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$uwsm = new UserWatchesSiteModel();
			$uwsm->setFromDataBaseRow($data);
			$results[] = $uwsm;
		}
		return $results;
		
	}

}

