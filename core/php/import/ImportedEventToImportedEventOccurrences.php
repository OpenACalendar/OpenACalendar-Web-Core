<?php

namespace import;

use JMBTechnologyLimited\RRuleUnravel\RRule;
use JMBTechnologyLimited\RRuleUnravel\Unraveler;
use models\ImportedEventModel;
use models\ImportedEventOccurrenceModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventToImportedEventOccurrences {


	protected $importedEventOccurrences = array();


	function __construct(ImportedEventModel $importedEvent)
	{

		if ($importedEvent->getIcsRrule1()) {


			$rrule = new RRule($importedEvent->getIcsRrule1());
			$unraveler = new Unraveler($rrule, clone $importedEvent->getStartAt(), clone $importedEvent->getEndAt(), $importedEvent->getTimezone());
			// TODO set unraveler, include original event option
			$unraveler->process();
			$results = $unraveler->getResults();

			// include original event ourselves
			$newImportedOccurrenceEvent = New ImportedEventOccurrenceModel();
			$newImportedOccurrenceEvent->setFromImportedEventModel($importedEvent);
			$this->importedEventOccurrences[] = $newImportedOccurrenceEvent;

			foreach($results as $wantedTimes) {
				$newImportedOccurrenceEvent = New ImportedEventOccurrenceModel();
				$newImportedOccurrenceEvent->setFromImportedEventModel($importedEvent);
				$newImportedOccurrenceEvent->setStartAt($wantedTimes->getStart());
				$newImportedOccurrenceEvent->setEndAt($wantedTimes->getEnd());
				$this->importedEventOccurrences[] = $newImportedOccurrenceEvent;
			}

		} else {

			// If not a reoccuring event, there will still be 1 occurence .....
			$newImportedOccurrenceEvent = New ImportedEventOccurrenceModel();
			$newImportedOccurrenceEvent->setFromImportedEventModel($importedEvent);
			$this->importedEventOccurrences[] = $newImportedOccurrenceEvent;

		}

	}

	/**
	 * @return array
	 */
	public function getImportedEventOccurrences()
	{
		return $this->importedEventOccurrences;
	}

}
