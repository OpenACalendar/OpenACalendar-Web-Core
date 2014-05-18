<?php

namespace siteapi2\controllers;

use Silex\Application;
use repositories\API2ApplicationRepository;
use repositories\API2ApplicationRequestTokenRepository;
use repositories\API2ApplicationUserAccessTokenRepository;
use repositories\API2ApplicationUserTokenRepository;
use models\API2ApplicationRequestTokenModel;

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
		
		return "TODO";
		
	}
	
	
	public function currentUserOnSiteJson(Application $app) {

		return json_encode(array(
				'success'=>true,
				'user'=>array(
					'username'=>$app['apiUser']->getUserName(),
				),
				'permissions'=>array(
					'is_write_user_actions'=>$app['apiUserIsWriteUserActions'],
					'is_write_user_profile'=>$app['apiUserIsWriteUserProfile'],
					'is_write_calendar'=>$app['apiUserIsWriteCalendar'],
				),
				'site'=>array(
					'title'=>$app['currentSite']->getTitle(),
				),
			));
	}
	
}


