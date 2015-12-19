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

class UsersWithNotificationsSeriesReport extends BaseSeriesReport {



	public function getExtensionID() { return 'org.openacalendar'; }

	public function getReportID() { return 'UsersWithNotifications'; }

	public function getReportTitle() { return 'Users With Notifications'; }

	public function run() {
		global $DB;

		$stat = $DB->prepare("SELECT user_notification.user_id, COUNT('user_notification.user_id') AS count , MAX(user_account_information.username) AS username".
			" FROM user_notification ".
			" JOIN user_account_information ON user_account_information.id = user_notification.user_id ".
			" GROUP BY user_notification.user_id ".
			" ORDER BY COUNT('user_notification.user_id') DESC");
		$stat->execute();
		$this->data = array();
		while($data = $stat->fetch()) {
			$this->data[$data['user_id']] = new ReportDataItem($data['count'], $data['user_id'], $data['username'],'/sysadmin/user/'.$data['user_id'].'/');
		}

	}


}


