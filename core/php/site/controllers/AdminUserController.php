<?php

namespace site\controllers;

use repositories\UserPermissionsRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\UserAccountRepository;
use repositories\SiteAccessRequestRepository;
use repositories\builders\SiteAccessRequestRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminUserController {

	protected $parameters = array();
	
	protected function build($username, Request $request, Application $app) {
		$this->parameters = array();

		$repo = new UserAccountRepository();
		$this->parameters['user'] =  $repo->loadByUserName($username);
		if (!$this->parameters['user']) {
			return false;
		}
		
		return true;

	}

	function index($username, Request $request, Application $app) {
		if (!$this->build($username, $request, $app)) {
			$app->abort(404, "User does not exist.");
		}

		$userPermissionRepo = new UserPermissionsRepository($app['extensions']);

		$this->parameters['userpermissions'] = $userPermissionRepo->getPermissionsForUserInSite($this->parameters['user'], $app['currentSite'], false, false)->getPermissions();

		return $app['twig']->render('site/adminuser/index.html.twig', $this->parameters);
	}

}


