<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\builders\MediaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaListController {
	
	
	function index($siteid, Request $request, Application $app) {
		
		
		$sr = new SiteRepository();
		$site = $sr->loadById($siteid);
		
		if (!$site) {
			die("404");
		}
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(true);
		$mrb->setSite($site);
		$medias = $mrb->fetchAll();
		
		return $app['twig']->render('sysadmin/medialist/index.html.twig', array(
				'site'=>$site,
				'medias'=>$medias,
			));
		
	}
	
	
}


