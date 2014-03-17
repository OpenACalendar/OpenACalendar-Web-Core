<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

use models\EventModel;
use models\SiteModel;
use repositories\builders\EventRepositoryBuilder;

class SearchForDuplicateEvents {

	/** @var EventModel **/
	protected $event;
	
	/** @var Site **/
	protected $site;
			
	protected $showEventsCount = 3;
	
	protected $showEventsThreshhold = 2;
			
	function __construct(EventModel $event, SiteModel $site, $showEventsCount=3, $showEventsThreshhold=2) {
		$this->event = $event;
		$this->site = $site;
		$this->showEventsCount = $showEventsCount;
		$this->showEventsThreshhold = $showEventsThreshhold;
	}
	
	protected $notTheseSlugs = array();
	
	function setNotDuplicateSlugs($in) {
		foreach($in as $slug) {
			if ($slug) {
				$this->notTheseSlugs[] = $slug;
			}
		}
	}
	
	function getPossibleDuplicates() {
	
		if (!$this->event->getStartAt() || !$this->event->getEndAt()) {
			$this->eventsToConsider  = array();
		}
		
		## Get events
		$eventRepositoryBuilder = new EventRepositoryBuilder();
		$eventRepositoryBuilder->setSite($this->site);
		
		$eventRepositoryBuilder->setIncludeDeleted(true);
		
		$after = clone $this->event->getStartAt();
		$after->sub(new \DateInterval("PT4H"));
		$eventRepositoryBuilder->setAfter($after);
		
		$before = clone $this->event->getStartAt();
		$before->add(new \DateInterval("PT4H"));
		$eventRepositoryBuilder->setBefore($before);

		$events  = $eventRepositoryBuilder->fetchAll();
		
		## Score
		$eventsWithScore = array();
		foreach($events as $event) {
			if (!in_array($event->getSlug(), $this->notTheseSlugs)) {
				$eventsWithScore[] = array(
					'event'=>$event,
					'score'=>$this->getScoreForConsideredEvent($event),
				); 
			}
		}
		
		## sort
		$sortFunc = function($a,$b){
			if ($a['score'] == $b['score']) { return 0; }
			elseif ($a['score'] > $b['score']) { return 1;}
			elseif ($a['score'] < $b['score']) { return -1; };
		};
		usort($eventsWithScore, $sortFunc);
		
		## Results
		$results = array();
		foreach($eventsWithScore as $eventWithScore) {
			if (count($results) < $this->showEventsCount && $eventWithScore['score'] >= $this->showEventsThreshhold) {
				$results[] = $eventWithScore['event'];
			}
		}
		
		return $results;
		
	}
	
	
	function getScoreForConsideredEvent(EventModel $event) {
		$score = 0;
		
		if ($this->event->getStartAt() && $event->getStartAt() && 
				$this->event->getStartAt()->getTimestamp() == $event->getStartAt()->getTimestamp()) {
			$score++;
		}
		if ($this->event->getEndAt() && $event->getEndAt() && 
				$this->event->getEndAt()->getTimestamp() == $event->getEndAt()->getTimestamp()) {
			$score++;
		}
		if ($this->event->getGroupId() && $this->event->getGroupId() == $event->getGroupId()) {
			$score++;
		}
		if ($this->event->getUrl() && $this->event->getUrl() == $event->getUrl()) {
			$score++;
		}
		if ($this->event->getSummary()) {
			if ($this->event->getSummary() == $event->getSummary()) {
				$score++;
			} else {
				$bits1 = explode(" ", strtolower($this->event->getSummary()));
				$bits2 = explode(" ", strtolower($event->getSummary()));
				$flag = false;
				foreach($bits1 as $bit) {
					if ($bit && in_array($bit, $bits2)) {
						$flag = true;
					}
				}
				if ($flag) $score++;
			}
		}
		if ($this->event->getVenueId() && $this->event->getVenueId() == $event->getVenueId()) {
			$score++;
		}
		if ($this->event->getAreaId() && $this->event->getAreaId() == $event->getAreaId()) {
			$score++;
		}
		
		return $score;
	}
	
	
	
}

