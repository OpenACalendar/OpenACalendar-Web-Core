<?php


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


class TruncateExtensionTest   extends \BaseAppTest  {

	
	function dataForTest1() {
		return array(
				array("in the future", 2, "in ..."),
				array("in the future", 3, "in the ..."),
				array("in the future", 4, "in the ..."),
				array("in the future", 5, "in the ..."),
				array("in the future", 6, "in the ..."),
				array("in the future", 7, "in the future"),
				array("in the future", 8, "in the future"),
				array("in the future", 9, "in the future"),
				array("in the future", 10, "in the future"),
				array("in the future", 11, "in the future"),
				array("in the future", 12, "in the future"),
				array("in the future", 13, "in the future"),
				array("in the future", 14, "in the future"),
				array("in the future", 15, "in the future"),
				array("in the future", 16, "in the future"),
			);
	}
	
	/**
     * @dataProvider dataForTest1
     */
	function test1($in, $length, $out) {
		$ext = new twig\extensions\TruncateExtension();
		$this->assertEquals($out, $ext->truncate($in, $length));
	}
	
}

