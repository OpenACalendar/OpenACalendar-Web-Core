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
			"Venue Slug,".
			"Venue Slug For URL,".
			"Venue Title,".
			"Venue Description,".
			"Venue Address,".
			"Venue Address Code,".
			"Venue Lat,".
			"Venue Lng,".
			"Venue URL,".
			"Area Slug,".
			"Area Slug For URL,".
			"Area Title,".
			"Area Description,".
			"Area URL,".
			"Country Code,".
			"Country Title,".
			"Country URL,".
			"\n".implode("\n",$this->events);
	}

	public function addEvent(EventModel $event, $groups = array(), VenueModel $venue = null,
							 AreaModel $area = null, CountryModel $country = null, $eventMedias = array()) {
		global $CONFIG;

		$siteurlbase = $CONFIG->getWebSiteDomainSecure($this->site?$this->site->getSlug():$event->getSiteSlug());
		$siteurl = $siteurlbase.'/event/'.$event->getSlugForUrl();
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
			$this->getCell($event->getEndAtInTimezone()->format("r")) . $this->delimiter .
			($venue ?

				$this->getCell($venue->getSlug()) . $this->delimiter .
				$this->getCell($venue->getSlugForUrl()) . $this->delimiter .
				$this->getCell($venue->getTitle()) . $this->delimiter .
				$this->getCell($venue->getDescription()) . $this->delimiter .
				$this->getCell($venue->getAddress()) . $this->delimiter .
				$this->getCell($venue->getAddressCode()) . $this->delimiter .
				$this->getCell($venue->getLat()) . $this->delimiter .
				$this->getCell($venue->getLng()) . $this->delimiter .
				$this->getCell($siteurlbase.'/venue/'.$venue->getSlugForUrl()) . $this->delimiter

			: $this->delimiter .$this->delimiter .$this->delimiter .$this->delimiter .$this->delimiter .$this->delimiter .$this->delimiter .$this->delimiter  .$this->delimiter).
			($area ?

				$this->getCell($area->getSlug()) . $this->delimiter .
				$this->getCell($area->getSlugForUrl()) . $this->delimiter .
				$this->getCell($area->getTitle()) . $this->delimiter .
				$this->getCell($area->getDescription()) . $this->delimiter .
				$this->getCell($siteurlbase . '/area/' . $area->getSlugForUrl()) . $this->delimiter

			: $this->delimiter .$this->delimiter .$this->delimiter .$this->delimiter .$this->delimiter ).
			($country ?

				$this->getCell($country->getTwoCharCode()) . $this->delimiter .
				$this->getCell($country->getTitle()) . $this->delimiter.
				$this->getCell($siteurlbase . '/country/' . $country->getTwoCharCode()) . $this->delimiter

			: $this->delimiter .$this->delimiter  .$this->delimiter );

	}


}
