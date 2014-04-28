<?php

namespace site\controllers;

use Silex\Application;
use repositories\builders\filterparams\EventFilterParams;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventListController {
	
	function index(Application $app) {
		
		
		$params = new EventFilterParams();
		$params->set($_GET);
		$params->getEventRepositoryBuilder()->setSite($app['currentSite']);
		if (userGetCurrent()) {
			$params->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
		}
		
		$events = $params->getEventRepositoryBuilder()->fetchAll();
		
		return $app['twig']->render('site/eventlist/index.html.twig', array(
				'eventListFilterParams'=>$params,
				'events'=>$events,
			));
		
	}
	
	
	
	function calendarNow(Application $app) {
		$cal = new \RenderCalendar();
		$cal->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$cal->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if (userGetCurrent()) {
			$cal->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
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
				'showCurrentUserOptions' => true,
			));
	}
	
	function calendar($year, $month, Application $app) {
		
		$cal = new \RenderCalendar();
		$cal->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$cal->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if (userGetCurrent()) {
			$cal->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
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
				'showCurrentUserOptions' => true,
			));
	}
	
}


