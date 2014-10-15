<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionListController {
	
	
	function index(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/extensionlist/index.html.twig', array(
				'extensions'=>$app['extensions']->getExtensionsIncludingCore(),
			));
		
	}


}


