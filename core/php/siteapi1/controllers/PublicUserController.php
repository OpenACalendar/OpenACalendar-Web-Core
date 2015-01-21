<?php 

namespace siteapi1\controllers;

use api1exportbuilders\EventListCSVBuilder;
use Silex\Application;
use index\forms\CreateForm;
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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class PublicUserController {
	
	protected $parameters = array();
	
	
	protected function build($username, Request $request, Application $app) {
		$this->parameters = array('user'=>null);

		$repository = new UserAccountRepository();
		$this->parameters['user'] =  $repository->loadByUserName($username);
		if (!$this->parameters['user']) {
			return false;
		}
		
		if ($this->parameters['user']->getIsClosedBySysAdmin()) {
			return false;
		}
		
		return true;

	}
	
	function ical($username, Request $request,Application $app) {
		
		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
				
		// TODO should we be passing a better timeZone here?
		$ical = new EventListICalBuilder(null, "UTC", $this->parameters['user']->getUserName());
		$ical->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$ical->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'], false, false, true, false);
		$ical->build();
		return $ical->getResponse();
		
	}
	
	function json($username, Request $request,Application $app) {


		$ourRequest = new \Request($request);

		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
		
		$json = new EventListJSONBuilder($app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$json->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'], false, false, true, false);
		$json->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$json->build();
		return $json->getResponse();
			
	}
	
	function jsonp($username, Request $request,Application $app) {


		$ourRequest = new \Request($request);

		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
		
		$jsonp = new EventListJSONPBuilder($app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$jsonp->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'], false, false, true, false);
		$jsonp->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
			
	}

	function csv($username, Request $request,Application $app) {


		$ourRequest = new \Request($request);

		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}

		$csv = new EventListCSVBuilder($app['currentSite'], $app['currentTimeZone']);
		$csv->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$csv->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'], false, false, true, false);
		$csv->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$csv->build();
		return $csv->getResponse();

	}
	
}

