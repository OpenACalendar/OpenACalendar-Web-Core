<?php

namespace repositories\builders;

use models\SiteModel;
use models\SysadminCommentModel;
use models\UserAccountModel;
use models\VenueModel;
use models\CountryModel;
use models\AreaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SysadminCommentRepositoryBuilder extends BaseRepositoryBuilder {
	

	/** @var  UserAccountModel */
	protected $user;

	public function setUser(UserAccountModel $user)
	{
		$this->user = $user;
	}




	protected function build() {

		$this->select[] = 'sysadmin_comment_information.*';

		if ($this->user) {
			$this->joins[] = "  JOIN sysadmin_comment_about_user ON sysadmin_comment_about_user.sysadmin_comment_id = sysadmin_comment_information.id  ";
			$this->where[] =  " sysadmin_comment_about_user.user_account_id = :user_account_id ";
			$this->params['user_account_id'] = $this->user->getId();
		}

	}

	
	protected function buildStat() {
		global $DB;
		
		
		$sql = "SELECT " . implode(", ",$this->select) . " FROM sysadmin_comment_information ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : '').
				" ORDER BY sysadmin_comment_information.created_at ASC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
	
		$results = array();
		while($data = $this->stat->fetch()) {
			$sac = new SysadminCommentModel();
			$sac->setFromDataBaseRow($data);
			$results[] = $sac;
		}
		return $results;
		
	}

}

