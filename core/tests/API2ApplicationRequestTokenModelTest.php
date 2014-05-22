<?php


use models\API2ApplicationRequestTokenModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2ApplicationRequestTokenModelTest  extends \PHPUnit_Framework_TestCase {
	
	function dataForTestGetCallbackUrlWithParams() {
		return array(			
				array('http://test.com/callback',array('a'=>1),'http://test.com/callback?a=1'),
				array('http://test.com/callback',array('a'=>1,'b'=>'cat'),'http://test.com/callback?a=1&b=cat'),
				array('http://test.com/callback?user=fred',array('a'=>1),'http://test.com/callback?user=fred&a=1'),
				array('http://test.com/callback?user=fred',array('a'=>1,'b'=>'cat'),'http://test.com/callback?user=fred&a=1&b=cat'),
				array('http://test.com/callback?user=fred&',array('a'=>1),'http://test.com/callback?user=fred&a=1'),
				array('http://test.com/callback?user=fred&',array('a'=>1,'b'=>'cat'),'http://test.com/callback?user=fred&a=1&b=cat'),
			);
	}

	/**
     * @dataProvider dataForTestGetCallbackUrlWithParams
     */		
	function testGetCallbackUrlWithParams ($url, $params, $result) {
		$rt = new API2ApplicationRequestTokenModel();
		$rt->setCallbackUrl($url);
		$this->assertEquals($result, $rt->getCallbackUrlWithParams($params));
	}
	
	
}
