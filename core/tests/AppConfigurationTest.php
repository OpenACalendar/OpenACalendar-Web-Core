<?php

use appconfiguration\AppConfigurationManager;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AppConfigurationTest extends \PHPUnit_Framework_TestCase {
	
	
	function testGetDefault() {
		global $CONFIG;
		$DB = getNewTestDB();
		$appConfigManager = new AppConfigurationManager($DB, $CONFIG);
		$def = new \appconfiguration\AppConfigurationDefinition('core','key','text',true);
		$this->assertEquals('yaks',$appConfigManager->getValue($def,'yaks'));
	}
	
	function testSetGet() {
		global $CONFIG;
		$DB = getNewTestDB();
		$appConfigManager = new AppConfigurationManager($DB, $CONFIG);
		$def = new \appconfiguration\AppConfigurationDefinition('core','key','text',true);
		
		$appConfigManager->setValue($def, 'moreyaks');
		$this->assertEquals('moreyaks',$appConfigManager->getValue($def,'yaks'));
	}
	
	function testSetUpdateGet() {
		global $CONFIG;
		$DB = getNewTestDB();
		$appConfigManager = new AppConfigurationManager($DB, $CONFIG);
		$def = new \appconfiguration\AppConfigurationDefinition('core','key','text',true);
		
		$appConfigManager->setValue($def, 'moreyaks');
		$appConfigManager->setValue($def, 'muchyaks');
		$this->assertEquals('muchyaks',$appConfigManager->getValue($def,'yaks'));
	}
	
	
}

