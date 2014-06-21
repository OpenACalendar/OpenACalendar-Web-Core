<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\builders\TagRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class TagListController {
	
	
	function index(Application $app) {
		
		$trb = new TagRepositoryBuilder();
		$trb->setSite($app['currentSite']);
		$tags = $trb->fetchAll();
		
		return $app['twig']->render('site/taglist/index.html.twig', array(	
				'tags'=>$tags,
			));
	}
	
		
	
}


