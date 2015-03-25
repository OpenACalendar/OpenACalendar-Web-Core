<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\GroupHistoryModel;
use models\EventHistoryModel;
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

		$historyRepositoryBuilder = new HistoryRepositoryBuilder();
		$historyRepositoryBuilder->setSite($app['currentSite']);
		$historyRepositoryBuilder->getHistoryRepositoryBuilderConfig()->setLimit(200);
		
		
		
		return $app['twig']->render('site/history/index.html.twig', array( 
				'historyItems'=>$historyRepositoryBuilder->fetchAll(),
			));
		
	}
		
}


