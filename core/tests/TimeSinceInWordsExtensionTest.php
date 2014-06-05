<?php


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


class TimeSinceInWordsExtensionTest   extends \PHPUnit_Framework_TestCase  {

	
	function dataForTest1() {
		return array(
				array(2014, 1, 1, 1, 30, 0, "in the future"),
				array(2014, 1, 1, 9, 30, 0, "now"),
				array(2014, 1, 1, 9, 30, 1, "1 second ago"),
				array(2014, 1, 1, 9, 30, 2, "2 seconds ago"),
				array(2014, 1, 1, 9, 30, 5, "5 seconds ago"),
				array(2014, 1, 1, 9, 31, 0, "1 minute ago"),
				array(2014, 1, 1, 9, 31, 1, "1 minute ago"),
				array(2014, 1, 1, 9, 32, 0, "2 minutes ago"),
				array(2014, 1, 1, 9, 32, 1, "2 minutes ago"),
				array(2014, 1, 1, 10, 30, 0, "1 hour ago"),
				array(2014, 1, 1, 10, 30, 1, "1 hour ago"),
				array(2014, 1, 1, 10, 40, 0, "1 hour ago"),
				array(2014, 1, 1, 11, 30, 0, "2 hours ago"),
				array(2014, 1, 1, 11, 30, 1, "2 hours ago"),
				array(2014, 1, 1, 11, 40, 0, "2 hours ago"),
				array(2014, 1, 2, 9, 30, 0, "1 day ago"),
				array(2014, 1, 2, 9, 30, 1, "1 day ago"),
				array(2014, 1, 3, 9, 30, 0, "2 days ago"),
				array(2014, 1, 3, 9, 30, 1, "2 days ago"),
			);
	}
	
	/**
     * @dataProvider dataForTest1
     */
	function test1($year, $month, $day, $hour, $minute, $second, $result) {
		TimeSource::mock($year, $month, $day, $hour, $minute, $second);
		
		$ext = new twig\extensions\TimeSinceInWordsExtension();
		$this->assertEquals($result, $ext->timeSinceInWords(getUTCDateTime(2014, 1, 1, 9, 30, 0)));
		
	}
	
}

