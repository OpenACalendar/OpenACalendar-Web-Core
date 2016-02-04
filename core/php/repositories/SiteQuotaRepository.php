<?php


namespace repositories;

use models\SiteQuotaModel;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteQuotaRepository {


    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }


	public function loadByCode($code) {

		$stat = $this->app['db']->prepare("SELECT site_quota_information.* FROM site_quota_information ".
				" WHERE site_quota_information.code =:code ");
		$stat->execute(array( 'code'=> strtoupper($code)));
		if ($stat->rowCount() > 0) {
			$siteQuota = new SiteQuotaModel();
			$siteQuota->setFromDataBaseRow($stat->fetch());
			return $siteQuota;
		}
	}
	
	public function loadById($id) {

		$stat = $this->app['db']->prepare("SELECT site_quota_information.* FROM site_quota_information ".
				" WHERE site_quota_information.id =:id ");
		$stat->execute(array( 'id'=>$id));
		if ($stat->rowCount() > 0) {
			$siteQuota = new SiteQuotaModel();
			$siteQuota->setFromDataBaseRow($stat->fetch());
			return $siteQuota;
		}
	}
	
}

