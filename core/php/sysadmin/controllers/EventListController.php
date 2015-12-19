<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventListController {
	
	
	function index($siteid, Request $request, Application $app) {
		
		
		$sr = new SiteRepository();
		$site = $sr->loadById($siteid);
		
		if (!$site) {
			die("404");
		}
		
		$erb = new EventRepositoryBuilder();
		$erb->setSite($site);
		$erb->setOrderByStartAt(true);
		$events = $erb->fetchAll();
		
		return $app['twig']->render('sysadmin/eventlist/index.html.twig', array(
				'site'=>$site,
				'events'=>$events,
			));
		
	}
	
	
}


