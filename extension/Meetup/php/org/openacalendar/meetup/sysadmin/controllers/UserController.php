<?php

namespace org\openacalendar\meetup\sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @package org.openacalendar.meetup
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UserController {
		
	function index(Request $request, Application $app) {		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.meetup');
		$appKey = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_key'));
		
		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken() 
			&& $request->request->get('submitted') == 'appdetails') {

			$appKey = $request->request->get('app_key');
			
			$app['appconfig']->setValue($extension->getAppConfigurationDefinition('app_key'), $appKey);
			
		}
		return $app['twig']->render('meetup/sysadmin/user/index.html.twig', array(
			'app_key'=>($appKey?substr($appKey,0,3)."XXXXXXX":''),
		));
	}
	
}

