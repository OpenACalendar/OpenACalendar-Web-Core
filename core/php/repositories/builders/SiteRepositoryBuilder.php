<?php

namespace repositories\builders;

use models\SiteModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteRepositoryBuilder  extends BaseRepositoryBuilder {
	
	/** @var UserAccountModel **/
	protected $userInterestedIn;

	public function setUserInterestedIn(UserAccountModel $user) {
		$this->userInterestedIn = $user;
	}
	
	protected $isListedInIndexOnly = false;

	public function setIsListedInIndexOnly ($value) {
		$this->isListedInIndexOnly = $value;
	}

	protected $isOpenBySysAdminsOnly = false;
	
	public function setIsOpenBySysAdminsOnly($value) {
		$this->isOpenBySysAdminsOnly = $value;
	}
	
	protected function build() {
		if ($this->userInterestedIn) {
			$this->params['user_in_site'] = $this->userInterestedIn->getId();

			// user watches site
			$this->joins[] = " LEFT JOIN user_watches_site_information ON user_watches_site_information.site_id = site_information.id AND user_watches_site_information.user_account_id = :user_in_site ";

			// user watches group information
			$inner = "SELECT  group_information.site_id AS site_id, user_watches_group_information.user_account_id AS user_account_id ".
				"FROM user_watches_group_information ".
				" JOIN group_information ON group_information.id = user_watches_group_information.group_id ".
				" WHERE user_watches_group_information.is_watching = '1' AND user_watches_group_information.user_account_id = :user_in_site ".
				" GROUP BY group_information.site_id, user_watches_group_information.user_account_id ";
			$this->joins[] = " LEFT JOIN (".$inner.") AS user_watches_group ON user_watches_group.site_id = site_information.id  ";
			
			// user watches area information
			$inner = "SELECT  area_information.site_id AS site_id, user_watches_area_information.user_account_id AS user_account_id ".
				"FROM user_watches_area_information ".
				" JOIN area_information ON area_information.id = user_watches_area_information.area_id ".
				" WHERE user_watches_area_information.is_watching = '1' AND user_watches_area_information.user_account_id = :user_in_site ".
				" GROUP BY area_information.site_id, user_watches_area_information.user_account_id ";
			$this->joins[] = " LEFT JOIN (".$inner.") AS user_watches_area ON user_watches_area.site_id = site_information.id  ";


			// TODO user at event. https://github.com/OpenACalendar/OpenACalendar-Web-Core/issues/357

			// Permissions
			$inner = "SELECT user_group_in_site.site_id AS site_id, user_in_user_group.user_account_id AS user_account_id FROM user_group_in_site ".
				"LEFT JOIN user_in_user_group ON user_in_user_group.user_group_id = user_group_in_site.user_group_id ".
				"WHERE user_group_in_site.removed_at IS NULL AND user_in_user_group.removed_at IS NULL AND user_in_user_group.user_account_id = :user_in_site ".
				"GROUP BY user_group_in_site.site_id, user_in_user_group.user_account_id ";
			$this->joins[] = " LEFT JOIN (".$inner.") AS user_permission_in_site ON user_permission_in_site.site_id = site_information.id  ";

			// put it all together
			$this->where[] = " (  user_watches_site_information.is_watching = '1' ".
				" OR user_permission_in_site.user_account_id = :user_in_site ".
				" OR user_watches_group.user_account_id = :user_in_site ".
				" OR user_watches_area.user_account_id = :user_in_site ".
				" )";
		}

		if ($this->isListedInIndexOnly) {
			$this->where[] = " site_information.is_listed_in_index = '1' ";
		}
		
		if ($this->isOpenBySysAdminsOnly) {
			$this->where[] = "  site_information.is_closed_by_sys_admin = '0' ";
		}
	}
	
	protected function buildStat() {
			global $DB;
		
		
	
		
		$sql = "SELECT site_information.* FROM site_information ".
				implode(" ", $this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY site_information.id ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
	
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$event = new SiteModel();
			$event->setFromDataBaseRow($data);
			$results[] = $event;
		}
		return $results;
		
	}

}

