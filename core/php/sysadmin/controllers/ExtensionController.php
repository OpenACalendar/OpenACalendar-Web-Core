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
class ExtensionController {
	
	
	function index(Request $request, Application $app) {
		global $CONFIG;
		
		$extensions = array();
		
		foreach($CONFIG->extensions as $extensionName) {
			$className = "ExtensionInfo".$extensionName;
			require APP_ROOT_DIR.'/extension/'.$extensionName.'/extensioninfo.php';
			$extensions[$extensionName] = new $className($app);
		}
		
		return $app['twig']->render('sysadmin/extension/index.html.twig', array(
				'extensions'=>$extensions,
			));
		
	}
	
}


