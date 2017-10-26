<?php

namespace sysadmin\controllers;

use BaseValueReport;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use sysadmin\forms\RunValueReportForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ValueReportController {

    /** @var  BaseValueReport */
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



		$form = $app['form.factory']->create(new RunValueReportForm($app, $this->report));

		return $app['twig']->render('sysadmin/valuereport/index.html.twig', array(
			'report'=>$this->report,
			'form'=>$form->createView(),
		));
	}

	function run($extid, $reportid, Request $request, Application $app) {
		if (!$this->build($extid, $reportid, $app)) {
			die("NO");
		}

		$form = $app['form.factory']->create(new RunValueReportForm($app, $this->report));
		$form->handleRequest($request);

		$filterStartAt = $this->report->getHasFilterTime() ? $form->get('start_at')->getData() : null;
		$filterEndAt = $this->report->getHasFilterTime() ? $form->get('end_at')->getData() : null;
		$filterSiteID = $this->report->getHasFilterSite() ? $form->get('site_id')->getData() : null;

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

