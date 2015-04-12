<?php

namespace site\controllers;

use models\EventEditMetaDataModel;
use models\NewEventDraftModel;
use repositories\AreaRepository;
use repositories\NewEventDraftRepository;
use Silex\Application;
use site\controllers\newevent\NewEventPreview;
use site\controllers\newevent\NewEventWhatDetails;
use site\controllers\newevent\NewEventWhenDetails;
use site\controllers\newevent\NewEventWhenFreeText;
use site\controllers\newevent\NewEventWhereDetails;
use site\controllers\newevent\NewEventWhoGroup;
use site\controllers\newevent\StepsUI;
use site\forms\EventNewForm;
use site\forms\EventEditForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use models\SiteModel;
use models\EventModel;
use repositories\EventRepository;
use repositories\GroupRepository;
use \SearchForDuplicateEvents;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventNewController {

	function getSteps(Request $request,Application $app, NewEventDraftModel $newEventDraftModel) {
		$out = array();
		if ($app['currentSite']->getIsFeatureGroup()) {
			$out[] = new NewEventWhoGroup($newEventDraftModel, $app, $request);
		}
		$out[] = new NewEventWhatDetails($newEventDraftModel, $app, $request);
		$out[] = new NewEventWhenDetails($newEventDraftModel, $app, $request);
		if ($app['currentSite']->getIsFeaturePhysicalEvents() && (!$newEventDraftModel->hasDetailsValue('event.is_physical') || $newEventDraftModel->getDetailsValue('event.is_physical') )) {
			$out[] = new NewEventWhereDetails($newEventDraftModel, $app, $request);
		}
		$out[] = new NewEventPreview($newEventDraftModel, $app, $request);
		return $out;
	}

	
	function newEvent(Request $request, Application $app) {

		/////////////////////////////////////////////////////// Set up incoming vars

		$newEventDraft = new NewEventDraftModel();
		$newEventDraft->setSiteId($app['currentSite']->getId());
		$newEventDraft->setUserAccountId($app['currentUser'] ? $app['currentUser']->getId() : null);

		$incomingData = array();

		// check for incoming date
		if (isset($_GET['date']) && trim($_GET['date'])) {
			$bits = explode("-", $_GET['date']);
			if (count($bits) == 3 && intval($bits[0]) && intval($bits[1]) && intval($bits[2])) {
				$incomingData['event.start_at'] = \TimeSource::getDateTime();
				$incomingData['event.start_at']->setTimezone(new \DateTimeZone($app['currentTimeZone']));
				$incomingData['event.start_at']->setDate($bits[0], $bits[1], $bits[2]);
				$incomingData['event.start_at']->setTime(9, 0, 0);
				$incomingData['event.end_at'] = clone $incomingData['event.start_at'];
				$incomingData['event.end_at']->setTime(17, 0, 0);
			}
		}

		// check for incoming area
		if (isset($_GET['area']) && trim($_GET['area'])) {
			$ar = new AreaRepository();
			$area = $ar->loadBySlug($app['currentSite'], $request->query->get('area'));
			if ($area) {
				$incomingData['area.id'] = $area->getId();
				$incomingData['area.title'] = $area->getTitle();
			}
		}

		// check for incoming group
		if (isset($_GET['group']) && trim($_GET['group'])) {
			$gr = new GroupRepository();
			$group = $gr->loadBySlug($app['currentSite'], $request->query->get('group'));
			if ($group) {
				$newEventDraft->setDetailsValue('group.id',$group->getId());
				$newEventDraft->setDetailsValue('group.title',$group->getTitle());
			}
		}

		/////////////////////////////////////////////////////// Check Permissions and Prompt IF NEEDED

		if (!$app['currentUser'] && !$app['currentUserActions']->has("org.openacalendar","eventNew") &&  $app['anyVerifiedUserActions']->has("org.openacalendar","eventNew")) {
			return $app['twig']->render('site/eventnew/new.useraccountneeded.html.twig', array('incomingData'=>$incomingData));
		}

		if (!$app['currentUser']) {
			$app->abort(403, "Not allowed");
		}

		/////////////////////////////////////////////////////// Set up draft and start!


		foreach($incomingData as $k=>$v) {
			$newEventDraft->setDetailsValue('incoming.' . $k, $v);
		}

		$repo = new NewEventDraftRepository();
		$repo->create($newEventDraft);

		$steps = $this->getSteps($request, $app, $newEventDraft);

		return $app->redirect('/event/new/'.$newEventDraft->getSlug()."/".$steps[0]->getStepID());

	}

	protected $parameters;

	protected function buildDraft($draftslug, Request $request, Application $app)
	{

		$this->parameters = array('draft' => null);

		$repo = new NewEventDraftRepository();
		$this->parameters['draft'] = $repo->loadBySlugForSiteAndUser($draftslug, $app['currentSite'], $app['currentUser']);
		if (!$this->parameters['draft']) {
			return false;
		}

		// check not already made into event!
		if ($this->parameters['draft']->getEventId() || $this->parameters['draft']->getWasExistingEventId()) {
			return false;
		}

		return true;
	}

	protected function buildSteps($stepid, Request $request, Application $app) {

		$this->parameters['steps'] = $this->getSteps($request, $app, $this->parameters['draft']);

		$this->parameters['isAllInformationGathered'] = true;
		$this->parameters['currentStep'] = null;

		$idxCurrentStep = -1;
		$idxWantedStep = -1;
		foreach($this->parameters['steps'] as $idx=>$step) {
			if ($this->parameters['isAllInformationGathered']) {
				$this->parameters['currentStep'] = $step;
				$idxCurrentStep = $idx;
				$step->processIsAllInformationGathered();
				$this->parameters['isAllInformationGathered'] = $step->getIsAllInformationGathered();
			}
			if ($step->getStepId() == $stepid) {
				$idxWantedStep = $idx;
			}
		}

		// if user tries to look at step that doesn't exist ... normal next step
		if ($idxWantedStep == -1) {
			return $this->parameters['currentStep']->getStepID();
		}

		// if user wants to look at step that is next step anyway
		if ($idxWantedStep == $idxCurrentStep) {
			if ($idxCurrentStep < (count($this->parameters['steps']) -1)) {
				$this->parameters['nextStepID'] =  $this->parameters['steps'][$idxCurrentStep+1]->getStepID();
			}
			return null;
		}

		// If user tries to look at step in future ... normal next step
		if ($idxWantedStep > $idxCurrentStep) {
			return $this->parameters['currentStep']->getStepID();
		}

		// if user tries to look at step in past
		if ($idxWantedStep < $idxCurrentStep) {
			$this->parameters['currentStep'] = $this->parameters['steps'][$idxWantedStep];
			if ($idxWantedStep < (count($this->parameters['steps']) -1)) {
				$this->parameters['nextStepID'] =  $this->parameters['steps'][$idxWantedStep+1]->getStepID();
			}
			return null;
		}


	}

	function newEventDraft($draftslug, $stepid, Request $request, Application $app) {

		if (!$this->buildDraft($draftslug, $request, $app)) {
			return $app->abort(404);
		}

		$redirectToStep = $this->buildSteps($stepid, $request, $app);
		if ($redirectToStep) {
			return $app->redirect("/event/new/".$this->parameters['draft']->getSlug()."/".$redirectToStep);
		}

		if ($this->parameters['currentStep']->getStepID() == 'preview') {


			if ($request->request->get('action') == 'CREATE' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {


				//////////////////////////////////////// Actually Create Event

				$event = new EventModel();
				$event->setSiteId($app['currentSite']->getId());
				foreach ($this->parameters['steps'] as $step) {
					$step->addDataToEventBeforeSave($event);
				}

				$eventEditMetaData = new EventEditMetaDataModel();
				$eventEditMetaData->setUserAccount($app['currentUser']);
				$eventEditMetaData->setCreatedFromNewEventDraftID($this->parameters['draft']->getId());
				if ($request->request->get('edit_comment')) {
					$eventEditMetaData->setEditComment($request->request->get('edit_comment'));
				}

				$repo = new EventRepository();
				$repo->createWithMetaData($event, $app['currentSite'], $eventEditMetaData);


				if ($app['config']->logFileParseDateTimeRange && $this->parameters['draft']->hasDetailsValue('event.start_end_freetext.done') == 'yes') {
					$parseStart = $this->parameters['draft']->getDetailsValueAsDateTime('event.start_end_freetext.start');
					$parseEnd = $this->parameters['draft']->getDetailsValueAsDateTime('event.start_end_freetext.end');
					$success = $parseStart->getTimestamp() == $event->getStartAt()->getTimestamp() && $parseEnd->getTimestamp() == $event->getEndAt()->getTimestamp();
					$handle = fopen($app['config']->logFileParseDateTimeRange, "a");
					$now = \TimeSource::getDateTime();
					fputcsv($handle, array(
						'Site',
						$app['currentSite']->getId(),
						$app['currentSite']->getSlug(),
						'Event',
						$event->getSlug(),
						'Now',
						$now->format("c"),
						'Wanted Timezone',
						$event->getTimezone(),
						'Wanted Start UTC',
						$event->getStartAtInUTC()->format("c"),
						'Wanted End UTC',
						$event->getEndAtInUTC()->format("c"),
						'Typed',
						$this->parameters['draft']->getDetailsValue('event.start_end_freetext.text'),
						'Got Start UTC',
						$parseStart->format("c"),
						'Got End UTC',
						$parseEnd->format("c"),
						($success ? 'SUCCESS' : 'FAIL'),
					));
					fclose($handle);
				}

				$app['flashmessages']->addMessage("Thanks! The event has been created.");

				return $app->redirect('/event/' . $event->getSlugForUrl());

			} else {

				//////////////////////////////////////// Show Final Preview Screen

				$this->parameters['stepsUI'] = (new StepsUI($this->parameters['steps'], $this->parameters['currentStep']))->getSteps();

				$this->parameters = array_merge($this->parameters, $this->parameters['currentStep']->onThisStepSetUpPageView());

				return $app['twig']->render('site/eventnew/eventDraft.preview.html.twig', $this->parameters);

			}


		} else {

			//////////////////////////////////////// Info needed! Show this step

			$this->parameters = array_merge($this->parameters, $this->parameters['currentStep']->onThisStepSetUpPage());

			if ($this->parameters['currentStep']->onThisStepProcessPage() && 'POST' == $request->getMethod()) {
				$repo = new NewEventDraftRepository();
				$repo->saveProgress($this->parameters['draft']);
				if ($this->parameters['currentStep']->getIsAllInformationGathered()) {
					return $app->redirect('/event/new/' . $this->parameters['draft']->getSlug() . "/" . $this->parameters['nextStepID']);
				} else {
					// we just have some info, but not all for this step. Stay on this step.
					return $app->redirect('/event/new/' . $this->parameters['draft']->getSlug() . "/". $this->parameters['currentStep']->getStepID());
				}

			}
			$this->parameters = array_merge($this->parameters, $this->parameters['currentStep']->onThisStepSetUpPageView());

			$this->parameters['stepsUI'] = (new StepsUI($this->parameters['steps'], $this->parameters['currentStep']))->getSteps();
			return $app['twig']->render('site/eventnew/eventDraft.html.twig', $this->parameters);

		}

	}


	function newEventDraftJSON($draftslug, $stepid, Request $request, Application $app)
	{

		if (!$this->buildDraft($draftslug, $request, $app)) {
			return $app->abort(404);
		}

		$redirectToStep = $this->buildSteps($stepid, $request, $app);
		if ($redirectToStep) {
			return $app->abort(404);
		}

		$data = array('duplicates'=>array());

		if ($this->parameters['currentStep']) {
			$this->parameters['currentStep']->onThisStepSetUpPage();
			$this->parameters['currentStep']->onThisStepProcessPage();
		}

		///////////////////////////////////////////// Dupes?

		$event = new EventModel();
		$event->setSiteId($app['currentSite']->getId());
		foreach ($this->parameters['steps'] as $step) {
			$step->addDataToEventBeforeCheck($event);
		}

		if ($request->query->get('notDuplicateSlugs')) {
			if ($this->parameters['draft']->addNotDuplicateEvents(explode(",", $request->query->get('notDuplicateSlugs')))) {
				$repo = new NewEventDraftRepository();
				$repo->saveNotDuplicateEvents($this->parameters['draft']);
			}
		}

		$searchForDuplicateEvents = new SearchForDuplicateEvents(
			$event,
			$app['currentSite'],
			$app['config']->findDuplicateEventsShow,
			$app['config']->findDuplicateEventsThreshhold,
			is_array($app['config']->findDuplicateEventsNoMatchSummary) ? $app['config']->findDuplicateEventsNoMatchSummary : array()
		);
		$searchForDuplicateEvents->setNotDuplicateSlugs($this->parameters['draft']->getNotDuplicateEvents());
		$timeZone = new \DateTimeZone($event->getTimezone());
		$this->parameters['duplicateEvents'] = array();
		foreach ($searchForDuplicateEvents->getPossibleDuplicates() as $dupeEvent) {
			$start = clone $dupeEvent->getStartAt();
			$start->setTimezone($timeZone);
			$data['duplicates'][] = array(
				'slug' => $dupeEvent->getSlug(),
				'summary' => $dupeEvent->getSummary(),
				'description' => $dupeEvent->getDescription(),
				'startDay' => $start->format("D"),
				'startDate' => $start->format("jS"),
				'startMonthYear' => $start->format("M \'y"),
				'startTime' => $start->format("g:ia"),
			);
		}


		///////////////////////////////////////////// Other Data
		if ($this->parameters['currentStep']) {
			$data = array_merge($data, $this->parameters['currentStep']->onThisStepAddAJAXCallData());
		}

		///////////////////////////////////////////// Response

		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;

	}

	function newEventIsDupeOf($draftslug, $eventslug, Request $request, Application $app) {

		if (!$this->buildDraft($draftslug, $request, $app)) {
			return $app->abort(404);
		}

		$er = new EventRepository();
		$event = $er->loadBySlug($app['currentSite'], $eventslug);
		if (!$event) {
			return $app->abort(404);
		}

		$ner = new NewEventDraftRepository();
		$ner->markIsDuplicateOf($this->parameters['draft'], $event);

		return $app->redirect('/event/' . $event->getSlugForUrl());

	}

}


