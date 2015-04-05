<?php

namespace api1exportbuilders;

use models\EventModel;
use models\SiteModel;
use models\GroupModel;
use models\VenueModel;
use models\AreaModel;
use models\CountryModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
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
		return json_encode($out);
	}
	
	public function addEvent(EventModel $event, $groups = array(), VenueModel $venue = null,
							 AreaModel $area = null, CountryModel $country = null, $eventMedias = array()) {
		global $CONFIG;
		
		$out = array(
			'slug'=>$event->getSlug(),
			'slugforurl'=>$event->getSlugForUrl(),
			'summary'=> $event->getSummary(),					
			'summaryDisplay'=> $event->getSummaryDisplay(),			
			'description'=> ($event->getDescription()?$event->getDescription():''),
			'deleted'=> (boolean)$event->getIsDeleted(),
			'cancelled'=> (boolean)$event->getIsCancelled(),
			'is_physical'=> (boolean)$event->getIsPhysical(),
			'is_virtual'=> (boolean)$event->getIsVirtual(),
			'custom_fields'=> array(),
		);
		
		$out['siteurl'] = $CONFIG->isSingleSiteMode ?
				'http://'.$CONFIG->webSiteDomain.'/event/'.$event->getSlugForUrl() :
				'http://'.($this->site?$this->site->getSlug():$event->getSiteSlug()).".".$CONFIG->webSiteDomain.'/event/'.$event->getSlugForUrl();
		$out['url'] = $event->getUrl() && filter_var($event->getUrl(), FILTER_VALIDATE_URL) ? $event->getUrl() : $out['siteurl'];
		$out['ticket_url'] = $event->getTicketUrl() && filter_var($event->getTicketUrl(), FILTER_VALIDATE_URL) ? $event->getTicketUrl() : null;
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
				'yearlocal'=>$startLocal->format('Y'),
				'monthlocal'=>$startLocal->format('n'),
				'daylocal'=>$startLocal->format('j'),
				'hourlocal'=>$startLocal->format('G'),
				'minutelocal'=>$startLocal->format('i'),
				'rfc2882timezone'=>$startTimeZone->format('r'),
				'displaytimezone'=>$startTimeZone->format('D j M Y h:ia'),
				'yeartimezone'=>$startTimeZone->format('Y'),
				'monthtimezone'=>$startTimeZone->format('n'),
				'daytimezone'=>$startTimeZone->format('j'),
				'hourtimezone'=>$startTimeZone->format('G'),
				'minutetimezone'=>$startTimeZone->format('i'),
			
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
				'yearlocal'=>$endLocal->format('Y'),
				'monthlocal'=>$endLocal->format('n'),
				'daylocal'=>$endLocal->format('j'),
				'hourlocal'=>$endLocal->format('G'),
				'minutelocal'=>$endLocal->format('i'),
				'rfc2882timezone'=>$endTimeZone->format('r'),
				'displaytimezone'=>$endTimeZone->format('D j M Y h:ia'),
				'yeartimezone'=>$endTimeZone->format('Y'),
				'monthtimezone'=>$endTimeZone->format('n'),
				'daytimezone'=>$endTimeZone->format('j'),
				'hourtimezone'=>$endTimeZone->format('G'),
				'minutetimezone'=>$endTimeZone->format('i'),
			);
		
		if (is_array($groups)) {
			$out['groups'] = array();
			foreach($groups as $group) {
				$out['groups'][] = array(
						'slug'=>$group->getSlug(),
						'title'=>$group->getTitle(),
						'description'=>$group->getDescription(),
					);
			}
		}
		
		if ($venue) {
			$out['venue'] = array(
				'slug'=>$venue->getSlug(),
				'title'=>$venue->getTitle(),
				'description'=>$venue->getDescription(),
				'address'=>$venue->getAddress(),
				'addresscode'=>$venue->getAddressCode(),
				'lat'=>$venue->getLat(),
				'lng'=>$venue->getLng(),
				);
		}
		
		if ($area) {
			$out['areas'] = array(array(
				'slug'=>$area->getSlug(),
				'title'=>$area->getTitle(),
			));
		}
		
		if ($country) {
			$out['country'] = array(
				'title'=>$country->getTitle(),
			);
		}

		if (is_array($eventMedias)) {
			$out['medias'] = array();
			$siteurl = $CONFIG->getWebSiteDomainSecure($this->site->getSlug());
			foreach($eventMedias as $eventMedia) {
				$out['medias'][] = array(
					'slug'=>$eventMedia->getSlug(),
					'title'=>$eventMedia->getTitle(),
					'sourceUrl'=>$eventMedia->getSourceUrl(),
					'sourcetext'=>$eventMedia->getSourceText(),
					'picture'=>array(
						'fullURL'=>$siteurl.'/media/'.$eventMedia->getSlug().'/full',
						'normalURL'=>$siteurl.'/media/'.$eventMedia->getSlug().'/normal',
						'thumbnailURL'=>$siteurl.'/media/'.$eventMedia->getSlug().'/thumbnail',
					)
				);
			}
		}

		if ($this->site) {
			foreach($this->site->getCachedEventCustomFieldDefinitionsAsModels() as $customField) {
				$out['custom_fields'][$customField->getKey()] = $event->getCustomField($customField);
			}
		}

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
