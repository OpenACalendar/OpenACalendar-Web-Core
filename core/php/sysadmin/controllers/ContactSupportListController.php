<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\builders\ContactSupportRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ContactSupportListController {
	
	
	function index(Request $request, Application $app) {
		
		
		$csrb = new ContactSupportRepositoryBuilder();
		
		return $app['twig']->render('sysadmin/contactsupportlist/index.html.twig', array(
				'contactsupports'=>$csrb->fetchAll(),
			));
	}
		
		

}

