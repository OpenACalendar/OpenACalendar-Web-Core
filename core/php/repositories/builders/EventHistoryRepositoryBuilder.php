<?php

namespace repositories\builders;

use models\SiteModel;
use models\EventModel;
use models\EventHistoryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventHistoryRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var EventModel **/
	protected $event;
	
	public function setEvent(EventModel $event) {
		$this->event = $event;
	}

	protected $orderBy = " event_history.created_at ";
	protected $orderDirection = " ASC ";

	public  function setOrderByCreatedAt($newestFirst = true) {
		$this->orderBy = " event_history.created_at ";
		$this->orderDirection = ($newestFirst ? " DESC " : " ASC ");
	}
	
	protected function build() {

		if ($this->event) {
			$this->where[] =  " event_history.event_id = :event_id ";
			$this->params['event_id'] = $this->event->getId();
		}
	}
	
	protected function buildStat() {
		global $DB;
		
	
		
		
		$sql = "SELECT event_history.*, group_information.id AS group_id, group_information.title AS group_title, user_account_information.username AS user_account_username FROM event_history ".
				" LEFT JOIN user_account_information ON user_account_information.id = event_history.user_account_id ".
				" LEFT JOIN event_information ON event_information.id = event_history.event_id ".
				" LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ".
				" LEFT JOIN group_information ON group_information.id = event_in_group.group_id ".
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY ".$this->orderBy." ".$this->orderDirection.( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$eventHistory = new EventHistoryModel();
			$eventHistory->setFromDataBaseRow($data);
			$results[] = $eventHistory;
		}
		return $results;
		
	}

}

