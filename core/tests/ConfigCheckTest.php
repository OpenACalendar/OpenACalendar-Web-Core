<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ConfigCheckTest extends \BaseAppTest {
	
	
	
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


    function dataForTestLogLevelGood() {
        return array(
            array('error'),
            array('emergency'),
            array('alert'),
            array('critical'),
            array('warning'),
            array('notice'),
            array('info'),
            array('debug'),
        );
    }

    /**
     * @dataProvider dataForTestLogLevelGood
     */
    function testLogLevelGood($set) {
        $config = new Config();
        $config->logFile = '/tmp/test.log';
        $config->logLevel = $set;

        $configCheck = new ConfigCheck($config);

        $this->assertEquals(0, count($configCheck->getErrors('logLevel')));
    }


    function dataForTestLogLevelBad() {
        return array(
            array('ERROR'),
            array('emergency!!'),
            array('alert  '),
            array('cats'),
        );
    }

    /**
     * @dataProvider dataForTestLogLevelBad
     */
    function testLogLevelBad($set) {
        $config = new Config();
        $config->logFile = '/tmp/test.log';
        $config->logLevel = $set;

        $configCheck = new ConfigCheck($config);

        $this->assertEquals(1, count($configCheck->getErrors('logLevel')));
    }
}

