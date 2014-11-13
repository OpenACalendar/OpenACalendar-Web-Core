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
				'description'=>$this->event->getDescription(),
				'url'=>$this->event->getUrl(),
				'ticket_url'=>$this->event->getTicketUrl(),
			),
		);
		
		return json_encode($out);
	}


	public function postInfoJson ($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Does not exist.");
		}

		$ourRequest = new \Request($request);

		$edits = false;
		if ($ourRequest->hasGetOrPost('summary') && $this->event->setSummaryIfDifferent($ourRequest->getGetOrPostString('summary', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('description') && $this->event->setDescriptionIfDifferent($ourRequest->getGetOrPostString('description', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('url') && $this->event->setUrlIfDifferent($ourRequest->getGetOrPostString('url', ''))) {
			$edits = true;
		}
		if ($ourRequest->hasGetOrPost('ticket_url') && $this->event->setTicketUrlIfDifferent($ourRequest->getGetOrPostString('ticket_url', ''))) {
			$edits = true;
		}

		if ($edits) {
			$repo = new EventRepository();
			$repo->edit($this->event, $app['apiUser']);
			$out = array(
				'edited'=>true,
			);
		} else {
			$out = array(
				'edited'=>false,
			);
		}

		return json_encode($out);
	}
	
}

