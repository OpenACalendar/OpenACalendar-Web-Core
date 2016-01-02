<?php
namespace import;


use models\ImportModel;
use models\ImportResultModel;
use repositories\builders\ImportedEventRepositoryBuilder;
use repositories\EventRecurSetRepository;
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
class ImportRunner {

    /** @var Application */
    protected $app;

    function __construct($app)
    {
        $this->app = $app;
    }

    public function go(ImportModel $importModel)
    {
        $importRun = new ImportRun($importModel);
        if ($this->runHandlersSaveResult($importRun)) {
            $this->runImportedEventsToEvents($importRun);
        }
    }

    protected function runHandlersSaveResult(ImportRun $importRun) {

		$iurlrRepo = new ImportResultRepository();
		$handlers = array();
		
		// Get
		foreach($this->app['extensions']->getExtensionsIncludingCore() as $extension) {
			foreach($extension->getImportHandlers() as $handler) {
				$handlers[] = $handler;
			}
		}
		
		// Sort
		usort($handlers, function($a, $b) {
			if ($a->getSortOrder() == $b->getSortOrder()) {
				return 0;
			} else if ($a->getSortOrder() > $b->getSortOrder()) {
				return 1;
			} else if ($a->getSortOrder() < $b->getSortOrder()) {
				return -1;
			}
		});

		// Run
		foreach($handlers as $handler) {
			$handler->setImportRun($importRun);
			if ($handler->canHandle()) {
				if ($handler->isStopAfterHandling()) {
					$iurlr = $handler->handle();
					$iurlr->setImportId($importRun->getImport()->getId());
					$iurlrRepo->create($iurlr);
					return $iurlr->getIsSuccess();
				} else {
					$handler->handle();
				}
			}
		}

        // Log that couldn't handle feed
        $iurlr = new ImportResultModel();
        $iurlr->setImportId($importRun->getImport()->getId());
        $iurlr->setIsSuccess(false);
        $iurlr->setMessage("Did not recognise data");
        $iurlrRepo->create($iurlr);
        return false;
    }

    protected function runImportedEventsToEvents(ImportRun $importRun) {
        $eventRecurSetRepo = new EventRecurSetRepository();
        $importedEventRepo = new ImportedEventRepository();

        $importedEventOccurrenceToEvent = new ImportedEventOccurrenceToEvent($importRun);
        $saved = 0;

        $importedEventsRepo = new ImportedEventRepositoryBuilder();
        $importedEventsRepo->setImport($importRun->getImport());
        $importedEventsRepo->setIncludeDeleted(true);
        foreach($importedEventsRepo->fetchAll() as $importedEvent) {

            if (!$importRun->wasImportedEventSeen($importedEvent)) {
                // So we have this event in our store, but it wasn't seen in the last import. Mark it deleted!
                if (!$importedEvent->getIsDeleted()) {
                    $importedEvent->setIsDeleted(true);
                    $importedEventRepo->delete($importedEvent);
                }
            }

            $importedEventToImportedEventOccurrences = new ImportedEventToImportedEventOccurrences($this->app, $importedEvent);

            if ($importedEventToImportedEventOccurrences->getToMultiples()) {
                $eventRecurSet = $importedEvent != null ? $eventRecurSetRepo->getForImportedEvent($importedEvent) : null;
                $importedEventOccurrenceToEvent->setEventRecurSet($eventRecurSet, true);
            } else {
                $importedEventOccurrenceToEvent->setEventRecurSet(null, false);
            }

            foreach($importedEventToImportedEventOccurrences->getImportedEventOccurrences() as $importedEventOccurrence) {
                if ($importedEventOccurrence->getEndAt()->getTimeStamp() < $this->app['timesource']->time()) {
                    // TODO log this somewhere?
                } else if ($importedEventOccurrence->getStartAt()->getTimeStamp() > $this->app['timesource']->time()+$this->app['config']->importAllowEventsSecondsIntoFuture) {
                    // TODO log this somewhere?
                } else if ($saved < $this->app['config']->importLimitToSaveOnEachRunEvents) {
                    if ($importedEventOccurrenceToEvent->run($importedEventOccurrence)) {
                        $saved++;
                    }
                }
            }

        }

        $importedEventOccurrenceToEvent->deleteEventsNotSeenAfterRun();

    }

}


