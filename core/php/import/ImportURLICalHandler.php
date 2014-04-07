<?php

namespace import;
use icalparser\ICalParser;
use icalparser\ICalParserEvent;
use \TimeSource;
use models\EventModel;
use models\ImportURLResultModel;
use repositories\EventRepository;
use repositories\ImportURLResultRepository;



/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLICalHandler extends ImportURLHandlerBase {
	
	protected $icalParser;
	


	public function canHandle() {
	
		$fileName = $this->importURLRun->downloadURLreturnFileName();
	
		$this->icalParser = new ICalParser();
		if ($this->icalParser->parseFromFile($fileName)) {
			return true;
		}
		
		return false;
	}
	
	public function handle() {
		global $CONFIG;
		
		$new = $existing = $saved = $inpast = $tofarinfuture = $notvalid = 0;
		
		$addUIDCounter = 1;
		
		$eventRepo = new EventRepository();
		
		foreach ($this->icalParser->getEvents() as $icalevent) {
			if ($this->importURLRun->hasFlag(ImportURLRun::$FLAG_ADD_UIDS) && !$icalevent->getUid()) {
				$icalevent->setUid("ADDEDBYIMPORTER".$addUIDCounter);
				++$addUIDCounter;
			}

			if ($icalevent->getStart() && $icalevent->getEnd() && $icalevent->getUid() && $icalevent->getStart()->getTimeStamp() <= $icalevent->getEnd()->getTimeStamp()) { 
				if ($icalevent->getEnd()->getTimeStamp() < TimeSource::time()) {
					$inpast++;
				} else if ($icalevent->getStart()->getTimeStamp() > TimeSource::time()+$CONFIG->importURLAllowEventsSecondsIntoFuture) {
					$tofarinfuture++;
				} else {
					//var_dump($data);

					
					$event = $eventRepo->loadByImportURLIDAndImportId($this->importURLRun->getImportURL()->getId() ,$icalevent->getUid());
					
					$changesToSave = false;
					if (!$event) {
						if (!$icalevent->isDeleted()) {
							++$new;
							$event = new EventModel();						
							$event->setImportId($icalevent->getUid());
							$event->setImportUrlId($this->importURLRun->getImportURL()->getId());
							$this->setOurNewEventFromIcalEvent($event, $icalevent);							
							$this->setOurEventFromIcalEvent($event, $icalevent);							
							$changesToSave = true;
						}
					} else {
						++$existing;
						if ($icalevent->isDeleted()) {
							if (!$event->getIsDeleted()) {
								$event->setIsDeleted(true);
								$changesToSave = true;
							}
						} else {
							$changesToSave = $this->setOurEventFromIcalEvent($event, $icalevent);
							// if was deleted, undelete
							if ($event->getIsDeleted()) {
								$event->setIsDeleted(false);
								$changesToSave = true;
							}
						}
					}
					if ($changesToSave && $saved < $this->limitToSaveOnEachRun) {
						++$saved;
						
						if ($event->getId()) {
							if ($event->getIsDeleted()) {
								$eventRepo->delete($event, null);
							} else {
								$eventRepo->edit($event, null);
							}
						} else {
							$eventRepo->create($event, $this->importURLRun->getSite(), null, $this->importURLRun->getGroup());
						}
						
					}
				}
			} else {
				$notvalid++;
			}
		}
				
		
		$iurlr = new ImportURLResultModel();
		$iurlr->setImportUrlId($this->importURLRun->getimportURL()->getId());
		$iurlr->setIsSuccess(true);
		$iurlr->setNewCount($new);
		$iurlr->setExistingCount($existing);
		$iurlr->setSavedCount($saved);
		$iurlr->setInPastCount($inpast);
		$iurlr->setToFarInFutureCount($tofarinfuture);
		$iurlr->setNotValidCount($notvalid);
		$iurlr->setMessage("ICAL Feed found");
		$iurlrRepo = new ImportURLResultRepository();
		$iurlrRepo->create($iurlr);	
	}

	protected function setOurEventFromIcalEvent(EventModel $event, ICalParserEvent $icalevent) {
		$changesToSave = false;
		if ($event->getDescription() != $icalevent->getDescription()) {
			$event->setDescription($icalevent->getDescription());
			$changesToSave = true;
		}
		if (!$event->getStartAt() || $event->getStartAt()->getTimeStamp() != $icalevent->getStart()->getTimeStamp()) {
			$event->setStartAt(clone $icalevent->getStart());
			$changesToSave = true;
		}
		if (!$event->getEndAt() || $event->getEndAt()->getTimeStamp() != $icalevent->getEnd()->getTimeStamp()) {
			$event->setEndAt(clone $icalevent->getEnd());
			$changesToSave = true;
		}
		if ($event->getSummary() != $icalevent->getSummary()) {
			$event->setSummary($icalevent->getSummary());
			$changesToSave = true;
		}
		if ($event->getUrl() != $icalevent->getUrl()) {
			$event->setUrl($icalevent->getUrl());
			$changesToSave = true;
		}
		return $changesToSave;
	}
	
	/**
	 * This is run only once on creation. We try to guess sensible defaults here for things we aren't sure about.
	 * @param \models\EventModel $event
	 * @param \icalparser\ICalParserEvent $icalevent
	 */
	protected function setOurNewEventFromIcalEvent(EventModel $event, ICalParserEvent $icalevent) {
		if ($this->importURLRun->getSite()->getIsFeaturePhysicalEvents() && !$this->importURLRun->getSite()->getIsFeatureVirtualEvents()) {
			$event->setIsPhysical(true);
			$event->setIsVirtual(false);
		} else if (!$this->importURLRun->getSite()->getIsFeaturePhysicalEvents() && $this->importURLRun->getSite()->getIsFeatureVirtualEvents()) {
			$event->setIsPhysical(false);
			$event->setIsVirtual(true);
		}				
		
		if ($this->importURLRun->getCountry()) {
			
			// country is set on importer.
			$event->setCountryId($this->importURLRun->getCountry()->getId());
			// take first timezone in that country at random :-/
			$timezones = $this->importURLRun->getCountry()->getTimezonesAsList();
			if ($timezones) {
				$event->setTimezone($timezones[0]);
			}
			
		} else {
		
			// if no country set on importer, we just pick first one at random :-/
			$crb = new \repositories\builders\CountryRepositoryBuilder();
			$crb->setSiteIn($this->importURLRun->getSite());
			$countries = $crb->fetchAll();
			if (count($countries) > 0) {
				$country = $countries[0];
				$event->setCountryId($country->getId());
				// take first timezone in that country at random :-/
				$timezones = $country->getTimezonesAsList();
				if ($timezones) {
					$event->setTimezone($timezones[0]);
				}
			}
		}
		
	}
}


