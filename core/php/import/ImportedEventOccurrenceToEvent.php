<?php


namespace import;

use actions\GetAreaForLatLng;
use models\EventRecurSetModel;
use models\ImportedEventModel;
use models\ImportedEventOccurrenceModel;
use models\ImportModel;
use models\SiteModel;
use models\GroupModel;
use models\CountryModel;
use models\AreaModel;
use models\EventModel;
use repositories\builders\EventRepositoryBuilder;
use repositories\EventRecurSetRepository;
use repositories\EventRepository;
use repositories\ImportedEventIsEventRepository;
use repositories\SiteFeatureRepository;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventOccurrenceToEvent {

    /** @var  Application */
    protected $app;

	/** @var  ImportModel */
	protected $import;

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

    protected $siteFeaturePhysicalEvents = false;
    protected $siteFeatureVirtualEvents = false;

    /** @var  GetAreaForLatLng */
    protected $getAreaForLatLng;

	public function __construct(Application $app, ImportRun $importRun) {
        $this->app = $app;
		$this->site = $importRun->getSite();
		$this->group = $importRun->getGroup();
		$this->country = $importRun->getCountry();
		$this->area = $importRun->getArea();
		$this->import = $importRun->getImport();
		$this->eventsSeenIDs = array();
        $siteFeatureRepo = new SiteFeatureRepository($app);
        $this->siteFeaturePhysicalEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($this->site,'org.openacalendar','PhysicalEvents');
        $this->siteFeatureVirtualEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($this->site,'org.openacalendar','VirtualEvents');
        $this->getAreaForLatLng = new GetAreaForLatLng($app, $importRun->getSite());
	}

	public function setEventRecurSet(EventRecurSetModel $eventRecurSet = null, $makeEventRecurSetIfNone = false) {
		$this->eventRecurSet = $eventRecurSet;
		$this->makeEventRecurSetIfNone = $makeEventRecurSetIfNone;
	}

	public function run(ImportedEventOccurrenceModel $importedEventOccurrenceModel) {

		$eventRepo = new EventRepository($this->app);
		$eventRecurSetRepo = new EventRecurSetRepository($this->app);

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
			if (!$this->import->getIsManualEventsCreation()) {
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
        $eventRepo = new EventRepository($this->app);
        $erb = new EventRepositoryBuilder($this->app);
        $erb->setImport($this->import);
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
		$eventRepo = new \repositories\EventRepository($this->app);

		// Try new way
		$event = $eventRepo->loadByImportedEvent($importedEvent);
		if ($event) {
			return $event;
		}

		// Try old way - flags on event table - and if found, set data for new way
		$event = $eventRepo->loadByImportURLIDAndImportId($importedEvent->getImportId(), $importedEvent->getIdInImport());
		if ($event) {
			// Save this data as the new way
			$repo = new ImportedEventIsEventRepository($this->app);
			$repo->createLink($importedEvent, $event);
			// .... and return
			return $event;
		}

		// Give up
		return null;
	}


	protected function loadEventForImportedReoccurredEvent(ImportedEventModel $importedEvent) {
		$erb = new EventRepositoryBuilder($this->app);
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

		if ($this->siteFeaturePhysicalEvents && !$this->siteFeatureVirtualEvents) {
			$event->setIsPhysical(true);
			$event->setIsVirtual(false);
		} else if (!$this->siteFeaturePhysicalEvents && $this->siteFeatureVirtualEvents) {
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

            // If no area set but we have lat & lng, try and get area from that.
            // TODO even if areas set, could look for sub areas?
            if (!$event->getAreaId() && $importedEvent->getLat()) {
                $area = $this->getAreaForLatLng->getArea($importedEvent->getLat(), $importedEvent->getLng(), $this->country);
                if ($area) {
                    $event->setAreaId($area->getId());
                }
            }


		} else {

			// if no country set on importer, we just pick first one at random :-/
			$crb = new \repositories\builders\CountryRepositoryBuilder($this->app);
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
