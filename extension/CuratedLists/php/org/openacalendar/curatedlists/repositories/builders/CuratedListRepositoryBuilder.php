<?php

namespace org\openacalendar\curatedlists\repositories\builders;

use models\SiteModel;
use org\openacalendar\curatedlists\models\CuratedListModel;
use models\UserAccountModel;
use models\EventModel;
use models\GroupModel;
use repositories\builders\BaseRepositoryBuilder;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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

	/** @var EventModel **/
	protected $containsEvent;

	/**
	 * @param \models\EventModel $containsEvent
	 */
	public function setContainsEvent($containsEvent)
	{
		$this->containsEvent = $containsEvent;
	}

	/** @var GroupModel **/
	protected $containsGroup;

	/**
	 * @param \models\GroupModel $containsGroup
	 */
	public function setContainsGroup($containsGroup)
	{
		$this->containsGroup = $containsGroup;
	}

	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}

    protected $freeTextSearch;

    public function setFreeTextsearch($freeTextSearch) {
        $this->freeTextSearch = $freeTextSearch;
    }


    protected $include_future_events_only = false;

    /**
     * @param boolean $include_future_events_only
     */
    public function setIncludeFutureEventsOnly($include_future_events_only)
    {
        $this->include_future_events_only = $include_future_events_only;
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

		if ($this->containsEvent) {
			$this->params['event_id'] = $this->containsEvent->getId();

			// event directly in list?
			$this->joins[] = " LEFT JOIN event_in_curated_list ON event_in_curated_list.curated_list_id = curated_list_information.id AND   ".
				" event_in_curated_list.event_id = :event_id AND event_in_curated_list.removed_at IS NULL ";

			// event in list via group?
			$this->joins[] = " LEFT JOIN ( SELECT group_in_curated_list.curated_list_id, MAX(group_in_curated_list.group_id) AS group_id FROM group_in_curated_list ".
				" JOIN event_in_group ON event_in_group.group_id = group_in_curated_list.group_id ".
				" WHERE event_in_group.event_id = :event_id AND group_in_curated_list.removed_at IS NULL AND event_in_group.removed_at IS NULL ".
				" GROUP BY group_in_curated_list.curated_list_id ) AS event_in_curated_list_via_group_table ON event_in_curated_list_via_group_table.curated_list_id = curated_list_information.id ";

			$this->where[] = " (event_in_curated_list.added_at IS NOT NULL OR event_in_curated_list_via_group_table.group_id IS NOT NULL) ";

		} else if ($this->eventInfo) {
			$this->params['event_id'] = $this->eventInfo->getId();

			// event directly in list?
			$this->joins[] = " LEFT JOIN event_in_curated_list ON event_in_curated_list.curated_list_id = curated_list_information.id AND   ".
					" event_in_curated_list.event_id = :event_id AND event_in_curated_list.removed_at IS NULL ";
			$this->select[] =  " event_in_curated_list.added_at AS is_event_in_list ";

			// event in list via group?
			$this->joins[] = " LEFT JOIN ( SELECT group_in_curated_list.curated_list_id, MAX(group_in_curated_list.group_id) AS group_id FROM group_in_curated_list ".
				" JOIN event_in_group ON event_in_group.group_id = group_in_curated_list.group_id ".
				" WHERE event_in_group.event_id = :event_id AND group_in_curated_list.removed_at IS NULL AND event_in_group.removed_at IS NULL ".
				" GROUP BY group_in_curated_list.curated_list_id ) AS event_in_curated_list_via_group_table ON event_in_curated_list_via_group_table.curated_list_id = curated_list_information.id ";
			$this->select[] = " event_in_curated_list_via_group_table.group_id AS event_in_list_via_group_id ";
		}

		if ($this->containsGroup) {

			$this->joins[] = " LEFT JOIN group_in_curated_list ON group_in_curated_list.curated_list_id = curated_list_information.id AND   ".
				" group_in_curated_list.group_id = :group_id AND group_in_curated_list.removed_at IS NULL ";
			$this->params['group_id'] = $this->containsGroup->getId();
			$this->where[] =  " group_in_curated_list.added_at IS NOT NULL ";

		} else if ($this->groupInfo) {
			$this->joins[] = " LEFT JOIN group_in_curated_list ON group_in_curated_list.curated_list_id = curated_list_information.id AND   ".
					" group_in_curated_list.group_id = :group_id AND group_in_curated_list.removed_at IS NULL ";
			$this->params['group_id'] = $this->groupInfo->getId();
			$this->select[] =  " group_in_curated_list.added_at AS is_group_in_list ";
		}

		if (!$this->include_deleted) {
			$this->where[] = " curated_list_information.is_deleted = '0' ";
		}

        if ($this->include_future_events_only) {
            $this->where[] = " curated_list_information.cached_future_events > 0 ";
        }


        if ($this->freeTextSearch) {
            $this->where[] =  '(CASE WHEN curated_list_information.title IS NULL THEN \'\' ELSE curated_list_information.title END )  || \' \' || '.
                '(CASE WHEN curated_list_information.description IS NULL THEN \'\' ELSE curated_list_information.description END )'.
                ' ILIKE :free_text_search ';
            $this->params['free_text_search'] = "%".strtolower($this->freeTextSearch)."%";
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

