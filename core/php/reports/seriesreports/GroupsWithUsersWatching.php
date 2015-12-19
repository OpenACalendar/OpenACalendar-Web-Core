<?php


namespace reports\seriesreports;

use BaseSeriesReport;
use ReportDataItem;



/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class GroupsWithUsersWatching  extends BaseSeriesReport {

	function __construct()
	{
		$this->hasFilterTime = false;
		$this->hasFilterSite = true;
	}

	public function getExtensionID() { return 'org.openacalendar'; }

	public function getReportID() { return 'GroupsWithUsersWatching'; }

	public function getReportTitle() { return 'Groups With Users Watching'; }

	public function run() {
		global $DB;

		$where = array();
		$params = array();

		if ($this->filterSiteId) {
			$where[] = " group_information.site_id = :site_id ";
			$params['site_id'] = $this->filterSiteId;
		}

		$sql = "SELECT group_information.id, group_information.title,  group_information.slug, group_information.site_id, count(user_watches_group_information.user_account_id) AS count FROM user_watches_group_information ".
			" JOIN group_information ON group_information.id = user_watches_group_information.group_id ".
			"WHERE user_watches_group_information.is_watching = '1' ".
			($where ? " AND " . implode(" AND ",$where) : "").
			"GROUP BY group_information.id ".
			"ORDER BY count DESC ";
		$stat = $DB->prepare($sql);
		$stat->execute($params);
		$this->data = array();
		while($data = $stat->fetch()) {
			$this->data[$data['id']] = new ReportDataItem($data['count'], $data['id'], $data['title'],'/sysadmin/site/'.$data['site_id'].'/group/'.$data['slug']);
		}

	}

} 
