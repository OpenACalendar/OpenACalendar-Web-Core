<?php

namespace org\openacalendar\contact\repositories\builders;


use org\openacalendar\contact\models\ContactSupportModel;
use repositories\builders\BaseRepositoryBuilder;


/**
 *
 * @package org.openacalendar.contact
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ContactSupportRepositoryBuilder  extends BaseRepositoryBuilder {
	
	protected function build() {
		$this->select[]  = ' contact_support.* ';
	}
	
	protected function buildStat() {
		global $DB;
		
	
		
		
		
		$sql = "SELECT ".  implode(",", $this->select)." FROM contact_support ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
				" ORDER BY contact_support.id ASC ";
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$cList = new ContactSupportModel();
			$cList->setFromDataBaseRow($data);
			$results[] = $cList;
		}
		return $results;
		
	}

}

