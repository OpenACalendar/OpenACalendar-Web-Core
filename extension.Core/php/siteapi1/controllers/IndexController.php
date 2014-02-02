<?php

namespace siteapi1\controllers;

use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IndexController {
	
	function index(Application $app) {
		
		return $app['twig']->render('siteapi1/index/index.html.twig', array(
			));
		
	}
	
	
}


