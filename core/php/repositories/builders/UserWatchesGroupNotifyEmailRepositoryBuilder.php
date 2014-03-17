<?php

namespace repositories\builders;

use models\UserAccountModel;
use models\UserWatchesGroupNotifyEmailModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesGroupNotifyEmailRepositoryBuilder  extends BaseRepositoryBuilder {

	/** @var UserAccountModel **/
	protected $user;
	
	public function setUser(UserAccountModel $user) {
		$this->user = $user;
		return $this;
	}
	
	protected function build() {

		$this->joins[] = " LEFT JOIN group_information ON group_information.id = user_watches_group_notify_email.group_id ";
		$this->selects[] = ' user_watches_group_notify_email.* ';
		$this->selects[] = ' group_information.site_id AS site_id ';
		$this->selects[] = ' group_information.slug AS group_slug ';
		$this->selects[] = ' group_information.title AS group_title ';
		
		if ($this->user) {
			$this->where[] = " user_watches_group_notify_email.user_account_id = :user_account_id";
			$this->params['user_account_id'] = $this->user->getId();
		}
		
	}
	
	protected function buildStat() {
		global $DB;
	
		$sql = "SELECT ".  implode(", ", $this->selects)." FROM user_watches_group_notify_email ".
				implode(" ", $this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY user_watches_group_notify_email.sent_at DESC";
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$email = new UserWatchesGroupNotifyEmailModel();
			$email->setFromDataBaseRow($data);
			$results[] = $email;
		}
		return $results;
		
	}

}

