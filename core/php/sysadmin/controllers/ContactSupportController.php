<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\EventRepository;
use repositories\UserAccountRepository;
use repositories\ContactSupportRepository;
use repositories\VenueRepository;
use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\UserAccountRepositoryBuilder;
use sysadmin\forms\ActionForm;
use sysadmin\ActionParser;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ContactSupportController {
	
		
	protected $parameters = array();
	
	protected function build($id, Request $request, Application $app) {
		$this->parameters = array('user'=>null);

		$csr = new ContactSupportRepository();
		$this->parameters['contactsupport'] = $csr->loadById($id);
		if (!$this->parameters['contactsupport']) {
			$app->abort(404);
		}
		
		if ($this->parameters['contactsupport']->getUserAccountId()) {
			$ur = new UserAccountRepository;
			$this->parameters['user'] = $ur->loadByID($this->parameters['contactsupport']->getUserAccountId());
		}
		
		
	}
	
	function index($id, Request $request, Application $app) {

		$this->build($id, $request, $app);
		
			
		
		return $app['twig']->render('sysadmin/contactsupport/index.html.twig', $this->parameters);		
	
	}
	
	
}


