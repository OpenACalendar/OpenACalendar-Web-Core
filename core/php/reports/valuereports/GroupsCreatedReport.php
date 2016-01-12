<?php

namespace reports\valuereports;

use BaseValueReport;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupsCreatedReport extends BaseValueReport {

    function __construct(Application $app)
    {
        parent::__construct($app);
		$this->hasFilterTime = true;
		$this->hasFilterSite = true;
	}

	public function getExtensionID() { return 'org.openacalendar'; }

	public function getReportTitle()
	{
		return "Groups Created";
	}

	public function getReportID()
	{
		return "GroupsCreated";
	}

	public function run()
	{


		$where = array();
		$params = array();

		if ($this->filterTimeStart) {
			$where[] = " group_information.created_at >= :start_at ";
			$params['start_at'] = $this->filterTimeStart->format("Y-m-d H:i:s");
		}


		if ($this->filterTimeEnd) {
			$where[] = " group_information.created_at <= :end_at ";
			$params['end_at'] = $this->filterTimeEnd->format("Y-m-d H:i:s");
		}

		if ($this->filterSiteId) {
			$where[] = " group_information.site_id = :site_id ";
			$params['site_id'] = $this->filterSiteId;
		}

		$sql = "SELECT COUNT(*) AS count  ".
			" FROM group_information ".
			($where ? " WHERE " . implode(" AND ",$where) : "");

		$stat = $this->app['db']->prepare($sql);
		$stat->execute($params);
		$data = $stat->fetch();
		$this->data = $data['count'];

	}

}
