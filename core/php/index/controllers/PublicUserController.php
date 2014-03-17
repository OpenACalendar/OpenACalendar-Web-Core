<?php 

namespace index\controllers;

use Silex\Application;
use index\forms\CreateForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use repositories\UserAccountRepository;
use Symfony\Component\Form\FormError;
use repositories\builders\EventRepositoryBuilder;
use api1exportbuilders\EventListICalBuilder;

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
	
	
	function index($username, Request $request,Application $app) {
		
		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
		
		
		$erb = new EventRepositoryBuilder();
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
		$ical = new EventListICalBuilder(null, "UTC", $this->parameters['user']->getUserName());
		$ical->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'], false, false, true, false);
		$ical->build();
		return $ical->getResponse();
		
	}

	
	
}