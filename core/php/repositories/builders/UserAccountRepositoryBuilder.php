<?php

namespace repositories\builders;

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\CuratedListModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountRepositoryBuilder  extends BaseRepositoryBuilder {


	protected $requestAccessSite = null;

	public function setRequestAccessSite(SiteModel $site) {
		$this->requestAccessSite = $site;
	}
	
	protected $editNotOwnCuratedList = null;
	
	public function canEditNotOwnCuratedList(CuratedListModel $curatedList) {
		$this->editNotOwnCuratedList = $curatedList;
	}
	
	
	protected $watchesSite;
	
	public function setWatchesSite(SiteModel $watchesSite) {
		$this->watchesSite = $watchesSite;
		return $this;
	}
	
	/** @var GroupModel **/
	protected $watchesGroup;
	
	public function setWatchesGroup(GroupModel $watchesGroup) {
		$this->watchesGroup = $watchesGroup;
		return $this;
	}

	protected $groupNeeded;

	/** @var UserGroupModel **/
	protected $inUserGroup;

	/**
	 * @param \repositories\builders\UserGroupModel $inUserGroup
	 */
	public function setInUserGroup($inUserGroup)
	{
		$this->inUserGroup = $inUserGroup;
		return $this;
	}




	protected function build() {

		$this->select[]  = 'user_account_information.*';
		$this->groupNeeded = false;

		
		if ($this->requestAccessSite) {
			$this->joins[] = " LEFT JOIN user_in_site_information ON user_in_site_information.user_account_id = user_account_information.id ";
			$this->joins[] = " JOIN site_access_request ON site_access_request.user_account_id = user_account_information.id AND ".
					" site_access_request.granted_by IS NULL AND site_access_request.rejected_by IS NULL AND site_access_request.site_id = :site_id";
			$this->where[] = " (  user_in_site_information.user_account_id IS NULL OR ".
					"(user_in_site_information.is_editor != '1' AND  user_in_site_information.is_administrator != '1' ".
					" AND  user_in_site_information.is_owner != '1' ))";
			$this->params['site_id'] = $this->requestAccessSite->getId();
			$this->groupNeeded = true;
		}
		
		if ($this->editNotOwnCuratedList) {
			$this->joins[] = " JOIN user_in_curated_list_information ON user_in_curated_list_information.user_account_id = user_account_information.id ".
					"AND user_in_curated_list_information.curated_list_id = :curated_list_id ";  
			$this->params['curated_list_id'] = $this->editNotOwnCuratedList->getId();
			$this->where[] = " user_in_curated_list_information.is_owner = '0' AND user_in_curated_list_information.is_editor = '1' ";
		}
	
		if ($this->watchesSite) {
			$this->joins[] = " JOIN user_watches_site_information ON ".
					"user_watches_site_information.user_account_id = user_account_information.id  AND ".
					"user_watches_site_information.site_id = :site_id AND ".
					"user_watches_site_information.is_watching = '1'";
			$this->params['site_id'] = $this->watchesSite->getId();
		}
		
	
		if ($this->watchesGroup) {
			$this->joins[] = " JOIN user_watches_group_information ON ".
					"user_watches_group_information.user_account_id = user_account_information.id  AND ".
					"user_watches_group_information.group_id = :group_id AND ".
					"user_watches_group_information.is_watching = '1'";
			$this->params['group_id'] = $this->watchesGroup->getId();
		}

		if ($this->inUserGroup) {
			$this->joins[] = " JOIN user_in_user_group ON user_in_user_group.user_account_id = user_account_information.id ".
				"AND user_in_user_group.user_group_id = :user_group_id AND user_in_user_group.removed_at IS NULL  ";
			$this->params['user_group_id'] = $this->inUserGroup->getId();
		}

	}
	
	protected function buildStat() {
		global $DB;
		
		$sql = "SELECT ".implode(",",$this->select)." FROM user_account_information ".
				implode(" ", $this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				($this->groupNeeded ? " GROUP BY user_account_information.id ":"")." ORDER BY user_account_information.id ASC ";
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
			
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$event = new UserAccountModel();
			$event->setFromDataBaseRow($data);
			$results[] = $event;
		}
		return $results;
		
	}

}

