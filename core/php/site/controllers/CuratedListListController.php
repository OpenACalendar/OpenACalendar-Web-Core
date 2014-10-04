<?php

namespace site\controllers;

use repositories\builders\filterparams\CuratedListFilterParams;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\CuratedListModel;
use repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class CuratedListListController {

	
	function index(Application $app) {

		$grb = new CuratedListRepositoryBuilder();
		$grb->setSite($app['currentSite']);
		$grb->setIncludeDeleted(false);

		$lists = $grb->fetchAll();

		return $app['twig']->render('site/curatedlistlist/index.html.twig', array(
				'curatedlists'=>$lists,
			));
				
	}

}

