<?php

namespace repositories\builders;

use models\NewEventDraftModel;
use models\SiteModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class NewEventDraftRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}



	protected function build() {

		$this->select = array('new_event_draft_information.*');

		if ($this->site) {
			$this->where[] =  " new_event_draft_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}

	}
	
	protected function buildStat() {
		global $DB;
		
		
		
		$sql = "SELECT ".  implode(",", $this->select)." FROM new_event_draft_information ".
				implode(" ",$this->joins).
				($this->where?" WHERE ".implode(" AND ", $this->where):"").
				" ORDER BY new_event_draft_information.slug ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			$newEventDraft = new NewEventDraftModel();
			$newEventDraft->setFromDataBaseRow($data);
			$results[] = $newEventDraft;
		}
		return $results;
		
	}

}

