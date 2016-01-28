<?php


namespace models;


use JMBTechnologyLimited\RRuleUnravel\ICalData;
use JMBTechnologyLimited\RRuleUnravel\Unraveler;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModel {

	public $id;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
	}
	
	protected $timeZoneName;
	/** @var EventModel **/
	protected $initalEvent;
	/** @var EventHistoryModel **/
	protected $initalEventLastChange;
	/** @var EventModel **/
	protected $initialEventJustBeforeLastChange;

	protected $futureEvents = array();
			
	protected $futureEventsProposedChanges = array();

	protected $customFields = array();

	public function getTimeZoneName() {
		return $this->timeZoneName;
	}

	public function setTimeZoneName($timeZoneName) {
		$this->timeZoneName = $timeZoneName;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getInitalEvent() {
		return $this->initalEvent;
	}

	public function setInitalEvent(EventModel $initalEvent) {
		$this->initalEvent = $initalEvent;
	}

	public function getInitalEventLastChange() {
		return $this->initalEventLastChange;
	}

	public function setInitalEventLastChange(EventHistoryModel $initalEventLastChange) {
		$this->initalEventLastChange = $initalEventLastChange;
	}

		
	public function getFutureEvents() {
		return $this->futureEvents;
	}

	public function setFutureEvents($futureEvents) {
		$this->futureEvents = $futureEvents;
	}

	/** @var EventInRecurSetProposedChangesModel **/
	public function getFutureEventsProposedChangesForEventSlug($slug) {
		return $this->futureEventsProposedChanges[$slug];
	}
	
	public function getFutureEventsProposedChanges() {
		return $this->futureEventsProposedChanges;
	}

	public function setFutureEventsProposedChanges($futureEventsProposedChanges) {
		$this->futureEventsProposedChanges = $futureEventsProposedChanges;
	}

	public function getInitialEventJustBeforeLastChange() {
		return $this->initialEventJustBeforeLastChange;
	}

	public function setInitialEventJustBeforeLastChange(EventModel $initialEventJustBeforeLastChange) {
		$this->initialEventJustBeforeLastChange = $initialEventJustBeforeLastChange;
	}

			
	
	public function getNewWeeklyEvents(EventModel $event,  $daysInAdvance = 93) {
        $untilDateTime = new \DateTime();
        $untilDateTime->setTimestamp(\TimeSource::time() + $daysInAdvance*24*60*60);

        $rruleunraveller = new Unraveler(new ICalData($event->getStartAtInUTC(), $event->getEndAtInUTC(), array("FREQ"=>"WEEKLY","UNTIL"=>$untilDateTime->format("Ymd")), $event->getTimezone()));
        // as well as the until clause above, we set a limit here for safety and robustness.
        $rruleunraveller->setResultsCountLimit(intval($daysInAdvance / 7) + 1);
        $rruleunraveller->setIncludeOriginalEvent(false);
        $rruleunraveller->process();
        $out = array();
        foreach($rruleunraveller->getResults() as $result) {
            $newEvent = new EventModel();
            $newEvent->setGroupId($event->getGroupId());
            $newEvent->setVenueId($event->getVenueId());
            $newEvent->setCountryId($event->getCountryId());
            $newEvent->setEventRecurSetId($this->id);
            $newEvent->setSummary($event->getSummary());
            $newEvent->setDescription($event->getDescription());
            $newEvent->setStartAt($result->getStartInUTC());
            $newEvent->setEndAt($result->getEndInUTC());
            foreach($this->customFields as $customField) {
                if ($event->hasCustomField($customField)) {
                    $newEvent->setCustomField($customField, $event->getCustomField($customField));
                }
            }

            $out[] = $newEvent;
        };
        return $out;
	}
	
	
	public function getEventPatternData(EventModel $event) {
		// constants
		$interval = new \DateInterval('P1D');
		$timeZone = new \DateTimeZone($this->timeZoneName);
		$timeZoneUTC = new \DateTimeZone("UTC");
		
		// calculate which day of month it should be
		$dayOfWeek = $event->getStartAt()->format("N");
		$thisStart = new \DateTime($event->getStartAt()->format('Y-m-d H:i:s'),$timeZoneUTC);
		$thisStart->setTimeZone($timeZone);
		$weekInMonth = 1;
		while($thisStart->format('d') > 1) {
			$thisStart->sub($interval);
			if ($thisStart->format("N") == $dayOfWeek) {
				++$weekInMonth;
			}
		}
		
		// is last day in month?
		$dayOfWeek = $event->getStartAt()->format("N");
		$month = $event->getStartAt()->format("n");
		$thisStart = new \DateTime($event->getStartAt()->format('Y-m-d H:i:s'),$timeZoneUTC);
		$thisStart->setTimeZone($timeZone);
		$isLastWeekInMonth = true;
		while($thisStart->format('n') == $month && $isLastWeekInMonth) {
			$thisStart->add($interval);
			if ($thisStart->format('n') == $month && $thisStart->format("N") == $dayOfWeek) {
				$isLastWeekInMonth = false;
			}
		}
		
		return array(
				'weekInMonth'=>$weekInMonth,
				'isLastWeekInMonth'=>$isLastWeekInMonth,
			);
		
	}

	
	public function getNewMonthlyEventsOnLastDayInWeek(EventModel $event,  $daysInAdvance = 186) {
        $dayOfWeek = substr(strtoupper($event->getStartAt()->format("l")),0,2);

        $untilDateTime = new \DateTime();
        $untilDateTime->setTimestamp(\TimeSource::time() + $daysInAdvance*24*60*60);

        $rruleunraveller = new Unraveler(new ICalData($event->getStartAtInUTC(), $event->getEndAtInUTC(), array("FREQ"=>"MONTHLY","BYDAY"=>"-1".$dayOfWeek,"UNTIL"=>$untilDateTime->format("Ymd")), $event->getTimezone()));
        // as well as the until clause above, we set a limit here for safety and robustness.
        $rruleunraveller->setResultsCountLimit(intval($daysInAdvance / 30) + 1);
        $rruleunraveller->setIncludeOriginalEvent(false);
        $rruleunraveller->process();
        $out = array();
        $currentMonthLong = $event->getStartAtInTimezone()->format('F');
        $currentMonthShort = $event->getStartAtInTimezone()->format('M');
        foreach($rruleunraveller->getResults() as $result) {
            $newEvent = new EventModel();
            $newEvent->setGroupId($event->getGroupId());
            $newEvent->setVenueId($event->getVenueId());
            $newEvent->setCountryId($event->getCountryId());
            $newEvent->setEventRecurSetId($this->id);
            $newEvent->setSummary($event->getSummary());
            $newEvent->setDescription($event->getDescription());
            $newEvent->setStartAt($result->getStartInUTC());
            $newEvent->setEndAt($result->getEndInUTC());
            foreach($this->customFields as $customField) {
                if ($event->hasCustomField($customField)) {
                    $newEvent->setCustomField($customField, $event->getCustomField($customField));
                }
            }
            if (stripos($newEvent->getSummary(),$currentMonthLong) !== false) {
                $newEvent->setSummary(str_ireplace($currentMonthLong, $newEvent->getStartAt()->format('F'), $newEvent->getSummary()));
            } else if (stripos($newEvent->getSummary(),$currentMonthShort) !== false) {
                $newEvent->setSummary(str_ireplace($currentMonthShort, $newEvent->getStartAt()->format('M'), $newEvent->getSummary()));
            }
            $out[] = $newEvent;
        };
        return $out;
	}

	/**
	 * 
	 * Gets new monthly events on the basis that the event is on the something day in the week.
	 * eg. 2nd tuesday of month 
	 * eg. 4th saturday in month
	 * 
	 * @param \models\EventModel $event
	 * @param type $monthsInAdvance
	 * @return \models\EventModel
	 */
	public function getNewMonthlyEventsOnSetDayInWeek(EventModel $event,  $daysInAdvance = 186) {
        $patternData = $this->getEventPatternData($event);
        $dayOfWeek = substr(strtoupper($event->getStartAt()->format("l")),0,2);

        $untilDateTime = new \DateTime();
        $untilDateTime->setTimestamp(\TimeSource::time() + $daysInAdvance*24*60*60);

        $rruleunraveller = new Unraveler(new ICalData($event->getStartAtInUTC(), $event->getEndAtInUTC(), array("FREQ"=>"MONTHLY","BYDAY"=>$patternData['weekInMonth'].$dayOfWeek,'UNTIL'=>$untilDateTime->format("Ymd")), $event->getTimezone()));
        // as well as the until clause above, we set a limit here for safety and robustness.
        $rruleunraveller->setResultsCountLimit(intval($daysInAdvance / 30) + 1);
        $rruleunraveller->setIncludeOriginalEvent(false);
        $rruleunraveller->process();
        $out = array();
        $currentMonthLong = $event->getStartAtInTimezone()->format('F');
        $currentMonthShort = $event->getStartAtInTimezone()->format('M');
        foreach($rruleunraveller->getResults() as $result) {
            $newEvent = new EventModel();
            $newEvent->setGroupId($event->getGroupId());
            $newEvent->setVenueId($event->getVenueId());
            $newEvent->setCountryId($event->getCountryId());
            $newEvent->setEventRecurSetId($this->id);
            $newEvent->setSummary($event->getSummary());
            $newEvent->setDescription($event->getDescription());
            $newEvent->setStartAt($result->getStartInUTC());
            $newEvent->setEndAt($result->getEndInUTC());
            foreach($this->customFields as $customField) {
                if ($event->hasCustomField($customField)) {
                    $newEvent->setCustomField($customField, $event->getCustomField($customField));
                }
            }
            if (stripos($newEvent->getSummary(),$currentMonthLong) !== false) {
                $newEvent->setSummary(str_ireplace($currentMonthLong, $newEvent->getStartAt()->format('F'), $newEvent->getSummary()));
            } else if (stripos($newEvent->getSummary(),$currentMonthShort) !== false) {
                $newEvent->setSummary(str_ireplace($currentMonthShort, $newEvent->getStartAt()->format('M'), $newEvent->getSummary()));
            }
            $out[] = $newEvent;
        };
        return $out;
	}

	public function isDateToSoonForArbitraryDate(\DateTime $newDate, \TimeSource $timeSource) {

		$now = $timeSource->getDateTime();

		// Add one day just to stop random errors with time.
		$now->add(new \DateInterval('P1D'));

		return $newDate < $now;
	}

	public function isDateToLateForArbitraryDate(\DateTime $newDate, \TimeSource $timeSource,  $daysInAdvance = 186) {

		$now = $timeSource->getDateTime();

		// Add one day just to stop random errors with time.
		$now->add(new \DateInterval('P'.$daysInAdvance.'D'));

		return $newDate > $now;
	}

	public function getNewEventOnArbitraryDate(EventModel $event, \DateTime $newDate) {

		$timeZoneUTC = new \DateTimeZone("UTC");
		$timeZone = new \DateTimeZone($this->timeZoneName);

		$start = clone $newDate;
		$start->setTimezone($timeZone);
		$start->setTime($event->getStartAtInTimezone()->format('G'), $event->getStartAtInTimezone()->format('i'), $event->getStartAtInTimezone()->format('s'));
		$start->setTimezone($timeZoneUTC);

		$end = clone $start;
		$end->add($event->getStartAtInUTC()->diff($event->getEndAtInUTC(), true));

		$newEvent = new EventModel();
		$newEvent->setGroupId($event->getGroupId());
		$newEvent->setVenueId($event->getVenueId());
		$newEvent->setCountryId($event->getCountryId());
		$newEvent->setEventRecurSetId($this->id);
		$newEvent->setSummary($event->getSummary());
		$newEvent->setDescription($event->getDescription());
		$newEvent->setStartAt($start);
		$newEvent->setEndAt($end);
		foreach($this->customFields as $customField) {
			if ($event->hasCustomField($customField)) {
				$newEvent->setCustomField($customField, $event->getCustomField($customField));
			}
		}

		return $newEvent;

	}


	/**
	 * This function takes a set of proposed new events in. It looks for any duplicate events already saved and filters them out.
	 * @return Array New proposed events where duplicates don't exist.
	 */
	public function filterEventsForExisting(EventModel $sourceEvent, $events) {
		
		$group = new GroupModel();
		$group->setId($sourceEvent->getGroupId());
		
		$out = array();
		
		foreach ($events as $event) {

			$erb = new EventRepositoryBuilder();
			$erb->setGroup($group);
			$erb->setStart($event->getStartAt());
			$erb->setEnd($event->getEndAt());
			
			$existingEvents = $erb->fetchAll();
			if (count($existingEvents) > 0) {
				
			} else {
				$out[] = $event;
			}
			
		}
		
		return $out;
		
	}
	
	public function getNewWeeklyEventsFilteredForExisting(EventModel $event,  $daysInAdvance = 93) {
		return $this->filterEventsForExisting($event, $this->getNewWeeklyEvents($event, $daysInAdvance));
	}
	
	public function getNewMonthlyEventsOnSetDayInWeekFilteredForExisting(EventModel $event,  $daysInAdvance = 186) {
		return $this->filterEventsForExisting($event, $this->getNewMonthlyEventsOnSetDayInWeek($event, $daysInAdvance));
	}
	
	public function getNewMonthlyEventsOnLastDayInWeekFilteredForExisting(EventModel $event,  $daysInAdvance = 186) {
		return $this->filterEventsForExisting($event, $this->getNewMonthlyEventsOnLastDayInWeek($event, $daysInAdvance));
	}

	public function getNewEventOnArbitraryDateFilteredForExisting(EventModel $event, \DateTime $newDate,  $daysInAdvance = 186) {
		$newEvent = $this->getNewEventOnArbitraryDate($event, $newDate);
		if ($newEvent) {
			return $this->filterEventsForExisting($event, array ( $newEvent ));
		}
	}

	/**
	 * @param array $customFields
	 */
	public function setCustomFields($customFields)
	{
		$this->customFields = $customFields;
	}



	public function applyChangeToFutureEvents() {
		$startDiff = $this->initalEvent->getStartAtInUTC()->diff($this->initialEventJustBeforeLastChange->getStartAtInUTC());
		$endDiff = $this->initalEvent->getEndAtInUTC()->diff($this->initialEventJustBeforeLastChange->getEndAtInUTC());
		foreach($this->futureEvents as $futureEvent) {
			$this->futureEventsProposedChanges[$futureEvent->getSlug()] = new EventInRecurSetProposedChangesModel();
			
			if (($this->initalEventLastChange->getCountryIdChanged() 
					|| $this->initalEventLastChange->getVenueIdChanged() 
					|| $this->initalEventLastChange->getAreaIdChanged()) && 
					($this->initalEvent->getCountryId() != $futureEvent->getCountryId() 
						|| $this->initalEvent->getAreaId() != $futureEvent->getAreaId() 
						|| $this->initalEvent->getVenueId() != $futureEvent->getVenueId())) {
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setCountryAreaVenueIdChangePossible(true);
				if ($this->initialEventJustBeforeLastChange->getCountryId() != $futureEvent->getCountryId() 
						|| $this->initialEventJustBeforeLastChange->getAreaId() != $futureEvent->getAreaId() 
						|| $this->initialEventJustBeforeLastChange->getVenueId() != $futureEvent->getVenueId()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setCountryAreaVenueIdChangeSelected(true);
				}
			}
			if ($this->initalEventLastChange->getSummaryChanged()) {
				$summary = $this->initalEvent->getSummary();
				// change month title
				$currentMonthLong = $this->initalEvent->getStartAtInUTC()->format('F');
				$currentMonthShort = $this->initalEvent->getStartAtInUTC()->format('M');	
				if (stripos($summary,$currentMonthLong) !== false) {
					$summary = str_ireplace($currentMonthLong, $futureEvent->getStartAtInUTC()->format('F'), $summary);
				} else if (stripos($summary,$currentMonthShort) !== false) {
					$summary = str_ireplace($currentMonthShort, $futureEvent->getStartAtInUTC()->format('M'), $summary);
				}
				if ($summary != $futureEvent->getSummary()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setSummary($summary);
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setSummaryChangePossible(true);
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setSummaryChangeSelected(true);
				}
			}
			if ($this->initalEventLastChange->getDescriptionChanged() && $this->initalEvent->getDescription() != $futureEvent->getDescription()) {
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setDescriptionChangePossible(true);
				if ($this->initialEventJustBeforeLastChange->getDescription() == $futureEvent->getDescription()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setDescriptionChangeSelected(true);
				}
			}
			if ($this->initalEventLastChange->getUrlChanged() && $this->initalEvent->getUrl() != $futureEvent->getUrl()) {
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setUrlChangePossible(true);
				if ($this->initialEventJustBeforeLastChange->getUrl() == $futureEvent->getUrl()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setUrlChangeSelected(true);
				}
			}
			if ($this->initalEventLastChange->getTicketUrlChanged() && $this->initalEvent->getTicketUrl() != $futureEvent->getTicketUrl()) {
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setTicketUrlChangePossible(true);
				if ($this->initialEventJustBeforeLastChange->getTicketUrl() == $futureEvent->getTicketUrl()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setTicketUrlChangeSelected(true);
				}
			}
			if ($this->initalEventLastChange->getTimezoneChanged() && $this->initalEvent->getTimezone() != $futureEvent->getTimezone()) {
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setTimezoneChangePossible(true);
				if ($this->initialEventJustBeforeLastChange->getTimezone() == $futureEvent->getTimezone()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setTimezoneChangeSelected(true);
				}
			}
			if ($this->initalEventLastChange->getIsPhysicalChanged() && $this->initalEvent->getIsPhysical() != $futureEvent->getIsPhysical()) {
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setIsPhysicalChangePossible(true);
				if ($this->initialEventJustBeforeLastChange->getIsPhysical() == $futureEvent->getIsPhysical()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setIsPhysicalChangeSelected(true);
				}
			}
			if ($this->initalEventLastChange->getIsVirtualChanged() && $this->initalEvent->getIsVirtual() != $futureEvent->getIsVirtual()) {
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setIsVirtualChangePossible(true);
				if ($this->initialEventJustBeforeLastChange->getIsVirtual() == $futureEvent->getIsVirtual()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setIsVirtualChangeSelected(true);
				}
			}
			if ($this->initalEventLastChange->getIsCancelledChanged() && $this->initalEvent->getIsCancelled() != $futureEvent->getIsCancelled()) {
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setIsCancelledChangePossible(true);
				if ($this->initialEventJustBeforeLastChange->getIsCancelled() == $futureEvent->getIsCancelled()) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setIsCancelledChangeSelected(true);
				}
			}
			if (($startDiff->y != 0 || $startDiff->m != 0 || $startDiff->d != 0  || $startDiff->h != 0  || $startDiff->i != 0  || $startDiff->s != 0 ) || 
					($endDiff->y != 0 || $endDiff->m != 0 || $endDiff->d != 0  || $endDiff->h != 0  || $endDiff->i != 0  || $endDiff->s != 0 )) {
				$start = clone $futureEvent->getStartAtInUTC();
				$start->sub($startDiff);
				$end = clone $futureEvent->getEndAtInUTC();
				$end->sub($endDiff);
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setStartEndAtChangePossible(true);
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setStartEndAtChangeSelected(true);
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setStartAt($start);
				$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setEndAt($end);
			}
			foreach ($this->customFields as $customField) {
				if ($this->initalEventLastChange->hasCustomField($customField) && $this->initalEvent->getCustomField($customField) != $futureEvent->getCustomField($customField)) {
					$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setCustomFieldChangePossible($customField, true);
					if ($this->initialEventJustBeforeLastChange->getCustomField($customField) == $futureEvent->getCustomField($customField)) {
						$this->futureEventsProposedChanges[$futureEvent->getSlug()]->setCustomFieldChangeSelected($customField, true);
					}
				}
			}
		}
	}
	
	public function isAnyProposedChangesPossible() {
		foreach($this->futureEventsProposedChanges as $proposedChange) {
			if ($proposedChange->isAnyChangesPossible()) {
				return true;
			}
		}
		return false;
	}
	
}


