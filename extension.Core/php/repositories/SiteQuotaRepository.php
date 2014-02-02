<?php


namespace repositories;

use models\SiteQuotaModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteQuotaRepository {

	
	public function loadByCode($code) {
		global $DB;
		$stat = $DB->prepare("SELECT site_quota_information.* FROM site_quota_information ".
				" WHERE site_quota_information.code =:code ");
		$stat->execute(array( 'code'=> strtoupper($code)));
		if ($stat->rowCount() > 0) {
			$siteQuota = new SiteQuotaModel();
			$siteQuota->setFromDataBaseRow($stat->fetch());
			return $siteQuota;
		}
	}
	
	public function loadById($id) {
		global $DB;
		$stat = $DB->prepare("SELECT site_quota_information.* FROM site_quota_information ".
				" WHERE site_quota_information.id =:id ");
		$stat->execute(array( 'id'=>$id));
		if ($stat->rowCount() > 0) {
			$siteQuota = new SiteQuotaModel();
			$siteQuota->setFromDataBaseRow($stat->fetch());
			return $siteQuota;
		}
	}
	
}

