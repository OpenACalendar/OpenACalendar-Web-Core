<?php

namespace repositories\builders;

use models\SiteModel;
use models\UserAccountModel;
use models\SiteAccessRequestModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteAccessRequestRepositoryBuilder extends BaseRepositoryBuilder  {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}

	/** @var UserAccountModel **/
	protected $user;
	
	public function setUser(UserAccountModel $user) {
		$this->user = $user;
	}

	protected $includeCompleted = false;
	

	protected function build() {

		if ($this->site) {
			$this->where[] =  " site_access_request.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->user) {
			$this->where[] =  " site_access_request.user_account_id = :user_account_id ";
			$this->params['user_account_id'] = $this->user->getId();
		}
		
		if (!$this->includeCompleted) {
			$this->where[] = " site_access_request.granted_by IS NULL AND site_access_request.rejected_by IS NULL ";
		}
	}
	
	protected function buildStat() {
		global $DB;
	
		
		
		$sql = "SELECT site_access_request.* FROM site_access_request ".
				" WHERE ".implode(" AND ", $this->where).
				" ORDER BY site_access_request.id ASC ";
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$siteAccessRequest = new SiteAccessRequestModel();
			$siteAccessRequest->setFromDataBaseRow($data);
			$results[] = $siteAccessRequest;
		}
		return $results;
		
	}

}

