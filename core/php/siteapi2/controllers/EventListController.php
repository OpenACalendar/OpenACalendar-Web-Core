<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventListController  {
	
	public function listJson (Request $request, Application $app) {
		
		$erb = new EventRepositoryBuilder();
		$erb->setSite($app['currentSite']);
			
		$ourRequest = new \Request($request);
		$erb->setIncludeDeleted($ourRequest->getGetOrPostBoolean('include_deleted', false));
		
		$out = array ('events'=> array());
		
		foreach($erb->fetchAll() as $event) {
			$out['events'][] = array(
				'slug'=>$event->getSlug(),
				'slugForURL'=>$event->getSlugForUrl(),
				'summary'=>$event->getSummary(),
				'summaryDisplay'=>$event->getSummaryDisplay(),
			);
		}
		
		return json_encode($out);
		
		
	}
	
	
	
}

