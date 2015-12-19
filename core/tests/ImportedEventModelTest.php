<?php


use models\ImportedEventModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventModelTest  extends \BaseAppTest {


	public function  testReoccurIfDifferentFromNull1() {
		$iem = new ImportedEventModel();
		$this->assertFalse($iem->hasReoccurence());
		$this->assertTrue($iem->setReoccurIfDifferent(array("ical_rrule"=>array("FREQ"=>"WEEKLY"))));
		$this->assertTrue($iem->hasReoccurence());
	}



	function dataForTestReoccurIfDifferentTrue() {
		return array(
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),array("FREQ"=>"WEEKLY","BYDAY"=>"WE","COUNT"=>5)),
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),array("FREQ"=>"WEEKLY","BYDAY"=>"SA")),
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),array("FREQ"=>"WEEKLY","COUNT"=>"5")),
		);
	}

	/**
	 * @dataProvider dataForTestReoccurIfDifferentTrue
	 */
	public function  testReoccurIfDifferentTrue($first, $second) {
		$iem = new ImportedEventModel();
		$iem->setReoccur(array('ical_rrule'=>$first));
		$this->assertTrue($iem->setReoccurIfDifferent(array('ical_rrule'=>$second)));
	}



	function dataForTestReoccurIfDifferentFalse() {
		return array(
			array(array("FREQ"=>"WEEKLY","BYDAY"=>"WE"),array("FREQ"=>"WEEKLY","BYDAY"=>"WE")),
		);
	}

	/**
	 * @dataProvider dataForTestReoccurIfDifferentFalse
	 */
	public function  testReoccurIfDifferentFalse($first, $second) {
		$iem = new ImportedEventModel();
		$iem->setReoccur(array('ical_rrule'=>$first));
		$this->assertTrue($iem->setReoccurIfDifferent(array('ical_rrule'=>$second)));
	}

}


