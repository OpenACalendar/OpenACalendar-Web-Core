<?php

namespace JMBTechnologyLimited\RRuleUnravel;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/RRuleUnravel
 * @license https://raw.github.com/JMB-Technology-Limited/RRuleUnravel/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class Unraveler {

	/** @var  RRuleUnravelling */
	protected $rruleUnravelling;

	/** @var  \DateTime */
	protected $start;

	/** @var  \DateTime */
	protected $end;

	protected $timezone;

	protected $results;

	function __construct(RRule $rrule, \DateTime $start, \DateTime $end, $timezone='UTC')
	{
		$this->rruleUnravelling = new RRuleUnravelling($rrule);
		$this->start = $start;
		$this->end = $end;
		$this->timezone = $timezone;
	}


	public function process()
	{

		$this->results = array();

		$start = clone $this->start;
		if ($start->getTimezone()->getName() != $this->timezone) {
			$start->setTimezone(new \DateTimeZone($this->timezone));
		}
		$end = clone $this->end;
		if ($end->getTimezone()->getName() != $this->timezone) {
			$end->setTimezone(new \DateTimeZone($this->timezone));
		}

		$intervalString = "";
		if ($this->rruleUnravelling->getRrule()->getFreq() == "WEEKLY")
		{
			$intervalString = "P7D";
		}


		if ($intervalString)
		{
			$interval = new \DateInterval($intervalString);

			$process = true;

			while($process) {

				$start->add($interval);
				$end->add($interval);

				$add = true;
				if (!$this->rruleUnravelling->isCountLeft()) {
					$add = false;
					// can also stop processing now
					$process = false;
				}

				if ($add)
				{
					$this->results[] = new UnravelerResult(clone $start, clone $end);
					$this->rruleUnravelling->decreaseCount();
				}

				// This is a temporary stop for rules with no count, so they stop sometime.
				// Need to do better!
				if (count($this->results) > 100)
				{
					$process = false;
				}

			}


		}

	}

	public function getResults()
	{
		return $this->results;
	}

}


