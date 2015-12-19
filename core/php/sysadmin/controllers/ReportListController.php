<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ReportListController {

	function index(Request $request, Application $app) {

		$reportsSeries = array();
		$reportsValue = array();

		foreach( $app['extensions']->getExtensionsIncludingCore() as $extension) {
			if (method_exists($extension, "getSeriesReports")) {
				$reportsSeries = array_merge($reportsSeries, $extension->getSeriesReports());
			}
			if (method_exists($extension, "getValueReports")) {
				$reportsValue = array_merge($reportsValue, $extension->getValueReports());
			}
		}

		return $app['twig']->render('sysadmin/reportlist/index.html.twig', array(
			'reportsSeries'=>$reportsSeries,
			'reportsValue'=>$reportsValue,
		));
	}

}

