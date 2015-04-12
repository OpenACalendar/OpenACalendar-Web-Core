<?php

namespace site\controllers\newevent;
use models\EventModel;
use repositories\builders\GroupRepositoryBuilder;
use repositories\GroupRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class NewEventWhoGroup extends BaseNewEvent
{

	function processIsAllInformationGathered() {
		if ($this->draftEvent->getDetailsValue('group.none') || $this->draftEvent->getDetailsValue('group.id') || $this->draftEvent->getDetailsValue('group.title')) {
			$this->isAllInformationGathered = true;
		}
	}

	function getTitle() {
		return 'Who';
	}

	function getStepID() {
		return 'who';
	}

	public function canJumpBackToHere() {
		return true;
	}

	function onThisStepGetViewName() {
		return 'site/eventnew/eventDraft.who.html.twig';
	}


	function onThisStepGetViewJavascriptName() {
		return 'site/eventnew/eventDraft.who.javascript.html.twig';
	}
	function onThisStepProcessPage()
	{

		if ($this->request->request->get('action') == 'nogroup') {
			$this->draftEvent->setDetailsValue('group.none',true);
			$this->isAllInformationGathered = true;
			return true;
		}

		if ($this->request->request->get('action') == 'selectgroup') {

			$gr = new GroupRepository();
			$group = $gr->loadBySlug($this->site, $this->request->request->get('group'));
			if ($group) {
				$this->draftEvent->setDetailsValue('group.id',$group->getId());
				$this->draftEvent->setDetailsValue('group.title',$group->getTitle());
				$this->isAllInformationGathered = true;
				return true;
			}
		}


	}



	function onThisStepSetUpPageView() {

		$out = array('groupSearchText'=>$this->request->request->get('groupsearch'));

		if ($this->request->request->get('action') == 'groupsearch') {
			$grb = new GroupRepositoryBuilder();
			$grb->setSite($this->site);
			$grb->setIncludeDeleted(false);
			$grb->setFreeTextsearch($this->request->request->get('groupsearch'));
			$grb->setLimit(100);
			$out['groups'] = $grb->fetchAll();
		}

		return $out;
	}

	function stepDoneGetViewName()
	{
		if ($this->draftEvent->getDetailsValue('group.title')) {
			return 'site/eventnew/eventDraft.who.preview.html.twig';
		}
	}

	function stepDoneGetMinimalViewName()
	{
		if ($this->draftEvent->getDetailsValue('group.title')) {
			return 'site/eventnew/eventDraft.who.minimalpreview.html.twig';
		}
	}

	function addDataToEventBeforeSave(EventModel $eventModel) {
		$this->addDataToEventBeforeCheck($eventModel);
	}

	function addDataToEventBeforeCheck(EventModel $eventModel) {
		if ($this->draftEvent->getDetailsValue('group.id')) {
			$eventModel->setGroupId($this->draftEvent->getDetailsValue('group.id'));
		}


	}
}