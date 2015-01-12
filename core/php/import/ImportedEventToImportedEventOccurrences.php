<?php

namespace import;

use JMBTechnologyLimited\RRuleUnravel\ICalData;
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

	protected $toMultiples;

	function __construct(ImportedEventModel $importedEvent)
	{

		if ($importedEvent->getIcsRrule1()) {


			$icaldata = new ICalData(
				clone $importedEvent->getStartAt(),
				clone $importedEvent->getEndAt(),
				$importedEvent->getIcsRrule1(),
				$importedEvent->getTimezone());
			$unraveler = new Unraveler($icaldata);
			$unraveler->setIncludeOriginalEvent(true);
			$unraveler->process();
			$results = $unraveler->getResults();

			foreach($results as $wantedTimes) {
				$newImportedOccurrenceEvent = New ImportedEventOccurrenceModel();
				$newImportedOccurrenceEvent->setFromImportedEventModel($importedEvent);
				$newImportedOccurrenceEvent->setStartAt($wantedTimes->getStart());
				$newImportedOccurrenceEvent->setEndAt($wantedTimes->getEnd());
				$this->importedEventOccurrences[] = $newImportedOccurrenceEvent;
			}

			$this->toMultiples = true;

		} else {

			// If not a reoccuring event, there will still be 1 occurence .....
			$newImportedOccurrenceEvent = New ImportedEventOccurrenceModel();
			$newImportedOccurrenceEvent->setFromImportedEventModel($importedEvent);
			$this->importedEventOccurrences[] = $newImportedOccurrenceEvent;

			$this->toMultiples = false;

		}

	}

	/**
	 * @return array
	 */
	public function getImportedEventOccurrences()
	{
		return $this->importedEventOccurrences;
	}

	/**
	 * @return boolean
	 */
	public function getToMultiples()
	{
		return $this->toMultiples;
	}



}
