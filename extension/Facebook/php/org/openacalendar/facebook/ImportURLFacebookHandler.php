<?php

namespace org\openacalendar\facebook;

use import\ImportURLHandlerBase;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;

/**
 *
 * @package org.openacalendar.facebook
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLFacebookHandler extends ImportURLHandlerBase {
	
	protected $eventId;


	
	
	public function canHandle() {
		global $app;
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.facebook');
		$appID = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_id'));
		$appSecret = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_secret'));
		$userToken = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('user_token'));
		
		$urlBits = parse_url($this->importURLRun->getRealURL());
		
		if ($urlBits['host']== 'facebook.com' || $urlBits['host']== 'www.facebook.com')  {
			
			$bits =  explode("/",$urlBits['path']);
			
			if ($bits[1] == 'events' && $bits[2] && $appID && $appSecret && $userToken) {
				$this->eventId = $bits[2];
				return true;
			}
			
		}
		
		return false;
	}

	public function handle() {
		global $app;
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.facebook');
		$appID = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_id'));
		$appSecret = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('app_secret'));
		$userToken = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('user_token'));
		
		FacebookSession::setDefaultApplication($appID, $appSecret);
		
		if ($this->eventId && $appID && $appSecret && $userToken) {
		
			$this->getEvent($this->eventId);
		
		}
		
	}	
	
	protected function getEvent($id) {
		global $app;
		
		$extension = $app['extensions']->getExtensionById('org.openacalendar.facebook');
		$userToken = $app['appconfig']->getValue($extension->getAppConfigurationDefinition('user_token'));
		$session = new FacebookSession($userToken);
		
		$url = '/' . strval($this->eventId);
		$request = new FacebookRequest($session, 'GET', $url);
		$response = $request->execute();
		$graphObject = $response->getGraphObject();
		
		var_dump($graphObject->getProperty('name'));
		var_dump($graphObject->getProperty('description'));
		var_dump($graphObject->getProperty('start_time'));
		var_dump($graphObject->getProperty('end_time'));
		var_dump($graphObject->getProperty('timezone'));
		var_dump($graphObject->getProperty('ticket_uri'));
		
		die();
		
	}
}

