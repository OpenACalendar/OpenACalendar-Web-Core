<?php

namespace site\controllers\newevent;


use models\EventModel;
use repositories\SiteFeatureRepository;
use site\forms\EventNewWhatDetailsForm;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class NewEventWhatDetails extends BaseNewEvent
{


	function processIsAllInformationGathered() {

		if ($this->draftEvent->getDetailsValue('event.summary')) {
			$this->isAllInformationGathered = true;
		}

	}

	function getTitle() {
		return 'What';
	}

	function getStepID() {
		return 'what';
	}

	public function canJumpBackToHere() {
		return true;
	}

	/** @var EventNewWhatDetailsForm  */
	protected $ourForm;
	protected $form;

	function onThisStepSetUpPage() {

		$this->ourForm = new EventNewWhatDetailsForm($this->application, $this->draftEvent);
		$this->form = $this->application['form.factory']->create($this->ourForm);

		return array();

	}

	function onThisStepProcessPage() {


		if ('POST' == $this->request->getMethod()) {
			$this->form->bind($this->request);
			if ($this->form->isValid()) {

				$this->draftEvent->setDetailsValue('event.summary', $this->form->get('summary')->getData());
				$this->draftEvent->setDetailsValue('event.description', $this->form->get('description')->getData());
				$this->draftEvent->setDetailsValue('event.url', $this->form->get('url')->getData());
				$this->draftEvent->setDetailsValue('event.ticket_url', $this->form->get('ticket_url')->getData());

				if ($this->form->has('is_physical')) {
					$this->draftEvent->setDetailsValue('event.is_physical', $this->form->get('is_physical')->getData());
				}
				if ($this->form->has('is_virtual')) {
					$this->draftEvent->setDetailsValue('event.is_virtual', $this->form->get('is_virtual')->getData());
				}

				foreach($this->ourForm->getCustomFields() as $customField) {

					$this->draftEvent->setDetailsValue('event.custom.' . $customField->getKey(), $this->form->get('custom_'.$customField->getKey())->getData() );
				}

				$this->isAllInformationGathered = true;
				return true;
			}
		}

		return false;
	}

	/** return array of variables to add to paramaters */
	function onThisStepSetUpPageView() {
		return array(
			'form' => $this->form->createView(),
			'formCustomFields' => $this->ourForm->getCustomFields(),
		);
	}


	function onThisStepGetViewName() {
		return 'site/eventnew/eventDraft.what.form.html.twig';
	}

	function onThisStepGetViewJavascriptName() {
		return 'site/eventnew/eventDraft.what.form.javascript.html.twig';
	}


	function stepDoneGetViewName() {
		return 'site/eventnew/eventDraft.what.preview.html.twig';
	}

	function stepDoneGetMinimalViewName() {
		return 'site/eventnew/eventDraft.what.minimalpreview.html.twig';
	}

	function addDataToEventBeforeSave(EventModel $eventModel) {
		$this->addDataToEventBeforeCheck($eventModel);
	}

	function addDataToEventBeforeCheck(EventModel $eventModel) {

		$eventModel->setSummary($this->draftEvent->getDetailsValue('event.summary'));
		$eventModel->setDescription($this->draftEvent->getDetailsValue('event.description'));
		$eventModel->setUrl($this->draftEvent->getDetailsValue('event.url'));
		$eventModel->setTicketUrl($this->draftEvent->getDetailsValue('event.ticket_url'));

		foreach($this->site->getCachedEventCustomFieldDefinitionsAsModels() as $customField) {
			if ($this->draftEvent->hasDetailsValue('event.custom.'. $customField->getKey())) {
				$eventModel->setCustomField($customField, $this->draftEvent->getDetailsValue('event.custom.'. $customField->getKey()));
			}
		}

        $siteFeatureRepo = new SiteFeatureRepository($this->application);
        $siteFeaturePhysicalEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($this->site,'org.openacalendar','PhysicalEvents');
        $siteFeatureVirtualEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($this->site,'org.openacalendar','VirtualEvents');


		if ($siteFeaturePhysicalEvents && $siteFeatureVirtualEvents) {

			$eventModel->setIsPhysical($this->draftEvent->getDetailsValue('event.is_physical'));
			$eventModel->setIsVirtual($this->draftEvent->getDetailsValue('event.is_virtual'));

		} else if ($siteFeaturePhysicalEvents) {

			$eventModel->setIsPhysical(true);
			$eventModel->setIsVirtual(false);

		} else if ($siteFeatureVirtualEvents) {

			$eventModel->setIsPhysical(false);
			$eventModel->setIsVirtual(true);

		}

	}




}

