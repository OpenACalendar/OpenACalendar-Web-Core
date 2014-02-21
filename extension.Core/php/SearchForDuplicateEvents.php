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
			
	function __construct(EventModel $event, SiteModel $site) {
		$this->event = $event;
		$this->site = $site;
	}
	
	
	
	function getPossibleDuplicates() {
		
		if (!$this->event->getStartAt() || !$this->event->getEndAt()) {
			return array();
		}
		
		$eventRepositoryBuilder = new EventRepositoryBuilder();
		$eventRepositoryBuilder->setSite($this->site);
		
		$after = clone $this->event->getStartAt();
		$after->sub(new \DateInterval("PT2H"));
		$eventRepositoryBuilder->setAfter($after);
		
		$before = clone $this->event->getStartAt();
		$before->add(new \DateInterval("PT2H"));
		$eventRepositoryBuilder->setBefore($before);

		return $eventRepositoryBuilder->fetchAll();
				
	}

	
}

