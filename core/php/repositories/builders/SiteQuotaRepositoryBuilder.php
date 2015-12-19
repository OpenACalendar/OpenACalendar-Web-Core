<?php

namespace repositories\builders;

use models\SiteQuotaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteQuotaRepositoryBuilder  extends BaseRepositoryBuilder {
	

	
	protected function build() {
		$this->select[] = 'site_quota_information.*';
		
	}
	
	protected function buildStat() {
		global $DB;
	
	
		
		
		$sql = "SELECT ".  implode(",", $this->select)." FROM site_quota_information ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY site_quota_information.id ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
			
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$siteQuota = new SiteQuotaModel();
			$siteQuota->setFromDataBaseRow($data);
			$results[] = $siteQuota;
		}
		return $results;
		
	}

}

