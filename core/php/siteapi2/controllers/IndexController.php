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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IndexController {
	
	function index(Application $app) {
		
		return "TODO";
		
	}
	
	
	public function currentUserOnSiteJson(Application $app) {
		$data = array(
				'success'=>true,
				'permissions'=>array(
					'is_editor'=>$app['apiUserToken']->getIsEditor(),
				),
				'site'=>array(
					'title'=>$app['currentSite']->getTitle(),
					'description_text'=>$app['currentSite']->getDescriptionText(),
					'footer_text'=>$app['currentSite']->getFooterText(),
					'timezones'=>$app['currentSite']->getCachedTimezonesAsList(),
					'is_feature_map'=>$app['currentSiteFeatures']->has('org.openacalendar','Map'),
					'is_feature_importer'=>$app['currentSiteFeatures']->has('org.openacalendar','Importer'),
					'is_feature_curated_list'=>$app['currentSiteFeatures']->has('org.openacalendar.curatedlists','CuratedList'),
					'is_feature_virtual_events'=>$app['currentSiteFeatures']->has('org.openacalendar','VirtualEvents'),
					'is_feature_physical_events'=>$app['currentSiteFeatures']->has('org.openacalendar','PhysicalEvents'),
					'is_feature_group'=>$app['currentSiteFeatures']->has('org.openacalendar','Group'),
				),
			);
		
		if ($app['apiUser']) {
			$data['user'] = array(
					'username'=>$app['apiUser']->getUserName(),
				);
		}
		
		return json_encode($data);
	}
	
}


