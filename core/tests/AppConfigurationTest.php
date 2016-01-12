<?php

use appconfiguration\AppConfigurationManager;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AppConfigurationTest extends \BaseAppWithDBTest {
	
	
	function testGetDefault() {
		$appConfigManager = new AppConfigurationManager($this->app['db'], $this->app['config']);
		$def = new \appconfiguration\AppConfigurationDefinition('core','key','text',true);
		$this->assertEquals('yaks',$appConfigManager->getValue($def,'yaks'));
	}
	
	function testSetGet() {
		$appConfigManager = new AppConfigurationManager($this->app['db'], $this->app['config']);
		$def = new \appconfiguration\AppConfigurationDefinition('core','key','text',true);
		
		$appConfigManager->setValue($def, 'moreyaks');
		$this->assertEquals('moreyaks',$appConfigManager->getValue($def,'yaks'));
	}
	
	function testSetUpdateGet() {
		$appConfigManager = new AppConfigurationManager($this->app['db'], $this->app['config']);
		$def = new \appconfiguration\AppConfigurationDefinition('core','key','text',true);
		
		$appConfigManager->setValue($def, 'moreyaks');
		$appConfigManager->setValue($def, 'muchyaks');
		$this->assertEquals('muchyaks',$appConfigManager->getValue($def,'yaks'));
	}
	
	
}

