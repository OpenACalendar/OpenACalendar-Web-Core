<?php

namespace JMBTechnologyLimited\RRuleUnravel;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/RRuleUnravel
 * @license https://raw.github.com/JMB-Technology-Limited/RRuleUnravel/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class RRuleUnravelling {



	/** @var  integer */
	protected $count = -1;


	/** @var  RRule */
	protected $rrule;

	function __construct(RRule $rrule)
	{
		$this->rrule = $rrule;
		$this->count = $rrule->getCount();
	}

	/**
	 * @return \JMBTechnologyLimited\RRuleUnravel\RRule
	 */
	public function getRrule()
	{
		return $this->rrule;
	}

	/**
	 * @return boolean
	 */
	public function isCountLeft()
	{
		// -1 indicates infinite and should return true
		return $this->count != 0;
	}

	public function decreaseCount()
	{
		if ($this->count > 0)
		{
			$this->count--;
		}
	}



}

