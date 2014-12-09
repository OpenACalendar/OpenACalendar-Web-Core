<?php

namespace JMBTechnologyLimited\RRuleUnravel;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/RRuleUnravel
 * @license https://raw.github.com/JMB-Technology-Limited/RRuleUnravel/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UnravelerResult {

	/** @var \DateTime **/
	protected $start;
	/** @var \DateTime **/
	protected $end;

	function __construct(\DateTime $start, \DateTime $end) {
		$this->start = $start;
		$this->end = $end;
	}

	public function getStart() {
		return $this->start;
	}

	public function getStartInUTC() {
		$startUTC = clone $this->start;
		$startUTC->setTimezone(new \DateTimeZone('UTC'));
		return $startUTC;
	}

	public function getEnd() {
		return $this->end;
	}

	public function getEndInUTC() {
		$endUTC = clone $this->end;
		$endUTC->setTimezone(new \DateTimeZone('UTC'));
		return $endUTC;
	}

}