<?php

namespace org\openacalendar\facebook\sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @package org.openacalendar.facebook
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UserController {
		
	function index(Request $request, Application $app) {
		
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.facebook');
		$appID = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_id'));
		$appSecret = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_secret'));
		$userToken = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('user_token'));

		// TODO CSFR Tokens
		if ($request->get('newfacebookaccesstoken')) {

			// Convert this short lived into long lived
			$url = "https://graph.facebook.com/oauth/access_token?client_id=".$appID.
					"&client_secret=".$appSecret.
					"&grant_type=fb_exchange_token&fb_exchange_token=".$request->get('newfacebookaccesstoken');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			$result = curl_exec($ch);
			curl_close($ch); 

			parse_str($result, $resultArray);
			if (!$resultArray['access_token']) {
				die("Did not get long-lived access token, problem.");
			}
			
			$userToken = $resultArray['access_token'];
			$app['appconfig']->setValue($extension->getAppConfigurationDefinition('user_token'), $userToken);
			
		}
		
		
		return $app['twig']->render('facebook/sysadmin/user/index.html.twig', array(
			'app_id'=>$appID,
			'app_secret'=>($appSecret?substr($appSecret,0,5)."XXXXXXX":''),
			'user_token'=>($userToken?substr($userToken,0,5)."XXXXXXX":''),
		));
	}
	
}

