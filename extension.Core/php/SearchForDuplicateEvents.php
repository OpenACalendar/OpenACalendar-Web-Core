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
	
	protected $showEventsThreshhold = 1;
			
	function __construct(EventModel $event, SiteModel $site) {
		$this->event = $event;
		$this->site = $site;
	}
	
	function getPossibleDuplicates() {
	
		if (!$this->event->getStartAt() || !$this->event->getEndAt()) {
			$this->eventsToConsider  = array();
		}
		
		## Get events
		$eventRepositoryBuilder = new EventRepositoryBuilder();
		$eventRepositoryBuilder->setSite($this->site);
		
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
			$eventsWithScore[] = array(
				'event'=>$event,
				'score'=>$this->getScoreForConsideredEvent($event),
			); 
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
		
		return $score;
	}
	
	
	
}

