<?php


namespace repositories\builders;

use models\EventModel;
use models\UserAtEventModel;
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UserAtEventRepositoryBuilder  extends BaseRepositoryBuilder {


	/** @var EventModel **/
	protected $eventOnly;
	
	public function setEventOnly(EventModel $event) {
		$this->eventOnly = $event;
	}
	
	protected $planAttendingMaybeOnly = false;
	
	public function setPlanAttendingMaybeOnly($value) {
		$this->planAttendingMaybeOnly = $value;
	}
	
	protected $planAttendingYesOnly = false;
	
	public function setPlanAttendingYesOnly($value) {
		$this->planAttendingYesOnly = $value;
	}
	
	protected $planPublicOnly = false;
	
	public function setPlanPublicOnly($value) {
		$this->planPublicOnly = $value;
	}
	
	protected $planPrivateOnly = false;
	
	public function setPlanPrivateOnly($value) {
		$this->planPrivateOnly = $value;
	}

	protected function build() {

		
		if ($this->eventOnly) {
			$this->where[] = ' user_at_event_information.event_id = :event_id ';
			$this->params['event_id'] = $this->eventOnly->getId();
		}
		
		if ($this->planAttendingMaybeOnly) {
			$this->where[] = " user_at_event_information.is_plan_maybe_attending =  '1' ";
		} elseif ($this->planAttendingYesOnly) {
			$this->where[] = " user_at_event_information.is_plan_attending =  '1' ";
		} 
		
		if ($this->planPublicOnly) {
			$this->where[] = " user_at_event_information.is_plan_public =  '1' ";
		} elseif ($this->planPrivateOnly) {
			$this->where[] = " user_at_event_information.is_plan_public =  '0' ";
		} 
	}
	
	protected function buildStat() {
		global $DB;
		
		
		$sql = "SELECT user_at_event_information.*, user_account_information.username AS user_username FROM user_at_event_information ".
				" JOIN user_account_information ON user_account_information.id = user_at_event_information.user_account_id ".
				implode(" ", $this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY user_account_information.username ASC ".
				( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$uae = new UserAtEventModel();
			$uae->setFromDataBaseRow($data);
			$results[] = $uae;
		}
		return $results;
		
	}
	
	
}

