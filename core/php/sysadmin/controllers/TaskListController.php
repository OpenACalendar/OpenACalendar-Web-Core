<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\SiteRepository;
use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TaskListController {

	function index(Request $request, Application $app) {

		$tasks = array();
		foreach($app['extensions']->getExtensionsIncludingCore() as $ext) {
			$tasks = array_merge($tasks, $ext->getTasks());
		}

		return $app['twig']->render('sysadmin/tasklist/index.html.twig', array(
			'tasks'=>$tasks,
		));

	}


}


