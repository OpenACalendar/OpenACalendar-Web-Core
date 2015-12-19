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

class UsersWithEventsEditedSeriesReport extends BaseSeriesReport {


	function __construct()
	{
		$this->hasFilterTime = true;
	}


	public function getExtensionID() { return 'org.openacalendar'; }

	public function getReportID() { return 'UsersWithEventsEdited'; }

	public function getReportTitle() { return 'Users With Events Edited'; }

	public function run() {
		global $DB;

		$where = array();
		$params = array();

		if ($this->filterTimeStart) {
			$where[] = " event_history.created_at >= :start_at ";
			$params['start_at'] = $this->filterTimeStart->format("Y-m-d H:i:s");
		}


		if ($this->filterTimeEnd) {
			$where[] = " event_history.created_at <= :end_at ";
			$params['end_at'] = $this->filterTimeEnd->format("Y-m-d H:i:s");
		}


		$innerSql = "SELECT user_account_id, event_id FROM event_history ".
			"WHERE user_account_id IS NOT NULL ".
			($where ? " AND " . implode(" AND ",$where) : "").
			"GROUP BY user_account_id, event_id";
		$sql = "select eh.user_account_id, COUNT(eh.event_id) AS count  , MAX(user_account_information.username) AS username ".
			"FROM (".$innerSql.") AS eh ".
			" JOIN user_account_information ON user_account_information.id = eh.user_account_id ".
			"GROUP BY eh.user_account_id ".
			"ORDER BY count DESC ";

		$stat = $DB->prepare($sql);
		$stat->execute($params);
		$this->data = array();
		while($data = $stat->fetch()) {
			$this->data[$data['user_account_id']] = new ReportDataItem($data['count'], $data['user_account_id'], $data['username'],'/sysadmin/user/'.$data['user_account_id'].'/');
		}

	}


}


