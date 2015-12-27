<?php

namespace org\openacalendar\facebook;

use appconfiguration\AppConfigurationDefinition;

/**
 *
 * @package org.openacalendar.facebook
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionFacebook extends \BaseExtension {
	
	public function getId() {
		return 'org.openacalendar.facebook';
	}
	
	public function getTitle() {
		return "Facebook Integration";
	}
	
	public function getDescription() {
		return "Facebook Integration";
	}
	
	public function getAppConfigurationDefinitions() {
		return array(
			new AppConfigurationDefinition($this->getId(),'app_id','text',true),
			new AppConfigurationDefinition($this->getId(),'app_secret','password',true),
			new AppConfigurationDefinition($this->getId(),'user_token','password',false),
		);
	}
	
	
	public function getImportHandlers() {
		return array(
			new ImportFacebookHandler(),
		);
	}


	public function getSysAdminLinks() {
		return array(
			new \SysAdminLink("Setup Facebook Access",'/sysadmin/facebookuser')
		);
	}
	
}
