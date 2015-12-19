<?php


use models\API2ApplicationModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2ApplicationModelTest  extends BaseAppTest {

	function testAddRemoveCallbackURLSAllowed1() {
		$app = new API2ApplicationModel();
		
		## No URLS
		$this->assertEquals(false, $app->hasAllowedCallbackUrls());
		$this->assertEquals(true, $app->isCallbackUrlAllowed("http://www.example.com/callback"));
		$this->assertEquals(true, $app->isCallbackUrlAllowed("http://www.example.co.uk/callback"));
		
		## Add 
		$app->addAllowedCallbackUrl('http://www.example.com/');
		
		## Has URLS
		$this->assertEquals(true, $app->hasAllowedCallbackUrls());
		$this->assertEquals(true, $app->isCallbackUrlAllowed("http://www.example.com/callback"));
		$this->assertEquals(false, $app->isCallbackUrlAllowed("http://www.example.co.uk/callback"));
		
		## Remove
		$app->removeAllowedCallbackUrl('http://www.example.com/');
		
		## No URLS
		$this->assertEquals(false, $app->hasAllowedCallbackUrls());
		$this->assertEquals(true, $app->isCallbackUrlAllowed("http://www.example.com/callback"));
		$this->assertEquals(true, $app->isCallbackUrlAllowed("http://www.example.co.uk/callback"));
	}
	
	
	function dataForTtestCallbackURLMatches() {
		return array(			
				// all allowed
				array('','http://test.com/callback',true),
				// http
				array('http://test.com','http://test.com/callback',true),
				array('http://test.com/','http://test.com/callback',true),
				array('http://test.co.uk','http://test.com/callback',false),
				array('http://test.co.uk/','http://test.com/callback',false),
				array("http://test.com\nhttp://test.co.uk",'http://test.com/callback',true),
				// https
				array('https://test.com','https://test.com/callback',true),
				array('https://test.com/','https://test.com/callback',true),
				array('https://test.co.uk','https://test.com/callback',false),
				array('https://test.co.uk/','https://test.com/callback',false),
				array("https://test.com\nhttp://test.co.uk",'https://test.com/callback',true),
				// http & https
				array('https://test.com','http://test.com/callback',false),
				array('https://test.com/','http://test.com/callback',false),
				array('https://test.co.uk','http://test.com/callback',false),
				array('https://test.co.uk/','http://test.com/callback',false),
				array("https://test.com\nhttps://test.co.uk",'http://test.com/callback',false),
				array('http://test.com','https://test.com/callback',false),
				array('http://test.com/','https://test.com/callback',false),
				array('http://test.co.uk','https://test.com/callback',false),
				array('http://test.co.uk/','https://test.com/callback',false),
				array("http://test.com\nhttp://test.co.uk",'https://test.com/callback',false),
				array("https://test.com\nhttp://test.com",'https://test.com/callback',true),
			);
	}

	/**
     * @dataProvider dataForTtestCallbackURLMatches
     */		
	function testCallbackURLMatches ($allowed_urls, $url, $result) {
		$app = new API2ApplicationModel();
		$app->setAllowedCallbackUrls($allowed_urls);
		$this->assertEquals($result, $app->isCallbackUrlAllowed($url));
	}
	
	
}
