<?php

namespace site\controllers;

use Silex\Application;
use repositories\builders\filterparams\EventFilterParams;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventListController {
	
	function index(Application $app) {
		
		
		$params = new EventFilterParams($app, null, $app['currentSite']);
		$params->getEventRepositoryBuilder()->setIncludeAreaInformation(true);
		$params->getEventRepositoryBuilder()->setIncludeVenueInformation(true);
		$params->getEventRepositoryBuilder()->setIncludeMediasSlugs(true);
		$params->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
		$params->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));
		if ($app['currentUser']) {
			$params->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
		}
        $params->set($_GET);
		
		$events = $params->getEventRepositoryBuilder()->fetchAll();
		
		return $app['twig']->render('site/eventlist/index.html.twig', array(
				'eventListFilterParams'=>$params,
				'events'=>$events,
			));
		
	}
	
	
	
	function calendarNow(Application $app) {

        $params = new EventFilterParams($app, null, $app['currentSite']);
        $params->setHasDateControls(false);
        //$params->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
        //$params->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));
        $params->set($_GET);

		$cal = new \RenderCalendar($app, $params);
		if ($app['currentUser']) {
			$cal->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
		}
		$cal->byDate(\TimeSource::getDateTime(), 31, true);
		
		list($prevYear,$prevMonth,$nextYear,$nextMonth) = $cal->getPrevNextLinksByMonth();

		return $app['twig']->render('/site/calendarPage.html.twig', array(
				'calendar'=>$cal,
				'prevYear' => $prevYear,
				'prevMonth' => $prevMonth,
				'nextYear' => $nextYear,
				'nextMonth' => $nextMonth,
				'pageTitle' => 'Calendar',
                'eventListFilterParams' => $params,
				'showCurrentUserOptions' => true,
			));
	}
	
	function calendar($year, $month, Application $app) {

        $params = new EventFilterParams($app, null, $app['currentSite']);
        $params->setHasDateControls(false);
        //$params->setHasTagControl($app['currentSiteFeatures']->has('org.openacalendar','Tag'));
        //$params->setHasGroupControl($app['currentSiteFeatures']->has('org.openacalendar','Group'));
        $params->set($_GET);

        $cal = new \RenderCalendar($app, $params);
		$cal->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$cal->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if ($app['currentUser']) {
			$cal->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
		}
		$cal->byMonth($year, $month, true);
		
		list($prevYear,$prevMonth,$nextYear,$nextMonth) = $cal->getPrevNextLinksByMonth();

		return $app['twig']->render('/site/calendarPage.html.twig', array(
				'calendar'=>$cal,
				'prevYear' => $prevYear,
				'prevMonth' => $prevMonth,
				'nextYear' => $nextYear,
				'nextMonth' => $nextMonth,
				'pageTitle' => 'Calendar',
                'eventListFilterParams' => $params,
				'showCurrentUserOptions' => true,
			));
	}
	
}


