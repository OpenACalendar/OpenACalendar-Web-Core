<?php


/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MeetupURLParserTest extends \BaseAppTest {


	function dataForTest1() {
		return array(
			array('http://www.meetup.com/Edinburgh-Mobile-Dev-Meetup/events/229930153/','Edinburgh-Mobile-Dev-Meetup','229930153'),
			array('http://www.meetup.com/Edinburgh-Mobile-Dev-Meetup/','Edinburgh-Mobile-Dev-Meetup',null),
			array('http://ican.openacalendar.org/',null,null),
		);
	}

	/**
	 * @dataProvider dataForTest1
	 */
	function test1($url, $group, $event) {
		$meetupURLParser = new \org\openacalendar\meetup\MeetupURLParser($url);
		if (is_null($group)) {
			$this->assertNull($meetupURLParser->getGroupName());
		} else {
			$this->assertEquals($group, $meetupURLParser->getGroupName());
		}
		if (is_null($event)) {
			$this->assertNull($meetupURLParser->getEventId());
		} else {
			$this->assertEquals($event, $meetupURLParser->getEventId());
		}
	}

}

