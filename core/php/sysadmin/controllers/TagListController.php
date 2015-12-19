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
class TagListController {
	
	
	function index($siteid, Request $request, Application $app) {
		
		
		$sr = new SiteRepository();
		$site = $sr->loadById($siteid);
		
		if (!$site) {
			die("404");
		}
		
		$trb = new TagRepositoryBuilder();
		$trb->setSite($site);
		$tags = $trb->fetchAll();
		
		return $app['twig']->render('sysadmin/taglist/index.html.twig', array(
				'site'=>$site,
				'tags'=>$tags,
			));
		
	}
	
	
}


