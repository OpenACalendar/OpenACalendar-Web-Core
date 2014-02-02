<?php

namespace api1exportbuilders;

use models\EventModel;
use models\SiteModel;
use models\VenueModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventListJSONBuilder extends BaseEventListBuilder {
	use TraitJSON;

	public $otherData = array();
	
	
	public function __construct(SiteModel $site = null, $timeZone  = null) {
		parent::__construct($site, $timeZone);
		$this->eventRepositoryBuilder->setAfterNow();
	}

	
	public function getContents() {
		global $CONFIG;
		$out = array_merge($this->otherData, array( 
			'data'=>$this->events , 
			'localtimezone'=>$this->localTimeZone->getName(),
		));
		if ($CONFIG->sponsor1Text && $CONFIG->sponsor1Html && $CONFIG->sponsor1Link) {
			$out['sponsorsHTML'] = '<a href="'.$CONFIG->sponsor1Link.'">'.$CONFIG->sponsor1Html.'</a>';
			$out['sponsorsText'] = $CONFIG->sponsor1Text.' '.$CONFIG->sponsor1Link;
		} else if ($CONFIG->sponsor1MightExist) {
			$out['sponsorsHTML'] = '';
			$out['sponsorsText'] = '';			
		}
		return json_encode($out);
	}
	
	public function addEvent(EventModel $event) {
		global $CONFIG;
		
		$out = array(
			'slug'=>$event->getSlug(),
			'summary'=> $event->getSummary(),					
			'summaryDisplay'=> $event->getSummaryDisplay(),			
			'description'=> ($event->getDescription()?$event->getDescription():''),
			'deleted'=> (boolean)$event->getIsDeleted(),
			'is_physical'=> (boolean)$event->getIsPhysical(),
			'is_virtual'=> (boolean)$event->getIsVirtual(),
		);
		
		$out['siteurl'] = $CONFIG->isSingleSiteMode ?
				'http://'.$CONFIG->webSiteDomain.'/event/'.$event->getSlug() :
				'http://'.$this->site->getSlug().".".$CONFIG->webSiteDomain.'/event/'.$event->getSlug();
		$out['url'] = $out['siteurl']; // $event->getUrl() && filter_var($event->getUrl(), FILTER_VALIDATE_URL) ? $event->getUrl() : $out['siteurl'];
		$out['timezone'] = $event->getTimezone();

		$startLocal = clone $event->getStartAt();
		$startLocal->setTimeZone($this->localTimeZone);
		$startTimeZone = clone $event->getStartAt();
		$startTimeZone->setTimeZone(new \DateTimeZone($event->getTimezone()));
		$out['start'] = array(
				'timestamp'=>$event->getStartAt()->getTimestamp(),
				'rfc2882utc'=>$event->getStartAt()->format('r'),
				'rfc2882local'=>$startLocal->format('r'),
				'displaylocal'=>$startLocal->format('D j M Y h:ia'),
				'rfc2882timezone'=>$startTimeZone->format('r'),
				'displaytimezone'=>$startTimeZone->format('D j M Y h:ia'),
			);
		
		
		$endLocal = clone $event->getEndAt();
		$endLocal->setTimeZone($this->localTimeZone);
		$endTimeZone = clone $event->getEndAt();
		$endTimeZone->setTimeZone(new \DateTimeZone($event->getTimezone()));
		$out['end'] = array(
				'timestamp'=>$event->getEndAt()->getTimestamp(),
				'rfc2882utc'=>$event->getEndAt()->format('r'),
				'rfc2882local'=>$endLocal->format('r'),
				'displaylocal'=>$endLocal->format('D j M Y h:ia'),
				'rfc2882timezone'=>$endTimeZone->format('r'),
				'displaytimezone'=>$endTimeZone->format('D j M Y h:ia'),
			);
		
		
		
		$this->events[] = $out;
	}
	
	public function addOtherDataVenue(VenueModel $venue) {
		$this->otherData['venue'] = array(
			'slug'=>$venue->getId(),
			'title'=>$venue->getTitle(),
			'lat'=>$venue->getLat(),
			'lng'=>$venue->getLng(),
		);
	}

}