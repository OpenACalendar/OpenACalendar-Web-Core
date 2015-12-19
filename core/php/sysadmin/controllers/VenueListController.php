<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\builders\VenueRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueListController {
	
	
	function index($siteid, Request $request, Application $app) {
		
		
		$sr = new SiteRepository();
		$site = $sr->loadById($siteid);
		
		if (!$site) {
			die("404");
		}
		
		$vrb = new VenueRepositoryBuilder();
		$vrb->setSite($site);
		$venues = $vrb->fetchAll();
		
		return $app['twig']->render('sysadmin/venuelist/index.html.twig', array(
				'site'=>$site,
				'venues'=>$venues,
			));
		
	}
	
	
}


