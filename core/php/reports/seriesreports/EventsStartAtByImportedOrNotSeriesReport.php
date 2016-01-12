<?php


namespace reports\seriesreports;

use BaseSeriesReport;
use ReportDataItem;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class EventsStartAtByImportedOrNotSeriesReport   extends BaseSeriesReport {

    function __construct(Application $app)
    {
        parent::__construct($app);
		$this->hasFilterTime = true;
		$this->hasFilterSite = true;
	}

	public function getExtensionID() { return 'org.openacalendar'; }

	public function getReportID() { return 'EventsStartAtByImportedOrNot'; }

	public function getReportTitle() { return 'Events by start time - imported or not?'; }


	public function run() {

		$where = array();
		$params = array();

		if ($this->filterSiteId) {
			$where[] = " event_information.site_id = :site_id ";
			$params['site_id'] = $this->filterSiteId;
		}

		if ($this->filterTimeStart) {
			$where[] = " event_information.start_at >= :start_at ";
			$params['start_at'] = $this->filterTimeStart->format("Y-m-d H:i:s");
		}


		if ($this->filterTimeEnd) {
			$where[] = " event_information.start_at <= :end_at ";
			$params['end_at'] = $this->filterTimeEnd->format("Y-m-d H:i:s");
		}


		$this->data = array();

		$sql = "SELECT count(event_information.id) AS count  ".
			" FROM event_information ".
			" LEFT JOIN imported_event_is_event ON imported_event_is_event.event_id = event_information.id ".
			"WHERE event_information.import_url_id IS NULL  AND imported_event_is_event.event_id IS NULL ".
			($where ? " AND " . implode(" AND ",$where) : "");
		$stat = $this->app['db']->prepare($sql);
		$stat->execute($params);
		$data = $stat->fetch();
		$this->data["NOTIMPORTED"] = new ReportDataItem($data['count'], "NOTIMPORTED", "Not Imported", null);


		// TODO When we get to the stage where you can have multiple imported events to one actual event, have to make sure this only counts things once.

		$sql = "SELECT count(event_information.id) AS count  ".
			" FROM event_information ".
			" LEFT JOIN imported_event_is_event ON imported_event_is_event.event_id = event_information.id ".
			"WHERE (event_information.import_url_id IS NOT NULL  OR imported_event_is_event.event_id IS NOT NULL) ".
			($where ? " AND " . implode(" AND ",$where) : "");
		$stat = $this->app['db']->prepare($sql);
		$stat->execute($params);
		$data = $stat->fetch();
		$this->data["IMPORTED"] = new ReportDataItem($data['count'], "IMPORTED", "Imported", null);


	}



} 
