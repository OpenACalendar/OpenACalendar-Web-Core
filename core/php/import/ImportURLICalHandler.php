<?php

namespace import;
use JMBTechnologyLimited\ICalDissect\ICalParser;
use JMBTechnologyLimited\ICalDissect\ICalEvent;
use repositories\EventRecurSetRepository;
use \TimeSource;
use models\ImportedEventModel;
use models\ImportURLResultModel;
use repositories\ImportedEventRepository;
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
		
	public function getSortOrder() {
		return 1000000;
	}
	
	protected $icalParser;
	

	protected $new;
	protected $existing;
	protected $saved;
	protected $inpast;
	protected $tofarinfuture;
	protected $notvalid;

	protected $addUIDCounter;

	protected $importedEventOccurrenceToEvent;

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
		
		$this->new = $this->existing = $this->saved = $this->inpast = $this->tofarinfuture = $this->notvalid = 0;
		
		$this->addUIDCounter = 1;

		$this->importedEventOccurrenceToEvent = new ImportedEventOccurrenceToEvent();
		$this->importedEventOccurrenceToEvent->setFromImportURlRun($this->importURLRun);
		
		foreach ($this->icalParser->getEvents() as $icalevent) {
			if ($this->importURLRun->hasFlag(ImportURLRun::$FLAG_ADD_UIDS) && !$icalevent->getUid()) {
				$icalevent->setUid("ADDEDBYIMPORTER".$this->addUIDCounter);
				++$this->addUIDCounter;
			}

			if ($icalevent->getStart() && $icalevent->getEnd() && $icalevent->getUid() && $icalevent->getStart()->getTimeStamp() <= $icalevent->getEnd()->getTimeStamp()) {
				$this->processICalEvent($icalevent);
			} else {
				$this->notvalid++;
			}
		}

		$iurlr = new ImportURLResultModel();
		$iurlr->setIsSuccess(true);
		$iurlr->setNewCount($this->new);
		$iurlr->setExistingCount($this->existing);
		$iurlr->setSavedCount($this->saved);
		$iurlr->setInPastCount($this->inpast);
		$iurlr->setToFarInFutureCount($this->tofarinfuture);
		$iurlr->setNotValidCount($this->notvalid);
		$iurlr->setMessage("ICAL Feed found");
		return $iurlr;	
	}


	protected function processICalEvent(ICalEvent $icalevent) {
		global $CONFIG;

		$importedEventRepo = new ImportedEventRepository();
		$eventRecurSetRepo = new EventRecurSetRepository();

		$importedEventChangesToSave = false;
		$importedEvent = $importedEventRepo->loadByImportURLIDAndImportId($this->importURLRun->getImportURL()->getId() ,$icalevent->getUid());

		if (!$importedEvent) {
			if (!$icalevent->isDeleted()) {
				$importedEvent = new ImportedEventModel();
				$importedEvent->setImportId($icalevent->getUid());
				$importedEvent->setImportUrlId($this->importURLRun->getImportURL()->getId());
				$this->setOurEventFromIcalEvent($importedEvent, $icalevent);
				$importedEventChangesToSave = true;
			}
		} else {
			if ($icalevent->isDeleted()) {
				if (!$importedEvent->getIsDeleted()) {
					$importedEvent->setIsDeleted(true);
					$importedEventChangesToSave = true;
				}
			} else {
				$importedEventChangesToSave = $this->setOurEventFromIcalEvent($importedEvent, $icalevent);
				// if was deleted, undelete
				if ($importedEvent->getIsDeleted()) {
					$importedEvent->setIsDeleted(false);
					$importedEventChangesToSave = true;
				}
			}
		}

		$ietieo = new ImportedEventToImportedEventOccurrences($importedEvent);

		if ($ietieo->getToMultiples()) {
			$eventRecurSet = $importedEvent != null ? $eventRecurSetRepo->getForImportedEvent($importedEvent) : null;
			$this->importedEventOccurrenceToEvent->setEventRecurSet($eventRecurSet, true);
		} else {
			$this->importedEventOccurrenceToEvent->setEventRecurSet(null, false);
		}

		foreach($ietieo->getImportedEventOccurrences() as $importedEventOccurrence) {

			if ($importedEventOccurrence->getEndAt()->getTimeStamp() < TimeSource::time()) {
				$this->inpast++;
			} else if ($importedEventOccurrence->getStartAt()->getTimeStamp() > TimeSource::time()+$CONFIG->importURLAllowEventsSecondsIntoFuture) {
				$this->tofarinfuture++;
			} else if ($this->saved < $this->limitToSaveOnEachRun) {

				// Imported Event
				if ($importedEventChangesToSave) {
					if ($importedEvent->getId()) {
						if ($importedEvent->getIsDeleted()) {
							$importedEventRepo->delete($importedEvent);
						} else {
							$importedEventRepo->edit($importedEvent);
						}
					} else {
						$importedEventRepo->create($importedEvent);
						// the ID will not be set until this point. So make sure we copy over the ID below just to be sure.
					}
					$importedEventChangesToSave = false;
				}

				// ... and the Imported Event Occurrence becomes a real event!
				$importedEventOccurrence->setId($importedEvent->getId());
				if ($this->importedEventOccurrenceToEvent->run($importedEventOccurrence)) {
					$this->saved++;
				}

			}

		}
	}

	protected function setOurEventFromIcalEvent(ImportedEventModel $importedEvent, ICalEvent $icalevent) {
		$changesToSave = false;
		if ($importedEvent->getDescription() != $icalevent->getDescription()) {
			$importedEvent->setDescription($icalevent->getDescription());
			$changesToSave = true;
		}
		if ($importedEvent->getTimezone() != $this->icalParser->getTimeZoneIdentifier()) {
			$importedEvent->setTimezone($this->icalParser->getTimeZoneIdentifier());
			$changesToSave = true;
		}
		if (!$importedEvent->getStartAt() || $importedEvent->getStartAt()->getTimeStamp() != $icalevent->getStart()->getTimeStamp()) {
			$importedEvent->setStartAt(clone $icalevent->getStart());
			$changesToSave = true;
		}
		if (!$importedEvent->getEndAt() || $importedEvent->getEndAt()->getTimeStamp() != $icalevent->getEnd()->getTimeStamp()) {
			$importedEvent->setEndAt(clone $icalevent->getEnd());
			$changesToSave = true;
		}
		if ($importedEvent->getTitle() != $icalevent->getSummary()) {
			$importedEvent->setTitle($icalevent->getSummary());
			$changesToSave = true;
		}
		if ($importedEvent->getUrl() != $icalevent->getUrl()) {
			$importedEvent->setUrl($icalevent->getUrl());
			$changesToSave = true;
		}
		if ($this->importURLRun->hasFlag(ImportURLRun::$FLAG_SET_TICKET_URL_AS_URL) && $importedEvent->getTicketUrl() != $icalevent->getUrl()) {
			$importedEvent->setTicketUrl($icalevent->getUrl());
			$changesToSave = true;
		}
		if ($icalevent->getRRuleCount() > 0 && $importedEvent->setIcsRrule1IfDifferent($icalevent->getRRule(0))) {
			$changesToSave = true;
		}
		return $changesToSave;
	}
	
	
}


