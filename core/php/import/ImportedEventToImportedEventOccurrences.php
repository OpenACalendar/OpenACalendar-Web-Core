<?php

namespace import;

use JMBTechnologyLimited\RRuleUnravel\ICalData;
use JMBTechnologyLimited\RRuleUnravel\ResultFilterAfterDateTime;
use JMBTechnologyLimited\RRuleUnravel\ResultFilterBeforeDateTime;
use JMBTechnologyLimited\RRuleUnravel\Unraveler;
use models\ImportedEventModel;
use models\ImportedEventOccurrenceModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventToImportedEventOccurrences {


	protected $importedEventOccurrences = array();

	protected $toMultiples;

	function __construct($app, ImportedEventModel $importedEvent)
	{

		$reoccur = $importedEvent->getReoccur();

		if (isset($reoccur) && isset($reoccur['ical_rrule']) && $reoccur['ical_rrule']) {

			$icaldata = new ICalData(
				clone $importedEvent->getStartAt(),
				clone $importedEvent->getEndAt(),
				$reoccur['ical_rrule'],
				$importedEvent->getTimezone());
			if (isset($reoccur['ical_exdates']) && is_array($reoccur['ical_exdates'])) {
				foreach($reoccur['ical_exdates'] as $exdate) {
					$icaldata->addExDateByString($exdate['values'], $exdate['properties']);
				}
			}

			$unraveler = new Unraveler($icaldata);
			$unraveler->setIncludeOriginalEvent(true);
            $unraveler->addResultFilter(new ResultFilterAfterDateTime($app['timesource']->getDateTime()));
            $toEnd = $app['timesource']->getDateTime();
            $toEnd->setTimestamp($toEnd->getTimestamp() + $app['config']->importURLAllowEventsSecondsIntoFuture);
            $unraveler->addResultFilter(new ResultFilterBeforeDateTime($toEnd));
            $unraveler->setResultsCountLimit(max( $app['config']->importLimitToSaveOnEachRunImportedEvents, $app['config']->importLimitToSaveOnEachRunEvents ));
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
