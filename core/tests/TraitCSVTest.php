<?php


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TraitCSVTest extends \PHPUnit_Framework_TestCase {

	use \api1exportbuilders\TraitCSV;


	function dataForTestCell() {
		return array(
			array('','""'),
			array(null,'""'),
			array('out','"out"'),
			array('out out','"out out"'),
			array('he said "hi"','"he said ""hi"""'),
		);
	}

	/**
	 * @dataProvider dataForTestCell
	 */
	function testCell($in, $out) {
		$this->assertEquals($out, $this->getCell($in));
	}



}