<?php

namespace import;
use icalparser\ICalParser;
use icalparser\ICalParserEvent;
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
		
		$importedEventRepo = new ImportedEventRepository();
		
		$importedEventsToEvents = new ImportedEventsToEvents();
		$importedEventsToEvents->setFromImportURlRun($this->importURLRun);
		
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
					//var_dump($icalevent);

					
					$importedEvent = $importedEventRepo->loadByImportURLIDAndImportId($this->importURLRun->getImportURL()->getId() ,$icalevent->getUid());
					
					$changesToSave = false;
					if (!$importedEvent) {
						if (!$icalevent->isDeleted()) {
							++$new;
							$importedEvent = new ImportedEventModel();						
							$importedEvent->setImportId($icalevent->getUid());
							$importedEvent->setImportUrlId($this->importURLRun->getImportURL()->getId());
							$this->setOurEventFromIcalEvent($importedEvent, $icalevent);							
							$changesToSave = true;
						}
					} else {
						++$existing;
						if ($icalevent->isDeleted()) {
							if (!$importedEvent->getIsDeleted()) {
								$importedEvent->setIsDeleted(true);
								$changesToSave = true;
							}
						} else {
							$changesToSave = $this->setOurEventFromIcalEvent($importedEvent, $icalevent);
							// if was deleted, undelete
							if ($importedEvent->getIsDeleted()) {
								$importedEvent->setIsDeleted(false);
								$changesToSave = true;
							}
						}
					}
					if ($changesToSave && $saved < $this->limitToSaveOnEachRun) {
						++$saved;
						
						if ($importedEvent->getId()) {
							if ($importedEvent->getIsDeleted()) {
								$importedEventRepo->delete($importedEvent);
							} else {
								$importedEventRepo->edit($importedEvent);
							}
						} else {
							$importedEventRepo->create($importedEvent);
						}
						$importedEventsToEvents->addImportedEvent($importedEvent);
						
					}					
				}
			} else {
				$notvalid++;
			}
		}
				
		// Now run the thing to make imported events real events!
		$importedEventsToEvents->run();
		
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

	protected function setOurEventFromIcalEvent(ImportedEventModel $importedEvent, ICalParserEvent $icalevent) {
		$changesToSave = false;
		if ($importedEvent->getDescription() != $icalevent->getDescription()) {
			$importedEvent->setDescription($icalevent->getDescription());
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
		return $changesToSave;
	}
	
	
}


