<?php

use models\GroupModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupTwitterUserNameTest extends \BaseAppTest {

	function dataForTestSet() {
		return array(
				array('jarofgreen','jarofgreen'),
				array('@jarofgreen','jarofgreen'),
				array('twitter@jarofgreen','jarofgreen'),
				array(' @jarofgreen','jarofgreen'),
				array('Follow @jarofgreen','jarofgreen'),
				array('http://twitter.com/#!/jarofgreen','jarofgreen'),
				array('https://twitter.com/#!/jarofgreen','jarofgreen'),
				array('http://twitter.com/jarofgreen','jarofgreen'),
				array('https://twitter.com/jarofgreen','jarofgreen'),
				array('http://www.twitter.com/jarofgreen','jarofgreen'),
				array('twitter.com/jarofgreen','jarofgreen'),
				array('www.twitter.com/jarofgreen','jarofgreen'),
			);
	}
	
	/**
     * @dataProvider dataForTestSet
     */
	function testSet($set, $result) {
		$group = new GroupModel();
		$group->setTwitterUsername($set);
		$this->assertEquals($result, $group->getTwitterUsername());
	}
	
}

