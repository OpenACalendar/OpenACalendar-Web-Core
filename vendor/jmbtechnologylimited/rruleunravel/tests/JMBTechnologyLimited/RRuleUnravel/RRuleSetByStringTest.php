<?php


namespace JMBTechnologyLimited\RRuleUnravel;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/RRuleUnravel
 * @license https://raw.github.com/JMB-Technology-Limited/RRuleUnravel/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RRuleSetByStringTest extends \PHPUnit_Framework_TestCase {


	function providerTest1() {
		return array(
			array('FREQ=YEARLY;BYDAY=WE', 'YEARLY', 'WE', -1),
			array('FREQ=YEARLY;BYDAY=WE;COUNT=5', 'YEARLY', 'WE', 5),
		);
	}

	/** @dataProvider providerTest1 */
	function test1set($in, $freq, $byday, $count) {
		$rrule = new RRule();
		$rrule->setByString($in);
		$this->assertEquals($freq, $rrule->getFreq());
		$this->assertEquals($byday, $rrule->getByday());
		$this->assertEquals($count, $rrule->getCount());
	}

	/** @dataProvider providerTest1 */
	function test1construct($in, $freq, $byday, $count) {
		$rrule = new RRule($in);
		$this->assertEquals($freq, $rrule->getFreq());
		$this->assertEquals($byday, $rrule->getByday());
		$this->assertEquals($count, $rrule->getCount());
	}

}



