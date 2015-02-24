<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use sysadmin\forms\RunValueReportForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ValueReportController {

	protected $report;

	function build($extid, $reportId, Application $app) {
		$extension = $app['extensions']->getExtensionById($extid);
		if (!$extension) return false;
		foreach($extension->getValueReports() as $report) {
			if ($report->getReportID() == $reportId) {
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



		$form = $app['form.factory']->create(new RunValueReportForm($this->report));

		return $app['twig']->render('sysadmin/valuereport/index.html.twig', array(
			'report'=>$this->report,
			'form'=>$form->createView(),
		));
	}

	function run($extid, $reportid, Request $request, Application $app) {
		if (!$this->build($extid, $reportid, $app)) {
			die("NO");
		}

		$form = $app['form.factory']->create(new RunValueReportForm($this->report));
		$form->bind($request);

		$filterStartAt = $form->get('start_at')->getData();
		$filterEndAt = $form->get('end_at')->getData();
		$filterSiteID = $form->get('site_id')->getData();

		$this->report->setFilterTime($filterStartAt,$filterEndAt);
		$this->report->setFilterSiteId($filterSiteID);

		$this->report->run();

		if ($form->get('output')->getData() == 'htmlTable') {
			return $app['twig']->render('sysadmin/valuereport/run.table.html.twig', array(
				'report'=>$this->report,
				'filterStartAt'=>$filterStartAt,
				'filterEndAt'=>$filterEndAt,
				'filterSiteId'=>$filterSiteID,
			));

		}
	}

}

