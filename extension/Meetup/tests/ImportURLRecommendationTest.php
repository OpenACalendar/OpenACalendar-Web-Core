<?php


/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLRecommendationTest extends \BaseAppTest {



	function dataForTest1() {
		return array(
			array('http://www.meetup.com/Edinburgh-Mobile-Dev-Meetup/events/229930153/','http://www.meetup.com/Edinburgh-Mobile-Dev-Meetup/'),
			array('http://www.meetup.com/Edinburgh-Mobile-Dev-Meetup/',null),
			array('http://ican.openacalendar.org/',null),
		);
	}

	/**
	 * @dataProvider dataForTest1
	 */
	function test1($url, $newURL) {
		$importURLRecommendation = new \org\openacalendar\meetup\ImportURLRecommendation($url);
		if (is_null($newURL)) {
			$this->assertFalse($importURLRecommendation->hasNewURL());
		} else {
			$this->assertTrue($importURLRecommendation->hasNewURL());
			$this->assertEquals($newURL, $importURLRecommendation->getNewURL());
		}

	}


}
