<?php
namespace api1exportbuilders;

use Symfony\Component\HttpFoundation\Response;
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
class EventListICalBuilder extends BaseEventListBuilder  {
	use TraitICal;
	
	
	
	public function __construct(SiteModel $site = null, $timeZone  = null, $title = null) {
		parent::__construct($site, $timeZone, $title);
		// We go back a month, just so calendars have a bit of the past available.
		$time = \TimeSource::getDateTime();
		$time->sub(new \DateInterval("P30D"));
		$this->eventRepositoryBuilder->setAfter($time);
		
	}

	
	public function getContents() {
		global $CONFIG;
		$txt = $this->getIcalLine('BEGIN','VCALENDAR');
		$txt .= $this->getIcalLine('VERSION','2.0');
		$txt .= $this->getIcalLine('PRODID','-//JarOfGreen//NONSGML JarOfGreenWikiCalendarBundle//EN');
		if ($this->site && !$CONFIG->isSingleSiteMode) {
			$txt .= $this->getIcalLine('X-WR-CALNAME', ($this->title ? $this->title .' - ' : '').$this->site->getTitle().' '.$CONFIG->siteTitle);
		} else {
			$txt .= $this->getIcalLine('X-WR-CALNAME', ($this->title ? $this->title .' - ' : '').$CONFIG->siteTitle);
		}
		$txt .= implode("", $this->events);
		$txt .= $this->getIcalLine('END','VCALENDAR');
		return $txt;
	}
	
	public function getResponse() {
		global $CONFIG;		
		$response = new Response($this->getContents());
		$response->headers->set('Content-Type', 'text/calendar');
		$response->setPublic();
		$response->setMaxAge($CONFIG->cacheFeedsInSeconds);
		return $response;				
	}
	
	public function addEvent(EventModel $event, $groups = array(), VenueModel $venue = null,
							 AreaModel $area = null, CountryModel $country = null, $eventMedias = array()) {
		global $CONFIG;
		
		$siteSlug = $this->site ? $this->site->getSlug() : $event->getSiteSlug();
		
		$txt = $this->getIcalLine('BEGIN','VEVENT');
		$txt .= $this->getIcalLine('UID',$event->getSlug().'@'.$siteSlug.".".$CONFIG->webSiteDomain);

		$url = $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/event/'.$event->getSlugForUrl() :
			'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/event/'.$event->getSlugForUrl() ;
		$txt .= $this->getIcalLine('URL',$url);

		if ($event->getIsDeleted()) {
			$txt .= $this->getIcalLine('SUMMARY',$event->getSummaryDisplay(). " [DELETED]");
			$txt .= $this->getIcalLine('METHOD','CANCEL');
			$txt .= $this->getIcalLine('STATUS','CANCELLED');
			$txt .= $this->getIcalLine('DESCRIPTION','DELETED');
		} else if ($event->getIsCancelled()) {
			$txt .= $this->getIcalLine('SUMMARY',$event->getSummaryDisplay(). " [CANCELLED]");
			$txt .= $this->getIcalLine('METHOD','CANCEL');
			$txt .= $this->getIcalLine('STATUS','CANCELLED');
			$txt .= $this->getIcalLine('DESCRIPTION','CANCELLED');
		} else {
			$txt .= $this->getIcalLine('SUMMARY',$event->getSummaryDisplay());

			$description = '';
			foreach($this->extraHeaders as $extraHeader) {
				$description .= $extraHeader->getText()."\n\n";
			}
			$description .= $event->getDescription()."\n".
					//($event->getUrl() ? $event->getUrl()."\n" : '').
					$url."\n".
					"Powered by ".$CONFIG->siteTitle;
			foreach($this->extraFooters as $extraFooter) {
				$description .= "\n".$extraFooter->getText();
			}
			$txt .= $this->getIcalLine('DESCRIPTION',$description);
			
			$descriptionHTML = "<html><body>";
			foreach($this->extraHeaders as $extraHeader) {
				$descriptionHTML .= "<p>".$extraHeader->getHtml()."</p>";
			}
			$descriptionHTML .=	"<p>".str_replace("\r","",str_replace("\n","<br>",htmlentities($event->getDescription())))."</p>";
			//if ($event->getUrl()) $descriptionHTML .= '<p>More info: <a href="'.$event->getUrl().'">'.$event->getUrl().'</a></p>';
			$descriptionHTML .= '<p>More info: <a href="'.$url.'">'.$url.'</a></p>';
			$descriptionHTML .= '<p style="font-style:italic;font-size:80%">Powered by <a href="'.$url.'">'.$CONFIG->siteTitle.'</a>';
			foreach($this->extraFooters as $extraFooter) {
				$descriptionHTML .= "<br>".$extraFooter->getHtml();
			}
			$descriptionHTML .= '</p>';
			$descriptionHTML .= '</body></html>';
			$txt .= $this->getIcalLine("X-ALT-DESC;FMTTYPE=text/html", $descriptionHTML);
			
			$locationDetails = array();
			if ($event->getVenue() && $event->getVenue()->getTitle()) $locationDetails[] = $event->getVenue()->getTitle();
			if ($event->getVenue() && $event->getVenue()->getAddress()) $locationDetails[] = $event->getVenue()->getAddress();
			if ($event->getArea() && $event->getArea()->getTitle()) $locationDetails[] = $event->getArea()->getTitle();
			if ($event->getVenue() && $event->getVenue()->getAddressCode()) $locationDetails[] = $event->getVenue()->getAddressCode();
			if ($locationDetails) {
				$txt .= $this->getIcalLine('LOCATION',implode(", ", $locationDetails));
			}
			if ($event->getVenue() && $event->getVenue()->getLat() && $event->getVenue()->getLng()) {
				$txt .= $this->getIcalLine('GEO',$event->getVenue()->getLat().";".$event->getVenue()->getLng());
			}
		}
		
		$txt .= $this->getIcalLine('DTSTART',$event->getStartAt()->format("Ymd")."T".$event->getStartAt()->format("His")."Z");
		$txt .= $this->getIcalLine('DTEND',$event->getEndAt()->format("Ymd")."T".$event->getEndAt()->format("His")."Z");
		
		$txt .= $this->getIcalLine('END','VEVENT');
		$this->events[] = $txt;
	}

}


