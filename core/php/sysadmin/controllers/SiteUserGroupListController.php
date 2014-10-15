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
class SiteUserGroupListController {


	function index($siteid, Request $request, Application $app) {


		$sr = new SiteRepository();
		$site = $sr->loadById($siteid);

		if (!$site) {
			die("404");
		}


		$ugrb = new UserGroupRepositoryBuilder();
		$ugrb->setSite($site);
		$userGroups = $ugrb->fetchAll();

		return $app['twig']->render('sysadmin/siteusergrouplist/index.html.twig', array(
				'usergroups'=>$userGroups,
				'site'=>$site,
			));

	}


}


