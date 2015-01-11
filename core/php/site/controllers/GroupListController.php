<?php

namespace site\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\GroupModel;
use repositories\GroupRepository;
use repositories\builders\GroupRepositoryBuilder;
use repositories\builders\filterparams\GroupFilterParams;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupListController {
	
	function index(Application $app) {
		
		
		$params = new GroupFilterParams();
		$params->set($_GET);
		$params->getGroupRepositoryBuilder()->setSite($app['currentSite']);
		$params->getGroupRepositoryBuilder()->setIncludeDeleted(false);
		$params->getGroupRepositoryBuilder()->setIncludeMediasSlugs(true);
		
		$groups = $params->getGroupRepositoryBuilder()->fetchAll();
		
		return $app['twig']->render('site/grouplist/index.html.twig', array(
				'groups'=>$groups,
				'groupListFilterParams'=>$params,
			));
		
	}
	
}


