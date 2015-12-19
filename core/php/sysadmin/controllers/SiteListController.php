<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\builders\SiteRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteListController {
	
	
	function index(Request $request, Application $app) {
		
		
		$erb = new SiteRepositoryBuilder();
		$sites = $erb->fetchAll();
		
		return $app['twig']->render('sysadmin/sitelist/index.html.twig', array(
				'sites'=>$sites,
			));
		
	}
	
	
}


