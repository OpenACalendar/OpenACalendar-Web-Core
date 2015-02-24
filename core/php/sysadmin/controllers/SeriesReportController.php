<?php

namespace sysadmin\controllers;

use BaseReport;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use sysadmin\forms\RunSeriesReportForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SeriesReportController {

	/** @var  BaseReport */
	protected $report;

	function build($extid, $reportId, Application $app) {
		$extension = $app['extensions']->getExtensionById($extid);
		if (!$extension) return false;
		foreach($extension->getSeriesReports() as $report) {
			if ($report->getReportID() == $reportId) {
				$this->report = $report;
				return true;
			}
		}
		return false;
	}

	function index($extid, $reportid, Request $request, Application $app) {
		if (!$this->build($extid, $reportid, $app)) {
			die("NO REPORT FOUND"); // TODO
		}



		$form = $app['form.factory']->create(new RunSeriesReportForm($this->report));

		return $app['twig']->render('sysadmin/seriesreport/index.html.twig', array(
			'report'=>$this->report,
			'form'=>$form->createView(),
		));
	}

	function run($extid, $reportid, Request $request, Application $app) {
		if (!$this->build($extid, $reportid, $app)) {
			die("NO");
		}

		$form = $app['form.factory']->create(new RunSeriesReportForm($this->report));
		$form->bind($request);

		$filterStartAt = $filterEndAt = $filterSiteID = null;
		if ($this->report->getHasFilterTime()) {
			$filterStartAt = $form->get('start_at')->getData();
			$filterEndAt = $form->get('end_at')->getData();
			$this->report->setFilterTime($filterStartAt,$filterEndAt);
		}
		if ($this->report->getHasFilterSite()) {
			$filterSiteID = $form->get('site_id')->getData();
			$this->report->setFilterSiteId($filterSiteID);
		}

		$this->report->run();

		if ($form->get('output')->getData() == 'htmlTable') {
			return $app['twig']->render('sysadmin/seriesreport/run.table.html.twig', array(
				'report'=>$this->report,
				'filterStartAt'=>$filterStartAt,
				'filterEndAt'=>$filterEndAt,
				'filterSiteId'=>$filterSiteID,
			));
		} else if ($form->get('output')->getData() == 'csv') {

			$csv = "Label ID, Label Text, Count\n";
			foreach($this->report->getData() as $data) {
				$csv .= $data->getLabelID().',"'.str_replace('"','',$data->getLabelText()).'",'.$data->getData()."\n";
			}

			$response = new Response($csv);
			$response->headers->set('Content-Type', 'text/csv');
			return $response;

		}
	}

}

