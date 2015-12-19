<?php


namespace import;

use models\EventRecurSetModel;
use models\ImportedEventModel;
use models\ImportedEventOccurrenceModel;
use models\ImportURLModel;
use models\SiteModel;
use models\GroupModel;
use models\CountryModel;
use models\AreaModel;
use models\EventModel;
use repositories\builders\EventRepositoryBuilder;
use repositories\EventRecurSetRepository;
use repositories\EventRepository;
use repositories\ImportedEventIsEventRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventOccurrenceToEvent {

	/** @var  ImportURLModel */
	protected $importURL;

	/** @var SiteModel **/
	protected $site;

	/** @var GroupModel **/
	protected $group;

	/** @var CountryModel **/
	protected $country;

	/** @var AreaModel **/
	protected $area;

	/** @var EventRecurSetModel **/
	protected $eventRecurSet;

	protected $makeEventRecurSetIfNone = false;

	protected $eventsSeenIDs;

	public function setFromImportURlRun(ImportURLRun $importURLRun) {
		$this->site = $importURLRun->getSite();
		$this->group = $importURLRun->getGroup();
		$this->country = $importURLRun->getCountry();
		$this->area = $importURLRun->getArea();
		$this->importURL = $importURLRun->getImportURL();
		$this->eventsSeenIDs = array();
	}

	public function setEventRecurSet(EventRecurSetModel $eventRecurSet = null, $makeEventRecurSetIfNone = false) {
		$this->eventRecurSet = $eventRecurSet;
		$this->makeEventRecurSetIfNone = $makeEventRecurSetIfNone;
	}

	public function run(ImportedEventOccurrenceModel $importedEventOccurrenceModel) {

		$eventRepo = new EventRepository();
		$eventRecurSetRepo = new EventRecurSetRepository();

		if ($importedEventOccurrenceModel->hasReoccurence()) {
			// Have to load it looking for the right time to!
			$event = $this->loadEventForImportedReoccurredEvent($importedEventOccurrenceModel);
		} else {
			// just load it.
			$event = $this->loadEventForImportedEvent($importedEventOccurrenceModel);
		}

		if ($event) {
			$this->eventsSeenIDs[] = $event->getId();
			// Set Existing Event From Import Event URL
			if ($importedEventOccurrenceModel->getIsDeleted()) {
				if (!$event->getIsDeleted()) {
					$eventRepo->delete($event);
					return true;
				}
			} else {
				if ($event->setFromImportedEventModel($importedEventOccurrenceModel) || $event->getIsDeleted()) {
					$event->setIsDeleted(false);
					$eventRepo->edit($event);
					return true;
				}
			}
		} else {
			if (!$this->importURL->getIsManualEventsCreation()) {
				// New Event From Import Event URL
				$event = $this->newEventFromImportedEventModel($importedEventOccurrenceModel);
				if ($this->eventRecurSet) {
					$event->setEventRecurSetId($this->eventRecurSet->getId());
				}
				$eventRepo->create($event, $this->site, null, $this->group, null, $importedEventOccurrenceModel);
				$this->eventsSeenIDs[] = $event->getId();
				if (!$this->eventRecurSet && $this->makeEventRecurSetIfNone) {
					$this->eventRecurSet = $eventRecurSetRepo->getForEvent($event);
				}
				return true;
			} else {
				return false;
			}
		}

		return false;

	}


	public function deleteEventsNotSeenAfterRun() {

		$count = 0;

		$eventRepo = new EventRepository();

		$erb = new EventRepositoryBuilder();
		$erb->setImportURL($this->importURL);
		$erb->setIncludeDeleted(false);
		$erb->setAfterNow();
		foreach($erb->fetchAll() as $event) {
			if (!in_array($event->getId(), $this->eventsSeenIDs)) {
				$eventRepo->delete($event);
				++$count;
			}
		}

		return $count;
	}

	/** @var EventModel **/
	protected function loadEventForImportedEvent(ImportedEventModel $importedEvent) {
		$eventRepo = new \repositories\EventRepository;

		// Try new way
		$event = $eventRepo->loadByImportedEvent($importedEvent);
		if ($event) {
			return $event;
		}

		// Try old way - flags on event table - and if found, set data for new way
		$event = $eventRepo->loadByImportURLIDAndImportId($importedEvent->getImportUrlId(), $importedEvent->getImportId());
		if ($event) {
			// Save this data as the new way
			$repo = new ImportedEventIsEventRepository();
			$repo->createLink($importedEvent, $event);
			// .... and return
			return $event;
		}

		// Give up
		return null;
	}


	protected function loadEventForImportedReoccurredEvent(ImportedEventModel $importedEvent) {
		$erb = new EventRepositoryBuilder();
		$erb->setImportedEvent($importedEvent);
		foreach($erb->fetchAll() as $event) {
			if (abs($event->getStartAt()->getTimestamp() - $importedEvent->getStartAt()->getTimestamp()) < 60*60 &&
				abs($event->getEndAt()->getTimestamp() - $importedEvent->getEndAt()->getTimestamp()) < 60*60) {
				return $event;
			}
		}
		return null;
	}


	protected function newEventFromImportedEventModel(ImportedEventModel $importedEvent, $startAt = null, $endAt = null) {

		$event = new EventModel();
		$event->setFromImportedEventModel($importedEvent, $startAt, $endAt);

		if ($this->site->getIsFeaturePhysicalEvents() && !$this->site->getIsFeatureVirtualEvents()) {
			$event->setIsPhysical(true);
			$event->setIsVirtual(false);
		} else if (!$this->site->getIsFeaturePhysicalEvents() && $this->site->getIsFeatureVirtualEvents()) {
			$event->setIsPhysical(false);
			$event->setIsVirtual(true);
		}

		if ($this->country) {

			// country is set on importer.
			$event->setCountryId($this->country->getId());
			$timezones = $this->country->getTimezonesAsList();
			if ($importedEvent->getTimezone() && in_array($importedEvent->getTimezone(), $timezones)) {
				$event->setTimezone($importedEvent->getTimezone());
			} else if ($timezones) {
				// take first timezone in that country at random :-/
				$event->setTimezone($timezones[0]);
			}

			if ($this->area) {
				$event->setAreaId($this->area->getId());
			}

		} else {

			// if no country set on importer, we just pick first one at random :-/
			$crb = new \repositories\builders\CountryRepositoryBuilder();
			$crb->setSiteIn($this->site);
			$countries = $crb->fetchAll();
			if (count($countries) > 0) {
				$country = $countries[0];
				$event->setCountryId($country->getId());
				$timezones = $country->getTimezonesAsList();
				if ($importedEvent->getTimezone() && in_array($importedEvent->getTimezone(), $timezones)) {
					$event->setTimezone($importedEvent->getTimezone());
				} else if ($timezones) {
					// take first timezone in that country at random :-/
					$event->setTimezone($timezones[0]);
				}
			}
		}

		return $event;
	}
}
