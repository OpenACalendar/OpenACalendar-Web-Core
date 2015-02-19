<?php

namespace siteapi1\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use api1exportbuilders\HistoryListATOMBuilder;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class HistoryListController {
	
	
	function atom(Request $request, Application $app) {
		
		$atom = new HistoryListATOMBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->build();
		return $atom->getResponse();
	}	
	

	
}


