<?php


use models\ImportedEventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventModelTest  extends \PHPUnit_Framework_TestCase {


	public function  testIcsRRule1IfDifferentFromNull1() {
		$iem = new ImportedEventModel();
		$this->assertFalse($iem->hasReoccurence());
		$this->assertTrue($iem->setIcsRrule1IfDifferent(array("FREQ"=>"WEEKLY")));
		$this->assertTrue($iem->hasReoccurence());
	}



	function dataForTestIcsRRule1IfDifferentTrue() {
		return array(
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),array("FREQ"=>"WEEKLY","BYDAY"=>"WE","COUNT"=>5)),
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),array("FREQ"=>"WEEKLY","BYDAY"=>"SA")),
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),array("FREQ"=>"WEEKLY","COUNT"=>"5")),
		);
	}

	/**
	 * @dataProvider dataForTestIcsRRule1IfDifferentTrue
	 */
	public function  testIcsRRule1IfDifferentTrue($first, $second) {
		$iem = new ImportedEventModel();
		$iem->setIcsRrule1($first);
		$this->assertTrue($iem->setIcsRrule1IfDifferent($second));
	}



	function dataForTestIcsRRule1IfDifferentFalse() {
		return array(
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),array("FREQ"=>"WEEKLY","BYDAY"=>"WE")),
		);
	}

	/**
	 * @dataProvider dataForTestIcsRRule1IfDifferentFalse
	 */
	public function  testIcsRRule1IfDifferentFalse($first, $second) {
		$iem = new ImportedEventModel();
		$iem->setIcsRrule1($first);
		$this->assertFalse($iem->setIcsRrule1IfDifferent($second));
	}


	function dataForTestGetIcsRRule1AsString() {
		return array(
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),"FREQ=WEEKLY;BYDAY=WE"),
		);
	}

	/**
	 * @dataProvider dataForTestGetIcsRRule1AsString
	 */
	public function  testGetIcsRRule1AsString($in, $string) {
		$iem = new ImportedEventModel();
		$iem->setIcsRrule1($in);
		$this->assertEquals($string, $iem->getIcsRrule1AsString());
	}



}


