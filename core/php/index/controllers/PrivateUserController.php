<?php 

namespace index\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use repositories\UserAccountRepository;
use repositories\UserAccountPrivateFeedKeyRepository;
use Symfony\Component\Form\FormError;
use api1exportbuilders\EventListICalBuilder;
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class PrivateUserController {
	
	protected $parameters = array();
	
	
	protected function build($username, $accesskey, Request $request, Application $app) {
		$this->parameters = array('user'=>null);

		$repository = new UserAccountRepository();
		$this->parameters['user'] =  $repository->loadByUserName($username);
		if (!$this->parameters['user']) {
			return false;
		}
		
		if ($this->parameters['user']->getIsClosedBySysAdmin()) {
			return false;
		}
		
		$repository = new UserAccountPrivateFeedKeyRepository();
		$this->parameters['feedKey'] =  $repository->loadByUserAccountIDAndAccessKey($this->parameters['user']->getId(), $accesskey);
		if (!$this->parameters['feedKey']) {
			return false;
		}
		
		
		return true;

	}
	
	
	function icalAttendingWatching($username, $accesskey, Request $request,Application $app) {
		
		if (!$this->build($username, $accesskey, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
				
		// TODO should we be passing a better timeZone here?
		$ical = new EventListICalBuilder($app, null, "UTC", $this->parameters['user']->getUserName());
		$ical->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'],false,true,true,true);
		$ical->build();
		return $ical->getResponse();
		
	}
	
	function icalAttending($username, $accesskey, Request $request,Application $app) {
		
		if (!$this->build($username, $accesskey, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}
				
		// TODO should we be passing a better timeZone here?
		$ical = new EventListICalBuilder($app, null, "UTC", $this->parameters['user']->getUserName());
		$ical->getEventRepositoryBuilder()->setUserAccount($this->parameters['user'],false,true,true,false);
		$ical->build();
		return $ical->getResponse();
		
	}

	
	
}