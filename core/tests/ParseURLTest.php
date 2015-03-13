<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ParseURLTest extends \BaseAppTest {
	
	
	function CanonicalDataProvider() {
		return array(
			array('http://test.com/ContactUs','http://test.com/ContactUs?'),
			array('http://TEST.COM/ContactUs','http://test.com/ContactUs?'),
			array('http://TEST.COM/contactus','http://test.com/contactus?'),
			array('http://TEST.COM/','http://test.com/?'),
			array('http://TEST.COM','http://test.com/?'),
		);
	}
	
	/**
	* @dataProvider CanonicalDataProvider
	*/ 
	function testCanonical($in, $out) {
		global $CONFIG;
		$p = new \ParseURL($in);
		$this->assertEquals($out, $p->getCanonical());
	}
}



