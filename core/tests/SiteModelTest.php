<?php


use models\SiteModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteModelTest extends \BaseAppTest {
	
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


	function providerSlugIsValid() {
		return array(
			array('abc'),
			array('cat12'),
		);
	}

	/**
	* @dataProvider providerSlugIsValid
	*/
	function testSlugIsValid($in) {
		$this->app['config']->siteSlugReserved = array('www');
		$this->assertTrue(SiteModel::isSlugValid($in, $this->app['config']));
	}

	function providerSlugIsNotValid() {
		return array(
			array('the cat sat on the mat'),
			array('a'),
			array(''),
			array('-'),
			array('cat-mat'),
			array('cafés'),
			array('www'),
		);
	}

	/**
	* @dataProvider providerSlugIsNotValid
	*/
	function testSlugIsNotValid($in) {
		$this->app['config']->siteSlugReserved = array('www');
		$this->assertFalse(SiteModel::isSlugValid($in, $this->app['config']));
	}

	
}


