<?php


use models\SiteModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteModelTest extends \PHPUnit_Framework_TestCase {
	
	function providerPromptEmailsDaysInAdvance() {
		return array(
			array('-999999',1),
			array('-90',1),
			array('-80',1),
			array('-70',1),
			array('-60',1),
			array('-30',1),
			array('-10',1),
			array('-1',1),
			array('0',30),
			array('1',1),
			array('10',10),
			array('20',20),
			array('30',30),
			array('60',60),
			array('70',60),
			array('80',60),
			array('90',60),
			array('999999',60),
			array('oeuhioeinst',30),
		);
	}
	
	/**
	* @dataProvider providerPromptEmailsDaysInAdvance
	*/ 
	function testPromptEmailsDaysInAdvance($in, $out) {
		$site = new SiteModel;
		$site->setPromptEmailsDaysInAdvance($in);
		$this->assertEquals($out, $site->getPromptEmailsDaysInAdvance());
	}
	
}


