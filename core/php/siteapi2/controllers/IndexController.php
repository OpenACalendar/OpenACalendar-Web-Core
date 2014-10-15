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
					'is_feature_map'=>$app['currentSite']->getIsFeatureMap(),
					'is_feature_importer'=>$app['currentSite']->getIsFeatureImporter(),
					'is_feature_curated_list'=>$app['currentSite']->getIsFeatureCuratedList(),
					'is_feature_virtual_events'=>$app['currentSite']->getIsFeatureVirtualEvents(),
					'is_feature_physical_events'=>$app['currentSite']->getIsFeaturePhysicalEvents(),
					'is_feature_group'=>$app['currentSite']->getIsFeatureGroup(),
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


