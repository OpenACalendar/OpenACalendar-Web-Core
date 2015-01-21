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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventListCSVBuilder extends BaseEventListBuilder {
	use TraitCSV;

	protected $delimiter = ",";

	public function __construct(SiteModel $site = null, $timeZone  = null) {
		parent::__construct($site, $timeZone);
		$this->eventRepositoryBuilder->setAfterNow();
	}

	public function getContents() {
		return "Slug,".
			"Slug For URL,".
			"Summary,".
			"Summary Display,".
			"Description,".
			"Is Deleted,".
			"Is Cancelled,".
			"Is Physical,".
			"Is Virtual,".
			"Site URL,".
			"URL,".
			"Ticket URL,".
			"Timezone,".
			"Start UTC,".
			"End UTC,".
			"Start Timezone,".
			"End Timezone,".
			",".
			"\n".implode("\n",$this->events);
	}

	public function addEvent(EventModel $event, $groups = array(), VenueModel $venue = null,
							 AreaModel $area = null, CountryModel $country = null, $eventMedias = array()) {
		global $CONFIG;

		$siteurl = $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/event/'.$event->getSlugForUrl() :
			'http://'.($this->site?$this->site->getSlug():$event->getSiteSlug()).".".$CONFIG->webSiteDomain.'/event/'.$event->getSlugForUrl();
		$url = $event->getUrl() && filter_var($event->getUrl(), FILTER_VALIDATE_URL) ? $event->getUrl() : $siteurl;
		$ticket_url = $event->getTicketUrl() && filter_var($event->getTicketUrl(), FILTER_VALIDATE_URL) ? $event->getTicketUrl() : null;

		$this->events[] =
			$this->getCell($event->getSlug()) . $this->delimiter .
			$this->getCell($event->getSlugForUrl()) . $this->delimiter .
			$this->getCell($event->getSummary()) . $this->delimiter .
			$this->getCell($event->getSummaryDisplay()) . $this->delimiter .
			$this->getCell($event->getDescription()) . $this->delimiter .
			$this->getCellBoolean($event->getIsDeleted()) . $this->delimiter .
			$this->getCellBoolean($event->getIsCancelled()) . $this->delimiter .
			$this->getCellBoolean($event->getIsPhysical()) . $this->delimiter .
			$this->getCellBoolean($event->getIsVirtual()) . $this->delimiter .
			$this->getCell($siteurl) . $this->delimiter .
			$this->getCell($url) . $this->delimiter .
			$this->getCell($ticket_url) . $this->delimiter .
			$this->getCell($event->getTimezone()) . $this->delimiter .
			$this->getCell($event->getStartAtInUTC()->format("r")) . $this->delimiter .
			$this->getCell($event->getEndAtInUTC()->format("r")) . $this->delimiter .
			$this->getCell($event->getStartAtInTimezone()->format("r")) . $this->delimiter .
			$this->getCell($event->getEndAtInTimezone()->format("r")) . $this->delimiter ;


		/**

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

		$this->events[] = $out; **/

	}


}
