<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\builders\UserAccountRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserListController {
	
	
	function index(Request $request, Application $app) {
		
		$erb = new UserAccountRepositoryBuilder();
		$users = $erb->fetchAll();
		
		
		return $app['twig']->render('sysadmin/userlist/index.html.twig', array(
				'users'=>$users,
			));
		
	}
	
	
}


