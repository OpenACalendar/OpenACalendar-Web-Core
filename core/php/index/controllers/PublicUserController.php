<?php 

namespace index\controllers;

use api1exportbuilders\ICalEventIdConfig;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use repositories\UserAccountRepository;
use Symfony\Component\Form\FormError;
use repositories\builders\EventRepositoryBuilder;
use api1exportbuilders\EventListICalBuilder;
use api1exportbuilders\EventListJSONBuilder;
use api1exportbuilders\EventListJSONPBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class PublicUserController {
	
	protected $parameters = array();
	
	
	protected function build($username, Request $request, Application $app) {
		$this->parameters = array('user'=>null);

		$repository = new UserAccountRepository($app);
		$this->parameters['user'] =  $repository->loadByUserName($username);
		if (!$this->parameters['user']) {
			return false;
		}
		
		if ($this->parameters['user']->getIsClosedBySysAdmin()) {
			return false;
		}
		
		return true;

	}
	
	
	function index($username, Request $request,Application $app) {
		
		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
		
		
		$erb = new EventRepositoryBuilder($app);
		$erb->setAfterNow();
		$erb->setUserAccount($this->parameters['user'], false, false, true, false);
		$this->parameters['events'] = $erb->fetchAll();
				
		return $app['twig']->render('index/publicuser/index.html.twig', $this->parameters);
		
	}
	
	
	
	function ical($username, Request $request,Application $app) {
		
		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
				
		// TODO should we be passing a better timeZone here?
		$ical = new EventListICalBuilder($app, null, "UTC", $this->parameters['user']->getUserName(), new ICalEventIdConfig($request->get('eventidconfig'), $request->server->all()));
		$ical->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'], false, false, true, false);
		$ical->build();
		return $ical->getResponse();
		
	}
	
	function json($username, Request $request,Application $app) {
		
		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
		
		$json = new EventListJSONBuilder($app, null, $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'], false, false, true, false);
		$json->build();
		return $json->getResponse();
			
	}
	
	function jsonp($username, Request $request,Application $app) {
		
		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
		
		$jsonp = new EventListJSONPBuilder($app, null, $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'], false, false, true, false);
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
			
	}
	
}

