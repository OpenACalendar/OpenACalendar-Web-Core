<?php

namespace repositories\builders;

use models\SiteModel;
use models\VenueModel;
use models\UserAccountModel;
use models\SendEmailModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendEmailRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}

	/** @var UserAccountModel **/
	protected $userCreatedBy;
	
	public function setUserCreatedBy(UserAccountModel $createdBy) {
		$this->userCreatedBy = $createdBy;
	}

	
	protected function build() {

		
		if ($this->site) {
			$this->where[] =  " send_email_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->userCreatedBy) {
			$this->where[] =  " send_email_information.created_by = :created_by ";
			$this->params['created_by'] = $this->userCreatedBy->getId();
		}
	}
	
	protected function buildStat() {

		
		
		$sql = "SELECT send_email_information.* FROM send_email_information ".
				" WHERE ".implode(" AND ", $this->where).
				" ORDER BY send_email_information.id ASC ".
				( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $this->app['db']->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	

	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		
		
		
		$results = array();
		while($data = $this->stat->fetch()) {
			$sendemail = new SendEmailModel();
			$sendemail->setFromDataBaseRow($data);
			$results[] = $sendemail;
		}
		return $results;
		
	}

}

