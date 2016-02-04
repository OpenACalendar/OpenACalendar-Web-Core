<?php

namespace org\openacalendar\curatedlists\sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListListController {
	
	
	function index($siteid, Request $request, Application $app) {
		
		
		$sr = new SiteRepository($app);
		$site = $sr->loadById($siteid);
		
		if (!$site) {
			die("404");
		}
		
		$rb = new CuratedListRepositoryBuilder($app);
		$rb->setSite($site);
		$curatedlists = $rb->fetchAll();
		
		return $app['twig']->render('sysadmin/curatedlistlist/index.html.twig', array(
				'site'=>$site,
				'curatedlists'=>$curatedlists,
			));
		
	}
	
	
}


