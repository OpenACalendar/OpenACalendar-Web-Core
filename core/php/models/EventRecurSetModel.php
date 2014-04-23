<?php


namespace models;


use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModel {

	public $id;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
	}
	
	protected $timeZoneName;
	
	public function getTimeZoneName() {
		return $this->timeZoneName;
	}

	public function setTimeZoneName($timeZoneName) {
		$this->timeZoneName = $timeZoneName;
	}

		
	public function getNewWeeklyEvents(EventModel $event,  $monthsInAdvance = 3) {
		// constants
		$interval = new \DateInterval('P1D');
		$timeZone = new \DateTimeZone($this->timeZoneName);
		$timeZoneUTC = new \DateTimeZone("UTC");
		// vars
		$dayOfWeek = $event->getStartAt()->format("N");
		$thisStart = new \DateTime($event->getStartAt()->format('Y-m-d H:i:s'),$timeZoneUTC);
		$thisEnd = new \DateTime($event->getEndAt()->format('Y-m-d H:i:s'),$timeZoneUTC);
		$thisStart->setTimeZone($timeZone);
		$thisEnd->setTimeZone($timeZone);
		$out = array();
		$loopStop = (\TimeSource::time() + $monthsInAdvance*30*24*60*60);
		while ( $thisStart->getTimestamp() < $loopStop) {
			$thisStart->add($interval);
			$thisEnd->add($interval);
			if ($thisStart->format("N") == $dayOfWeek && $thisStart->getTimestamp() > \TimeSource::time()) {
				
				$start = clone $thisStart;
				$end = clone $thisEnd;
				$start->setTimeZone($timeZoneUTC);
				$end->setTimeZone($timeZoneUTC);
				
				$include = true;
				
				
				if ($include) {
					$newEvent = new EventModel();
					$newEvent->setGroupId($event->getGroupId());
					$newEvent->setVenueId($event->getVenueId());
					$newEvent->setCountryId($event->getCountryId());
					$newEvent->setEventRecurSetId($this->id);
					$newEvent->setSummary($event->getSummary());
					$newEvent->setDescription($event->getDescription());
					$newEvent->setStartAt($start);
					$newEvent->setEndAt($end);
					
					$out[] = $newEvent;
				}
			}
		}
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

	
	public function getNewMonthlyEventsOnLastDayInWeek(EventModel $event,  $monthsInAdvance = 6) {
		// constants
		$interval = new \DateInterval('P1D');
		$timeZone = new \DateTimeZone($this->timeZoneName);
		$timeZoneUTC = new \DateTimeZone("UTC");
		
		// calculate which day of month it should be
		$dayOfWeek = $event->getStartAt()->format("N");
		$thisStart = new \DateTime($event->getStartAt()->format('Y-m-d H:i:s'),$timeZoneUTC);
		$thisEnd = new \DateTime($event->getEndAt()->format('Y-m-d H:i:s'),$timeZoneUTC);
		$thisStart->setTimeZone($timeZone);
		$thisEnd->setTimeZone($timeZone);
		while($thisStart->format('d') != 1) {
			$thisStart->add($interval);
			$thisEnd->add($interval);
		}
				
		// vars		
		$out = array();
		$currentMonthLong = $thisStart->format('F');
		$currentMonthShort = $thisStart->format('M');		
		$currentMonth = $thisStart->format('m');
		$loopStop = \TimeSource::time() + $monthsInAdvance*30*24*60*60;
		$startInMonth = null;
		$endInMonth = null;
		while ( $thisStart->getTimestamp() < $loopStop) {
			$thisStart->add($interval);
			$thisEnd->add($interval);
			//print $thisStart->format("r")."  current month: ".$currentMonth." current week: ".$currentWeekInMonth."\n";
			if ($currentMonth != $thisStart->format('m')) {
				$currentMonth = $thisStart->format('m');
				
				$startInMonth->setTimeZone($timeZoneUTC);
				$endInMonth->setTimeZone($timeZoneUTC);

				$include = true;

				if ($include) {
					$newEvent = new EventModel();
					$newEvent->setGroupId($event->getGroupId());
					$newEvent->setVenueId($event->getVenueId());
					$newEvent->setCountryId($event->getCountryId());
					$newEvent->setEventRecurSetId($this->id);
					$newEvent->setSummary($event->getSummary());
					$newEvent->setDescription($event->getDescription());
					$newEvent->setStartAt($startInMonth);
					$newEvent->setEndAt($endInMonth);

					if (stripos($newEvent->getSummary(),$currentMonthLong) !== false) {
						$newEvent->setSummary(str_ireplace($currentMonthLong, $newEvent->getStartAt()->format('F'), $newEvent->getSummary()));
					} else if (stripos($newEvent->getSummary(),$currentMonthShort) !== false) {
						$newEvent->setSummary(str_ireplace($currentMonthShort, $newEvent->getStartAt()->format('M'), $newEvent->getSummary()));
					}

					$out[] = $newEvent;
				}
				
			}
			if ($thisStart->format("N") == $dayOfWeek) {
				$startInMonth = clone $thisStart;
				$endInMonth = clone $thisEnd;
			}			
		}
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
	public function getNewMonthlyEventsOnSetDayInWeek(EventModel $event,  $monthsInAdvance = 6) {
		// constants
		$interval = new \DateInterval('P1D');
		$timeZone = new \DateTimeZone($this->timeZoneName);
		$timeZoneUTC = new \DateTimeZone("UTC");
		
		// calculate which day of month it should be
		$dayOfWeek = $event->getStartAt()->format("N");
		$thisStart = new \DateTime($event->getStartAt()->format('Y-m-d H:i:s'),$timeZoneUTC);
		$thisEnd = new \DateTime($event->getEndAt()->format('Y-m-d H:i:s'),$timeZoneUTC);
		$thisStart->setTimeZone($timeZone);
		$thisEnd->setTimeZone($timeZone);
		$weekInMonth = 1;
		while($thisStart->format('d') > 1) {
			$thisStart->sub($interval);
			$thisEnd->sub($interval);
			if ($thisStart->format("N") == $dayOfWeek) ++$weekInMonth;
		}
				
		// vars		
		$out = array();
		$currentMonthLong = $thisStart->format('F');
		$currentMonthShort = $thisStart->format('M');		
		$currentMonth = $thisStart->format('m');
		$currentWeekInMonth = 1;
		$loopStop = \TimeSource::time() + $monthsInAdvance*30*24*60*60;
		while ( $thisStart->getTimestamp() < $loopStop) {
			$thisStart->add($interval);
			$thisEnd->add($interval);
			//print $thisStart->format("r")."  current month: ".$currentMonth." current week: ".$currentWeekInMonth."\n";
			if ($currentMonth != $thisStart->format('m')) {
				$currentMonth = $thisStart->format('m');
				$currentWeekInMonth = 1;
			}
			if ($thisStart->format("N") == $dayOfWeek) {

				if ($currentWeekInMonth == $weekInMonth && $thisStart->getTimestamp() > \TimeSource::time()) {
				
					$start = clone $thisStart;
					$end = clone $thisEnd;
					$start->setTimeZone($timeZoneUTC);
					$end->setTimeZone($timeZoneUTC);

					$include = true;

					if ($include) {
						$newEvent = new EventModel();
						$newEvent->setGroupId($event->getGroupId());
						$newEvent->setVenueId($event->getVenueId());
						$newEvent->setCountryId($event->getCountryId());
						$newEvent->setEventRecurSetId($this->id);
						$newEvent->setSummary($event->getSummary());
						$newEvent->setDescription($event->getDescription());
						$newEvent->setStartAt($start);
						$newEvent->setEndAt($end);
						
						if (stripos($newEvent->getSummary(),$currentMonthLong) !== false) {
							$newEvent->setSummary(str_ireplace($currentMonthLong, $newEvent->getStartAt()->format('F'), $newEvent->getSummary()));
						} else if (stripos($newEvent->getSummary(),$currentMonthShort) !== false) {
							$newEvent->setSummary(str_ireplace($currentMonthShort, $newEvent->getStartAt()->format('M'), $newEvent->getSummary()));
						}
						
						$out[] = $newEvent;
					}
				
				}
				
				++$currentWeekInMonth;
			}
		}
		return $out;
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
	
	public function getNewWeeklyEventsFilteredForExisting(EventModel $event,  $monthsInAdvance = 3) {
		return $this->filterEventsForExisting($event, $this->getNewWeeklyEvents($event, $monthsInAdvance));
	}
	
	public function getNewMonthlyEventsOnSetDayInWeekFilteredForExisting(EventModel $event,  $monthsInAdvance = 6) {
		return $this->filterEventsForExisting($event, $this->getNewMonthlyEventsOnSetDayInWeek($event, $monthsInAdvance));
	}
	
	public function getNewMonthlyEventsOnLastDayInWeekFilteredForExisting(EventModel $event,  $monthsInAdvance = 6) {
		return $this->filterEventsForExisting($event, $this->getNewMonthlyEventsOnLastDayInWeek($event, $monthsInAdvance));
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}


	
}


