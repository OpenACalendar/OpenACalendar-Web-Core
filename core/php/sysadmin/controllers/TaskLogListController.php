<?php

namespace sysadmin\controllers;

use repositories\builders\TaskLogRepositoryBuilder;
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
class TaskLogListController {

	function index(Request $request, Application $app) {


		$tllrb = new TaskLogRepositoryBuilder($app);
		$tllrb->setLimit(500);



		return $app['twig']->render('sysadmin/taskloglist/index.html.twig', array(
			'tasklogs'=>$tllrb->fetchAll(),
		));

	}


}


