<?php

namespace import;

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

            // Include Original
            $newImportedOccurrenceEvent = New ImportedEventOccurrenceModel();
            $newImportedOccurrenceEvent->setFromImportedEventModel($importedEvent);
            $newImportedOccurrenceEvent->setStartAt($importedEvent->getStartAt());
            $newImportedOccurrenceEvent->setEndAt($importedEvent->getEndAt());
            $this->importedEventOccurrences[] = $newImportedOccurrenceEvent;

            // New get rest .....
            $start = clone $importedEvent->getStartAt();
            $start->setTimezone(new \DateTimeZone($importedEvent->getTimezone()));
            $end = clone $importedEvent->getEndAt();
            $end->setTimezone(new \DateTimeZone($importedEvent->getTimezone()));
            $rule  = new \Recurr\Rule($reoccur['ical_rrule'], $start, $end, $importedEvent->getTimezone());
            $transformerConfig = new \Recurr\Transformer\ArrayTransformerConfig();
            $transformerConfig->setVirtualLimit( max( $app['config']->importLimitToSaveOnEachRunImportedEvents, $app['config']->importLimitToSaveOnEachRunEvents )  );
            $transformer = new \Recurr\Transformer\ArrayTransformer($transformerConfig);

            $toEnd = $app['timesource']->getDateTime();
            $toEnd->setTimestamp($toEnd->getTimestamp() + $app['config']->importAllowEventsSecondsIntoFuture);
            $constraint = new \Recurr\Transformer\Constraint\BetweenConstraint($app['timesource']->getDateTime(), $toEnd, true);

            foreach($transformer->transform($rule, $constraint) as $wantedTimes) {
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
