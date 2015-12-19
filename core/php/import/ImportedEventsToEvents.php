<?php

namespace import;

use models\ImportedEventModel;
use models\SiteModel;
use models\GroupModel;
use models\CountryModel;
use models\AreaModel;
use models\EventModel;
use repositories\ImportedEventIsEventRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventsToEvents {

	/** @var models\SiteModel **/
	protected $site;

	/** @var models\GroupModel **/
	protected $group;

	/** @var models\CountryModel **/
	protected $country;

	/** @var models\AreaModel **/
	protected $area;

	protected $importedEvents = array();

	public function setFromImportURlRun(ImportURLRun $importURLRun) {
		$this->site = $importURLRun->getSite();
		$this->group = $importURLRun->getGroup();
		$this->country = $importURLRun->getCountry();
		$this->area = $importURLRun->getArea();
	}

	public function addImportedEvent(ImportedEventModel $importedEvent) {
		$this->importedEvents[] = $importedEvent;
	}

	public function run() {
		$eventRepo = new \repositories\EventRepository;
		foreach($this->importedEvents as $importedEvent) {
			$event = $this->loadEventForImportedEvent($importedEvent);
			if ($event) {
				// Set Existing Event From Import Event URL
				if ($importedEvent->getIsDeleted()) {
					if (!$event->getIsDeleted()) {
						$eventRepo->delete($event);
					}
				} else {
					if ($event->setFromImportedEventModel($importedEvent) || $event->getIsDeleted()) {
						$event->setIsDeleted(false);
						$eventRepo->edit($event);
					}
				}
			} else {
				// New Event From Import Event URL
				$event = $this->newEventFromImportedEventModel($importedEvent);
				$eventRepo->create($event, $this->site, null, $this->group, null, $importedEvent);
			}
		}
	}

	/** @var models\EventModel **/
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


	protected function newEventFromImportedEventModel(ImportedEventModel $importedEvent) {

		$event = new EventModel();
		$event->setFromImportedEventModel($importedEvent);

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
			$crb->setLimit(1);
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

