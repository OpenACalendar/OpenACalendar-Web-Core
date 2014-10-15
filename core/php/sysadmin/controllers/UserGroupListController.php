<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\builders\UserGroupRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserGroupListController {


	function index(Request $request, Application $app) {




		$ugrb = new UserGroupRepositoryBuilder();
		$ugrb->setIndexOnly(true);
		$userGroups = $ugrb->fetchAll();

		return $app['twig']->render('sysadmin/usergrouplist/index.html.twig', array(
				'usergroups'=>$userGroups,
			));

	}


}


