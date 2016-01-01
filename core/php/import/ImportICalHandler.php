<?php

namespace import;
use JMBTechnologyLimited\ICalDissect\ICalParser;
use JMBTechnologyLimited\ICalDissect\ICalEvent;
use repositories\EventRecurSetRepository;
use \TimeSource;
use models\ImportedEventModel;
use models\ImportResultModel;
use repositories\ImportedEventRepository;
use repositories\ImportResultRepository;



/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportICalHandler extends ImportHandlerBase {
		
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
	
		$fileName = $this->importRun->downloadURLreturnFileName();
	
		$this->icalParser = new ICalParser();
		if ($this->icalParser->parseFromFile($fileName)) {
			return true;
		}
		
		return false;
	}
	
	public function handle() {

		$this->new = $this->existing = $this->saved = $this->inpast = $this->tofarinfuture = $this->notvalid = 0;
		
		$this->addUIDCounter = 1;

		foreach ($this->icalParser->getEvents() as $icalevent) {
			if ($this->importRun->hasFlag(ImportRun::$FLAG_ADD_UIDS) && !$icalevent->getUid()) {
				$icalevent->setUid("ADDEDBYIMPORTER".$this->addUIDCounter);
				++$this->addUIDCounter;
			}

			if ($icalevent->getStart() && $icalevent->getEnd() && $icalevent->getUid() && $icalevent->getStart()->getTimeStamp() <= $icalevent->getEnd()->getTimeStamp()) {
				$this->processICalEvent($icalevent);
			} else {
				$this->notvalid++;
			}
		}

		$iurlr = new ImportResultModel();
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

		$importedEventRepo = new ImportedEventRepository();

		$importedEventChangesToSave = false;
		$importedEvent = $importedEventRepo->loadByImportURLIDAndImportId($this->importRun->getImport()->getId() ,$icalevent->getUid());

		if (!$importedEvent) {
			if (!$icalevent->isDeleted()) {
				$importedEvent = new ImportedEventModel();
				$importedEvent->setImportId($icalevent->getUid());
				$importedEvent->setImportUrlId($this->importRun->getImport()->getId());
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

        if ($importedEventChangesToSave && ($this->existing + $this->new) < $this->app['config']->importLimitToSaveOnEachRunImportedEvents) {
            if ($importedEvent->getId()) {
                if ($importedEvent->getIsDeleted()) {
                    $importedEventRepo->delete($importedEvent);
                } else {
                    $importedEventRepo->edit($importedEvent);
                    $this->existing++;
                }
            } else {
                $importedEventRepo->create($importedEvent);
                $this->new++;
            }
        }

        $this->importRun->markImportedEventSeen($importedEvent);
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
		if ($this->importRun->hasFlag(ImportRun::$FLAG_SET_TICKET_URL_AS_URL) && $importedEvent->getTicketUrl() != $icalevent->getUrl()) {
			$importedEvent->setTicketUrl($icalevent->getUrl());
			$changesToSave = true;
		}
		if ($icalevent->getRRule()) {
			$reoccur = array('ical_rrule'=>$icalevent->getRRule(),'ical_exdates'=>array());
			foreach($icalevent->getExDates() as $exDate) {
				$reoccur['ical_exdates'][] = array('properties'=>$exDate->getProperties(), 'values'=>$exDate->getValues());
			}
		} else {
			$reoccur = array();
		}
		if ($importedEvent->setReoccurIfDifferent($reoccur)) {
			$changesToSave = true;
		}
		return $changesToSave;
	}
	
	
}


