<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\EventRepository;
use repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class HistoryController {
	
	
	function index(Request $request, Application $app) {
		
		$hrb = new HistoryRepositoryBuilder();
		
		
		return $app['twig']->render('sysadmin/history/index.html.twig', array(
				'historyItems'=>$hrb->fetchAll(),
			));
		
	}
	
}


