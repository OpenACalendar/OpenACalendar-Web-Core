<?php


namespace sysadmin\controllers;

use reports\SeriesOfSeriesByTimeReport;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use sysadmin\forms\RunSeriesByTimeReportForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


class SeriesByTimeReportController {

	protected $report;

	function build($extid, $reportId, Application $app) {
		$extension = $app['extensions']->getExtensionById($extid);
		if (!$extension) return false;
		foreach($extension->getSeriesReports() as $report) {
			if ($report->getReportID() == $reportId && $report->getHasFilterTime()) {
				$this->report = $report;
				return true;
			}
		}
		return false;
	}

	function index($extid, $reportid, Request $request, Application $app) {
		if (!$this->build($extid, $reportid, $app)) {
			die("NO");
		}



		$form = $app['form.factory']->create(new RunSeriesByTimeReportForm($this->report));

		return $app['twig']->render('sysadmin/seriesbytimereport/index.html.twig', array(
			'report'=>$this->report,
			'form'=>$form->createView(),
		));
	}

	function run($extid, $reportid, Request $request, Application $app) {
		if (!$this->build($extid, $reportid, $app)) {
			die("NO");
		}

		$form = $app['form.factory']->create(new RunSeriesByTimeReportForm($this->report));
		$form->bind($request);

		$startAt = $form->get('start_at')->getData();
		$endAt = $form->get('end_at')->getData();
		$period = $form->get('timeperiod')->getData();

		if ($this->report->getHasFilterSite()) {
			$filterSiteID = $form->get('site_id')->getData();
			$this->report->setFilterSiteId($filterSiteID);
		}
		
		$reportByTime = new SeriesOfSeriesByTimeReport($this->report, $startAt, $endAt, $period);
		$reportByTime->run();

		if ($form->get('output')->getData() == 'htmlTable') {
			return $app['twig']->render('sysadmin/seriesbytimereport/run.table.html.twig', array(
				'report'=>$this->report,
				'reportData'=>$reportByTime->getData(),
				'reportDataKeys'=>$reportByTime->getDataKeys(),
				'startAt'=>$startAt,
				'endAt'=>$endAt,
			));
		} else if ($form->get('output')->getData() == 'csv') {

			$csv = "Label Text, ";
			foreach($reportByTime->getDataKeys() as $dataKey=>$dataValue) {
				$csv .= str_replace('"','','Data '.$dataValue->getLabelText()).',';
			}
			$csv .= "\n";
			foreach($reportByTime->getData() as $data) {
				$csv .= '"'.str_replace('"','',$data->getLabelText()).'",';
				foreach($reportByTime->getDataKeys() as $dataKey=>$dataValue) {
					if (array_key_exists($dataKey, $data->getData())) {
						$csv .= $data->getData()[$dataKey]->getData().",";
					} else {
						$csv .= "0,";
					}
				}
				$csv .= "\n";
			}

			$response = new Response($csv);
			$response->headers->set('Content-Type', 'text/csv');
			return $response;


		}
	}

}
