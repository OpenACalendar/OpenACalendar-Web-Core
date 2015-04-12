<?php

namespace site\controllers\newevent;

use models\EventModel;
use models\NewEventDraftModel;
use models\SiteModel;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseNewEvent
{


	//public static $TYPE_WHO = 1;
	//public static $TYPE_WHAT = 2;
	//public static $TYPE_WHEN = 3;
	//public static $TYPE_WHERE = 4;

	/** @var NewEventDraftModel */
	protected $draftEvent;

	/** @var Silex/ */
	protected $application;

	/** @var  Request */
	protected $request;


	/** @var  SiteModel */
	protected $site;

	function __construct(NewEventDraftModel $draftEvent, Application $application, Request $request)
	{
		$this->draftEvent = $draftEvent;
		$this->application = $application;
		$this->site = $application['currentSite'];
		$this->request = $request;
	}

	protected $isAllInformationGathered = false;

	/**
	 * @return boolean
	 */
	public function getIsAllInformationGathered()
	{
		return $this->isAllInformationGathered;
	}

	public function canJumpBackToHere() {
		return false;
	}

	abstract function getTitle();

	abstract function getStepID();

	/**
	 * Do we have all the info we need against this step?
	 * Work it out and store in $this->isAllInformationGathered TRUE if yes FALSE if no
	 */
	abstract function processIsAllInformationGathered();


	/** return array of variables to add to paramaters */
	function onThisStepSetUpPage() {
		return array();
	}

	/** return boolean TRUE if processed, and want to save and reload. FALSE if not processed */
	function onThisStepProcessPage() {
		return false;
	}

	/** return array of variables to add to paramaters */
	function onThisStepSetUpPageView() {
		return array();
	}

	function onThisStepAddAJAXCallData() {
		return array();
	}

	function stepDoneGetViewName() {
		return '';
	}

	function stepDoneGetMinimalViewName() {
		return '';
	}

	function onThisStepGetViewJavascriptName() {
		return '';
	}

	function onThisStepGetViewName() {
		return '';
	}

	function addDataToEventBeforeSave(EventModel $eventModel) {

	}

	function addDataToEventBeforeCheck(EventModel $eventModel) {

	}



}