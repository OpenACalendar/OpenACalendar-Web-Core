<?php

namespace repositories\builders;

use models\UserAccountModel;
use models\UserWatchesSitePromptEmailModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSitePromptEmailRepositoryBuilder  extends BaseRepositoryBuilder {

	/** @var UserAccountModel **/
	protected $user;
	
	public function setUser(UserAccountModel $user) {
		$this->user = $user;
		return $this;
	}
	
	protected function build() {

		if ($this->user) {
			$this->where[] = " user_watches_site_prompt_email.user_account_id = :user_account_id";
			$this->params['user_account_id'] = $this->user->getId();
		}
		
	}
	
	protected function buildStat() {
		global $DB;
		
		$sql = "SELECT user_watches_site_prompt_email.* FROM user_watches_site_prompt_email ".
				implode(" ", $this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY user_watches_site_prompt_email.sent_at DESC";
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$email = new UserWatchesSitePromptEmailModel();
			$email->setFromDataBaseRow($data);
			$results[] = $email;
		}
		return $results;
		
	}

}

