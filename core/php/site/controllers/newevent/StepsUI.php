<?php

namespace site\controllers\newevent;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class StepsUI
{

	protected $stepsOut = array();

	function __construct($steps, BaseNewEvent $currentStep)
	{
		$currentStepFound = false;
		foreach($steps as $step) {
			if ($step->getStepID() == $currentStep->getStepId()) {
				$currentStepFound = true;
			}
			if (count($this->stepsOut) > 0 && $this->stepsOut[count($this->stepsOut)-1]['title'] == $step->getTitle()) {
				if (!$step->getIsAllInformationGathered()) {
					$this->stepsOut[count($this->stepsOut)-1]['done'] = false;
				}
				if ($step->canJumpBackToHere() && !$currentStepFound && !$this->stepsOut[count($this->stepsOut)-1]['jumpBack']) {
					$this->stepsOut[count($this->stepsOut)-1]['jumpBack'] = $step->getStepID();
				}
			} else {
				$this->stepsOut[] = array(
					'done'=>$step->getIsAllInformationGathered(),
					'title'=>$step->getTitle(),
					'jumpBack'=> $step->canJumpBackToHere() && !$currentStepFound ? $step->getStepID() : null,
				);
			}
		}
	}

	/**
	 * @return array
	 */
	public function getSteps()
	{
		return $this->stepsOut;
	}

}