<?php


use models\AreaModel;
use models\GroupModel;
use models\SiteModel;
use models\VenueModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionHookRunner {
	
	
	public function beforeVenueSave(VenueModel $venue, UserAccountModel $user = null) {
		global $app, $CONFIG;
		foreach($CONFIG->extensions as $extensionDir) {
			$app['extensions']->getExtensionByDir($extensionDir)->beforeVenueSave($venue, $user);
		}
	}

	public function beforeGroupSave(GroupModel $group, UserAccountModel $user = null) {
		global $app, $CONFIG;
		foreach($CONFIG->extensions as $extensionDir) {
			$app['extensions']->getExtensionByDir($extensionDir)->beforeGroupSave($group, $user);
		}
	}

	public function beforeAreaSave(AreaModel $area, UserAccountModel $user = null) {
		global $app, $CONFIG;
		foreach($CONFIG->extensions as $extensionDir) {
			$app['extensions']->getExtensionByDir($extensionDir)->beforeAreaSave($area, $user);
		}
	}

	public function afterSiteCreate(SiteModel $site, UserAccountModel $owner) {
		global $app, $CONFIG;
		foreach($CONFIG->extensions as $extensionDir) {
			$app['extensions']->getExtensionByDir($extensionDir)->afterSiteCreate($site, $owner);
		}
	}

	public function afterUserAccountCreate(UserAccountModel $user) {
		global $app, $CONFIG;
		foreach($CONFIG->extensions as $extensionDir) {
			$app['extensions']->getExtensionByDir($extensionDir)->afterUserAccountCreate($user);
		}
	}

}


