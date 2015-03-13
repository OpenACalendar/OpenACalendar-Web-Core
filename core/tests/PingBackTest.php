<?php
use pingback\ParsePingBack;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class PingBackTest extends \BaseAppTest {


	function testParse1() {

		$pingback = ParsePingBack::parseFromData('<?xml version="1.0" encoding="iso-8859-1"?>
<methodCall>
  <methodName>pingback.ping</methodName>
  <params>
   <param><value><string>http://www.example.com/index.php?p=71</string></value></param>
   <param><value><string>http://www.example2.com/index.php?p=72</string></value></param>
  </params>
</methodCall>');

		$this->assertEquals("http://www.example.com/index.php?p=71", $pingback->getSourceUrl());
		$this->assertEquals("http://www.example2.com/index.php?p=72", $pingback->getTargetUrl());

	}

}




