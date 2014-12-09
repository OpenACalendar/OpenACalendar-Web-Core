<?php


namespace JMBTechnologyLimited\RRuleUnravel;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/RRuleUnravel
 * @license https://raw.github.com/JMB-Technology-Limited/RRuleUnravel/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class WeeklyTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Whether we pass in or out in the local or UTC timezone should not matter, so let's test that
	 */
	function providerTest1() {
		return array(
			array(new \DateTime("2014-10-01 09:00:00", new \DateTimeZone("Europe/London")), new \DateTime("2014-10-01 17:00:00", new \DateTimeZone("Europe/London"))),
			array(new \DateTime("2014-10-01 08:00:00", new \DateTimeZone("UTC")), new \DateTime("2014-10-01 17:00:00", new \DateTimeZone("Europe/London"))),
			array(new \DateTime("2014-10-01 08:00:00", new \DateTimeZone("UTC")), new \DateTime("2014-10-01 16:00:00", new \DateTimeZone("UTC"))),
		);
	}

	/** @dataProvider providerTest1 */
	function test1($in, $out) {
		$rrule = new RRule("FREQ=WEEKLY");
		$unraveler = new Unraveler($rrule, $in, $out, "Europe/London");
		$unraveler->process();
		$results = $unraveler->getResults();

		$this->assertTrue(count($results) > 5);


		$this->assertEquals("2014-10-08T09:00:00+01:00", $results[0]->getStart()->format("c"));
		$this->assertEquals("2014-10-08T17:00:00+01:00", $results[0]->getEnd()->format("c"));

		$this->assertEquals("2014-10-08T08:00:00+00:00", $results[0]->getStartInUTC()->format("c"));
		$this->assertEquals("2014-10-08T16:00:00+00:00", $results[0]->getEndInUTC()->format("c"));

		$this->assertEquals("2014-10-15T09:00:00+01:00", $results[1]->getStart()->format("c"));
		$this->assertEquals("2014-10-15T17:00:00+01:00", $results[1]->getEnd()->format("c"));

		$this->assertEquals("2014-10-15T08:00:00+00:00", $results[1]->getStartInUTC()->format("c"));
		$this->assertEquals("2014-10-15T16:00:00+00:00", $results[1]->getEndInUTC()->format("c"));

		$this->assertEquals("2014-10-22T09:00:00+01:00", $results[2]->getStart()->format("c"));
		$this->assertEquals("2014-10-22T17:00:00+01:00", $results[2]->getEnd()->format("c"));

		$this->assertEquals("2014-10-22T08:00:00+00:00", $results[2]->getStartInUTC()->format("c"));
		$this->assertEquals("2014-10-22T16:00:00+00:00", $results[2]->getEndInUTC()->format("c"));

		// at this point the BST change happens

		$this->assertEquals("2014-10-29T09:00:00+00:00", $results[3]->getStart()->format("c"));
		$this->assertEquals("2014-10-29T17:00:00+00:00", $results[3]->getEnd()->format("c"));

		$this->assertEquals("2014-10-29T09:00:00+00:00", $results[3]->getStartInUTC()->format("c"));
		$this->assertEquals("2014-10-29T17:00:00+00:00", $results[3]->getEndInUTC()->format("c"));

	}

	/** @dataProvider providerTest1 */
	function test1withCount($in, $out) {
		$rrule = new RRule("FREQ=WEEKLY;COUNT=5");
		$unraveler = new Unraveler($rrule, $in, $out, "Europe/London");
		$unraveler->process();
		$results = $unraveler->getResults();

		$this->assertTrue(count($results) == 5);

		$this->assertEquals("2014-10-08T09:00:00+01:00", $results[0]->getStart()->format("c"));
		$this->assertEquals("2014-10-08T17:00:00+01:00", $results[0]->getEnd()->format("c"));

		$this->assertEquals("2014-10-08T08:00:00+00:00", $results[0]->getStartInUTC()->format("c"));
		$this->assertEquals("2014-10-08T16:00:00+00:00", $results[0]->getEndInUTC()->format("c"));

		$this->assertEquals("2014-10-15T09:00:00+01:00", $results[1]->getStart()->format("c"));
		$this->assertEquals("2014-10-15T17:00:00+01:00", $results[1]->getEnd()->format("c"));

		$this->assertEquals("2014-10-15T08:00:00+00:00", $results[1]->getStartInUTC()->format("c"));
		$this->assertEquals("2014-10-15T16:00:00+00:00", $results[1]->getEndInUTC()->format("c"));

		$this->assertEquals("2014-10-22T09:00:00+01:00", $results[2]->getStart()->format("c"));
		$this->assertEquals("2014-10-22T17:00:00+01:00", $results[2]->getEnd()->format("c"));

		$this->assertEquals("2014-10-22T08:00:00+00:00", $results[2]->getStartInUTC()->format("c"));
		$this->assertEquals("2014-10-22T16:00:00+00:00", $results[2]->getEndInUTC()->format("c"));

	}


}



