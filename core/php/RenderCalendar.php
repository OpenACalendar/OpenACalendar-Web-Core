<?php

use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RenderCalendar {
	
	
	protected $maxYear;
	protected $minYear;

	protected $year;
	protected $month;
	
	protected $modeByMonth = false;
	protected $modeByDate = false;


	/** @var EventRepositoryBuilder **/
	protected  $eventRepositoryBuilder;
	
	function __construct() {
		global $CONFIG;
		$this->eventRepositoryBuilder = new EventRepositoryBuilder();	
		$this->minYear = $CONFIG->calendarEarliestYearAllowed;
		$this->maxYear = (\TimeSource::getDateTime()->format("Y")+ $CONFIG->eventsCantBeMoreThanYearsInFuture);
	}

	public function getEventRepositoryBuilder() {
		return $this->eventRepositoryBuilder;
	}

	/** @var \DateTime **/
	protected $start;
	/** @var \DateTime **/
	protected $end;
	
	public function getYear() {
		return $this->year;
	}

	public function getMonth() {
		return $this->month;
	}
	
	public function getStart() {
		return $this->start;
	}

	public function getEnd() {
		return $this->end;
	}

	public function getModeByMonth() {
		return $this->modeByMonth;
	}

	public function getModeByDate() {
		return $this->modeByDate;
	}

		
	
	public function getMonthLongName() {
		$months = array(
			1=>'January',
			2=>'February',
			3=>'March',
			4=>'April',
			5=>'May',
			6=>'June',
			7=>'July',
			8=>'August',
			9=>'September',
			10=>'October',
			11=>'November',
			12=>'December',
		);
		// intval because I've seen "09" come thru here, and then key fails
		return $months[intval($this->month)];
	}

		
	protected $events = null;
	protected $eventsDataByDay = null;
	
	/**
	 *
	 * @param type $year
	 * @param type $month
	 * @throws \Exception 
	 * @TODO Change it from an Exception to another class so other exceptions are not accidentally caught.
	 */
	public function byMonth($year, $month, $expandToFullWeek = false) {
		if ($year < $this->minYear) throw new \Exception($this->minYear.' Onwards Only');
		if ($year > $this->maxYear) throw  new \Exception('Up to '.$this->maxYear.' only');
		if ($month < 1 || $month > 12)  new \Exception('Month is wrong');

		$this->start = new \DateTime('',new \DateTimeZone('UTC'));
		$this->start->setDate($year, $month, 1);
		$this->start->setTime(0, 0, 0);
		
		if ($expandToFullWeek) {
			$oneDay = new \DateInterval('P1D');
			while($this->start->format('N') != 1) {
				$this->start->sub($oneDay);
			}
		}

		$this->end = new \DateTime('',new \DateTimeZone('UTC'));
		if ($month == 12) {
			$this->end->setDate($year+1, 1, 1);
		} else {
			$this->end->setDate($year, $month+1, 1);
		}
		$this->end->setTime(23, 59, 59);		

		if ($expandToFullWeek) {
			while($this->end->format('N') != 7) {
				$this->end->add($oneDay);
			}
		}
		
		$this->year = $year;
		$this->month = $month;
		$this->modeByMonth = true;
	}
	
	public function byDate(\DateTime $dateTime, $days=31, $expandToFullWeek = false) {
		if ($dateTime->format("Y") < $this->minYear) throw new \Exception($this->minYear.' Onwards Only');
		if ($dateTime->format("Y") > $this->maxYear) throw  new \Exception('Up to '.$this->maxYear.' only');
		
		$this->start = clone $dateTime;
		$this->start->setTimezone(new \DateTimeZone('UTC'));
		$this->start->setTime(0, 0, 0);
		
		if ($expandToFullWeek) {
			$oneDay = new \DateInterval('P1D');
			while($this->start->format('N') != 1) {
				$this->start->sub($oneDay);
			}
		}

		$this->end = clone $this->start;
		$this->end->add(new \DateInterval('P'.$days.'D'));
		$this->end->setTime(23, 59, 59);		

		if ($expandToFullWeek) {
			while($this->end->format('N') != 7) {
				$this->end->add($oneDay);
			}
		}
		
		
		
		if ($this->start->format("j") > 15) {
			$m = $dateTime->format("n");
			if ($m == 12) {
				$this->year = $dateTime->format("Y")+1;
				$this->month = 1;
			} else {
				$this->year = $dateTime->format("Y");
				$this->month = $this->start->format("n")+1;
			}
		} else {
			$this->year = $this->start->format("Y");
			$this->month = $this->start->format("n");
		}
		
		$this->modeByDate = true;
		
	}
	
	public function setStartAndEnd($start, $end) {
		$this->start = $start;
		$this->end = $end;
	}
	
	
	/**
	 * Don't let user navigate off the end or start of the allowed range and get a 404 - so can return nulls
	 * @param type $year
	 * @param type $month
	 * @return type 
	 */
	public function getPrevNextLinksByMonth() {	
		// Don't let user navigate off the end or start of the allowed range and get a 404.
		$prevYear = $prevMonth = $nextYear = $nextMonth = null;
		if ($this->month == 12) {
			if ($this->year < $this->maxYear) {
				$nextYear = $this->year+1;
				$nextMonth = 1;
			}
		} else {
			$nextYear = $this->year;
			$nextMonth = $this->month+1;
		}
		if ($this->month == 1) {
			if ($this->year > $this->minYear) {
				$prevYear = $this->year-1;
				$prevMonth = 12;
			}
		} else {
			$prevYear = $this->year;
			$prevMonth = $this->month-1;
		}
		return array($prevYear,$prevMonth,$nextYear,$nextMonth);
	}
	
	protected function buildData() {
		// ############# Build Array of dates
		$this->eventsDataByDay = array();
		
		//print "START ".$this->start->format('Y-m-d H:i:s')."<br>";
		//print "END ".$this->start->format('Y-m-d H:i:s')."<br>";
	
		$now = new \DateTime($this->start->format('Y-m-d H:i:s'),new \DateTimeZone('UTC'));
		$now->setTime(23, 59, 59);
		
		$this->eventsDataByDay[] = $this->getTemplateForDaysData($now, true);
		
		$oneDay = new \DateInterval('P1D');
		while($now->getTimestamp() < $this->end->getTimestamp()) {
			$now->add($oneDay);
			$this->eventsDataByDay[] = $this->getTemplateForDaysData($now);
		}
		
		// ############# Get events

		$this->eventRepositoryBuilder->setAfter($this->start);
		$this->eventRepositoryBuilder->setBefore($this->end);
		
		$this->events = $this->eventRepositoryBuilder->fetchAll();
		
		foreach($this->events as $event) {
			
			foreach ($this->eventsDataByDay as $k=>$data) {
				$startAt = $event->getStartAt()->getTimestamp();
				
				if ($data['startTimestamp'] <= $startAt && $startAt <= $data['endTimestamp']) {
					$this->eventsDataByDay[$k]['events'][] = $event;
				} else if ($startAt < $data['startTimestamp'] && $event->getEndAt()->getTimestamp() > $data['startTimestamp']) {
					$this->eventsDataByDay[$k]['eventsContinuing'][] = $event;
				}
				
			}
			
		}
		
	}
	
	public function getData() {
		if (is_null($this->eventsDataByDay) || is_null($this->events)) {
			$this->buildData();
		}
		return $this->eventsDataByDay;
	}
	
	public function getEvents() {
		if (is_null($this->eventsDataByDay) || is_null($this->events)) {
			$this->buildData();
		}
		return $this->events;
	}
	
	public function isAnyEvents() {
		if (is_null($this->eventsDataByDay) || is_null($this->events)) {
			$this->buildData();
		}
		return (boolean)$this->events;
	}


	
	private function getTemplateForDaysData(\DateTime $now, $isFirst = false) {
		$currentDateTime = \TimeSource::getDateTime();
		$out =  array(
			'timestamp'=>$now->getTimestamp(),
			'day'=>$now->format('Y m d'),
			'dataForAddUrl'=>($now > $currentDateTime ? $now->format('Y-m-d') : null),
			'dayOfWeek'=>$now->format('N'),
			'events'=>array(),
			'eventsContinuing'=>array(),
			'today'=>false
		);
		
		if ($isFirst) {
			$out['display'] = $now->format('jS M Y');
			$this->lastDateShown = new \DateTime('',new \DateTimeZone('UTC'));
		} else {
			if ($this->lastDateShown->format('M Y') != $now->format('M Y')) {
				$out['display'] = $now->format('jS M Y');
			} else {
				$out['display'] = $now->format('jS');
			}
		}
		if (date('Y m d') == $out['day']) $out['today'] = true;
		$this->lastDateShown->setTimestamp($now->getTimestamp());
		
		$dt = new \DateTime('', new \DateTimeZone('UTC'));
		$dt->setTimestamp($now->getTimestamp());
		$dt->setTime(0, 0, 0);
		$out['startTimestamp'] = $dt->getTimestamp();
		$dt->setTime(23, 59, 59);
		$out['endTimestamp'] = $dt->getTimestamp();
		
		return $out;
	} 
}

