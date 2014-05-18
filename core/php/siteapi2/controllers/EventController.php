<?php

namespace siteapi2\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\EventRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventController {
	
	/** @var \models\EventModel **/
	protected $event;

	protected function build($slug, Request $request, Application $app) {

		
		
		$repo = new EventRepository();
		$this->event = $repo->loadBySlug($app['currentSite'], $slug);
		if (!$this->event) {
			return false;
		}
		
		return true;
		
		
	}
	


	public function infoJson ($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Does not exist.");
		}
		
		$out = array(
			'event'=>array(
				'slug'=>$this->event->getSlug(),
				'slugForURL'=>$this->event->getSlugForUrl(),
				'summary'=>$this->event->getSummary(),
				'summaryDisplay'=>$this->event->getSummaryDisplay(),
			),
		);
		
		return json_encode($out);
	}
	
	
}

