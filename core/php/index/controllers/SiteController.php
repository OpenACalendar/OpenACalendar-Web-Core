<?php 

namespace index\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\UserAtEventRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */

class SiteController {
	
	protected $parameters = array();
	
	
	protected function build($siteSlug, Request $request, Application $app) {
		$this->parameters = array('user'=>null);

		$repository = new SiteRepository();
		$this->parameters['site'] =  $repository->loadBySlug($siteSlug);
		if (!$this->parameters['site']) {
			return false;
		}
		
		if ($this->parameters['site']->getIsClosedBySysAdmin()) {
			return false;
		}
		
		return true;

	}
	
	
	function eventMyAttendanceJson($siteSlug, $eventSlug, Request $request,Application $app) {
		global $WEBSESSION;
		
		if (!$this->build($siteSlug, $request, $app)) {
			$app->abort(404, "Site does not exist.");
		}
		
		$repo = new \repositories\EventRepository();
		$this->parameters['event'] = $repo->loadBySlug($this->parameters['site'], $eventSlug);
		
		if (!$this->parameters['event']) {
			$app->abort(404, "Event does not exist.");
		}
		
		$uaer = new UserAtEventRepository();
		$userAtEvent = $uaer->loadByUserAndEventOrInstanciate(userGetCurrent(), $this->parameters['event']);

		$data = array();
		
		if (isset($_POST['CSFRToken']) && $_POST['CSFRToken'] == $WEBSESSION->getCSFRToken() && !$this->parameters['event']->isInPast()) {
			
			if (isset($_POST['privacy']) && $_POST['privacy'] == 'public') {
				$userAtEvent->setIsPlanPublic(true);
			} else if (isset($_POST['privacy']) && $_POST['privacy'] == 'private') {
				$userAtEvent->setIsPlanPublic(false);
			}
			
			if (isset($_POST['attending']) && $_POST['attending'] == 'no') {
				$userAtEvent->setIsPlanAttending(false);
				$userAtEvent->setIsPlanMaybeAttending(false);
			} else if (isset($_POST['attending']) && $_POST['attending'] == 'maybe') {
				$userAtEvent->setIsPlanAttending(false);
				$userAtEvent->setIsPlanMaybeAttending(true);
			} else if (isset($_POST['attending']) && $_POST['attending'] == 'yes') {
				$userAtEvent->setIsPlanAttending(true);
				$userAtEvent->setIsPlanMaybeAttending(false);
			}
			
			$uaer->save($userAtEvent);
		}

		$data['attending'] = ($userAtEvent->getIsPlanAttending() ? 'yes' : ($userAtEvent->getIsPlanMaybeAttending()?'maybe':'no'));
		$data['privacy'] = ($userAtEvent->getIsPlanPublic() ? 'public' : 'private');
		$data['inPast'] = $this->parameters['event']->isInPast() ? 1 : 0;
		$data['CSFRToken'] = $WEBSESSION->getCSFRToken();
		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;	
		
		
		
	}
	
	
}

