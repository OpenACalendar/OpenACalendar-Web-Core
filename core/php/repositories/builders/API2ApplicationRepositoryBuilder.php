<?php

namespace repositories\builders;

use models\API2ApplicationModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2ApplicationRepositoryBuilder extends BaseRepositoryBuilder {
	
	
	protected function build() {
		
		
	}
	
	protected function buildStat() {

		
		
		$sql = "SELECT api2_application_information.* FROM api2_application_information ".
				($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
				" ORDER BY api2_application_information.id ASC ";
	
		$this->stat = $this->app['db']->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
	
		$results = array();
		while($data = $this->stat->fetch()) {
			$area = new API2ApplicationModel();
			$area->setFromDataBaseRow($data);
			$results[] = $area;
		}
		return $results;
		
	}

}

