<?php

namespace repositories\builders;

use models\EventCustomFieldDefinitionModel;
use models\SiteModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventCustomFieldDefinitionRepositoryBuilder extends BaseRepositoryBuilder {


	/** @var SiteModel **/
	protected $site;

	public function setSite(SiteModel $site) {
		$this->site = $site;
	}


	protected function build() {

		$this->select[] = 'event_custom_field_definition_information.*';

		if ($this->site) {
			$this->where[] =  " event_custom_field_definition_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}

	}

	protected function buildStat() {
		global $DB;


		$sql = "SELECT " . implode(", ",$this->select) . " FROM event_custom_field_definition_information ".
			implode(" ",$this->joins).
			($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
			" ORDER BY event_custom_field_definition_information.key ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");

		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);

	}

	public function fetchAll() {

		$this->buildStart();
		$this->build();
		$this->buildStat();


		$results = array();
		while($data = $this->stat->fetch()) {
			$area = new EventCustomFieldDefinitionModel();
			$area->setFromDataBaseRow($data);
			$results[] = $area;
		}
		return $results;

	}

}

