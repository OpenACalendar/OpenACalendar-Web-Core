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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */

class SiteController {
	
	protected $parameters = array();
	
	
	protected function build($siteSlug, Request $request, Application $app) {
		$this->parameters = array('user'=>null);

		$repository = new SiteRepository($app);
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
		if (!$this->build($siteSlug, $request, $app)) {
			$app->abort(404, "Site does not exist.");
		}
		
		$repo = new \repositories\EventRepository($app);
		$this->parameters['event'] = $repo->loadBySlug($this->parameters['site'], $eventSlug);
		
		if (!$this->parameters['event']) {
			$app->abort(404, "Event does not exist.");
		}
		
		$uaer = new UserAtEventRepository($app);
		$userAtEvent = $uaer->loadByUserAndEventOrInstanciate($app['currentUser'], $this->parameters['event']);

		$data = array();
		
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken() && !$this->parameters['event']->isInPast()) {
			
			if ($request->request->get('privacy') == 'public') {
				$userAtEvent->setIsPlanPublic(true);
			} else if ($request->request->get('privacy') == 'private') {
				$userAtEvent->setIsPlanPublic(false);
			}

            if ($request->request->get('attending') == 'unknown') {
                $userAtEvent->setIsPlanUnknownAttending(true);
            } else if ($request->request->get('attending') == 'no') {
                $userAtEvent->setIsPlanNotAttending(true);
            } else if ($request->request->get('attending') == 'maybe') {
                $userAtEvent->setIsPlanMaybeAttending(true);
            } else if ($request->request->get('attending') == 'yes') {
                $userAtEvent->setIsPlanAttending(true);
            }
			
			$uaer->save($userAtEvent);
		}

        if ($userAtEvent->getIsPlanAttending()) {
            $data['attending'] = 'yes';
        } else if ($userAtEvent->getIsPlanMaybeAttending()) {
            $data['attending'] = 'maybe';
        } else if ($userAtEvent->getIsPlanNotAttending()) {
            $data['attending'] = 'no';
        } else {
            $data['attending'] = 'unknown';
        }
		$data['privacy'] = ($userAtEvent->getIsPlanPublic() ? 'public' : 'private');
		$data['inPast'] = $this->parameters['event']->isInPast() ? 1 : 0;
		$data['CSFRToken'] = $app['websession']->getCSFRToken();
		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;	
		
		
		
	}
	
	
}

