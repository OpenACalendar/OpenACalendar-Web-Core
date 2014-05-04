<?php

use \ConfigCheck;
use \Config;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ConfigCheckTest extends \PHPUnit_Framework_TestCase {
	
	
	
	function testMultiSiteOK() {
		$config = new Config();
		$config->isSingleSiteMode = false;
		$config->webIndexDomain = 'www.test.com';
		$config->webSiteDomain = 'test.com';
		
		$configCheck = new ConfigCheck($config);
		
		$this->assertEquals(0, count($configCheck->getErrors('webIndexDomain')));
		$this->assertEquals(0, count($configCheck->getErrors('webSiteDomain')));
		
	}
	
	function testSingleSiteOK() {
		$config = new Config();
		$config->isSingleSiteMode = true;
		$config->webIndexDomain = 'test.com';
		$config->webSiteDomain = 'test.com';
		
		$configCheck = new ConfigCheck($config);
		
		$this->assertEquals(0, count($configCheck->getErrors('webIndexDomain')));
		$this->assertEquals(0, count($configCheck->getErrors('webSiteDomain')));
		
	}
	
	function testSingleSiteDifferentDomains() {
		$config = new Config();
		$config->isSingleSiteMode = true;
		$config->webIndexDomain = 'www.test.com';
		$config->webSiteDomain = 'test.com';
		
		$configCheck = new ConfigCheck($config);
		
		$this->assertEquals(1, count($configCheck->getErrors('webIndexDomain')));
		$this->assertEquals(1, count($configCheck->getErrors('webSiteDomain')));
		
	}
	
	
	
	function testEmailsOk() {
		$config = new Config();
		$config->emailFrom = 'test@test.com';
		$config->contactEmail = 'test@test.com';
		
		$configCheck = new ConfigCheck($config);
		
		$this->assertEquals(0, count($configCheck->getErrors('emailFrom')));
		$this->assertEquals(0, count($configCheck->getErrors('contactEmail')));
	}
	
	
	function testEmailsBad() {
		$config = new Config();
		$config->emailFrom = 'test@test';
		$config->contactEmail = 'testtest.com';
		
		$configCheck = new ConfigCheck($config);
		
		$this->assertEquals(1, count($configCheck->getErrors('emailFrom')));
		$this->assertEquals(1, count($configCheck->getErrors('contactEmail')));
	}
	
	function testLogOK() {
		$config = new Config();
		$config->logFile= '/tmp/log.txt';
		$config->logToStdError = true;
		
		$configCheck = new ConfigCheck($config);
		
		$this->assertEquals(0, count($configCheck->getErrors('logFile')));
		$this->assertEquals(0, count($configCheck->getErrors('logToStdError')));
	}
	
	function testLogBad() {
		$config = new Config();
		$config->logFile= null;
		$config->logToStdError = true;
		
		$configCheck = new ConfigCheck($config);
		
		$this->assertEquals(0, count($configCheck->getErrors('logFile')));
		$this->assertEquals(1, count($configCheck->getErrors('logToStdError')));
	}
}

