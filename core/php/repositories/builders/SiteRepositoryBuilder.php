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
			$this->joins[] = " LEFT JOIN user_watches_site_information ON user_watches_site_information.site_id = site_information.id AND user_watches_site_information.user_account_id = :user_in_site ";
			$this->params['user_in_site'] = $this->userInterestedIn->getId();
			$this->where[] = " (   user_watches_site_information.is_watching = '1')";
			// TODO could do user_watches_group_information to?
			// TODO permissions?
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
				" ORDER BY site_information.id ASC ";
	
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

