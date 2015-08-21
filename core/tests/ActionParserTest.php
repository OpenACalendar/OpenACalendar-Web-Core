<?php

use sysadmin\ActionParser;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ActionParserTest extends BaseAppTest {
	
	
	function noParamDataProvider() {
		return array(
			array("cat   ","cat"),
			array("CAT   ","cat"),
		);
	}
	
	/**
	* @dataProvider noParamDataProvider
	*/ 
	function testNoParam($in, $out) {
		$ap = new ActionParser($in);
		$this->assertEquals($out,$ap->getCommand());
	}
	
	function oneParamDataProvider() {
		return array(
			array("cat tabby","cat","tabby"),
			array("CAT tabby","cat","tabby"),
		);
	}
	
	/**
	* @dataProvider oneParamDataProvider
	*/ 
	function testOneParam($in, $out, $param) {
		$ap = new ActionParser($in);
		$this->assertEquals($out,$ap->getCommand());
		$this->assertEquals($param,$ap->getParam(0));
	}

	function booleanParamDataProvider() {
		return array(
			array("test t  ",true),
			array("test f  ",false),
			array("test T  ",true),
			array("test F  ",false),
			array("test tr  ",true),
			array("test fa  ",false),
			array("test Tr  ",true),
			array("test Fa  ",false),
			array("test 1  ",true),
			array("test 0  ",false),
			array("test 111  ",true),
			array("test 000  ",false),
			array("TEST 000  ",false),
		);
	}
	
	/**
	* @dataProvider booleanParamDataProvider
	*/ 
	function testBooleanParam($in, $out) {
		$ap = new ActionParser($in);
		$this->assertEquals($out,$ap->getParamBoolean(0));
	}
	
}
