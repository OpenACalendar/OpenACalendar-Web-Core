<?php

namespace repositories\builders;

use models\SiteModel;
use models\CuratedListModel;
use models\UserAccountModel;
use models\EventModel;
use models\GroupModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class CuratedListRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}
	
	/** @var UserAccountModel **/
	protected $userAccount;
	
	public function setUserCanEdit(UserAccountModel $user) {
		$this->userAccount = $user;
	}
	
	/** @var EventModel **/
	protected $eventInfo;
	
	public function setEventInformation(EventModel $event) {
		$this->eventInfo = $event;
	}
	
	/** @var GroupModel **/
	protected $groupInfo;
	
	public function setGroupInformation(GroupModel $group) {
		$this->groupInfo = $group;
	}
	
	protected function build() {

		$this->select[] = ' curated_list_information.* ';
		
		if ($this->userAccount) {
			$this->joins[] = " JOIN user_in_curated_list_information ON user_in_curated_list_information.curated_list_id = curated_list_information.id ".
					"AND user_in_curated_list_information.user_account_id = :user_account_id ";
			$this->params['user_account_id'] = $this->userAccount->getId();
			$this->where[] = " (user_in_curated_list_information.is_owner = '1' OR user_in_curated_list_information.is_editor = '1'  ) ";
		}
		
		if ($this->site) {
			$this->where[] =  " curated_list_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->eventInfo) {
			$this->joins[] = " LEFT JOIN event_in_curated_list ON event_in_curated_list.curated_list_id = curated_list_information.id AND   ".
					" event_in_curated_list.event_id = :event_id AND event_in_curated_list.removed_at IS NULL ";
			$this->params['event_id'] = $this->eventInfo->getId();
			$this->select[] =  " event_in_curated_list.added_at AS is_event_in_list ";
		}
		
		if ($this->groupInfo) {
			$this->joins[] = " LEFT JOIN group_in_curated_list ON group_in_curated_list.curated_list_id = curated_list_information.id AND   ".
					" group_in_curated_list.group_id = :group_id AND group_in_curated_list.removed_at IS NULL ";
			$this->params['group_id'] = $this->groupInfo->getId();
			$this->select[] =  " group_in_curated_list.added_at AS is_group_in_list ";
		}
	}
	
	protected function buildStat() {
				global $DB;
	
		
		$sql = "SELECT ".  implode(",", $this->select)." FROM curated_list_information ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
				" ORDER BY curated_list_information.title ASC ";
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			$cList = new CuratedListModel();
			$cList->setFromDataBaseRow($data);
			$results[] = $cList;
		}
		return $results;
		
	}

}

