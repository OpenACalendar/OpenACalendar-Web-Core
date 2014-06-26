<?php
namespace api1exportbuilders;

use Symfony\Component\HttpFoundation\Response;
use models\EventModel;
use models\SiteModel;

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
	
	public function addEvent(EventModel $event) {
		global $CONFIG;
		
		$siteSlug = $this->site ? $this->site->getSlug() : $event->getSiteSlug();
		
		$txt = $this->getIcalLine('BEGIN','VEVENT');
		$txt .= $this->getIcalLine('UID',$event->getSlug().'@'.$siteSlug.".".$CONFIG->webSiteDomain);
		
		if ($event->getIsDeleted()) {
			$txt .= $this->getIcalLine('METHOD','CANCEL');
			$txt .= $this->getIcalLine('STATUS','CANCELLED');
		} else {
			$txt .= $this->getIcalLine('SUMMARY',$event->getSummaryDisplay());
			
			$url = $CONFIG->isSingleSiteMode ?
					'http://'.$CONFIG->webSiteDomain.'/event/'.$event->getSlug() : 
					'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/event/'.$event->getSlug() ; 
			$txt .= $this->getIcalLine('URL',$url);
			$description = '';
			foreach($this->extraHeaders as $extraHeader) {
				$description .= $extraHeader->getText()."\n\n";
			}
			$description .= $event->getDescription()."\n".
					//($event->getUrl() ? $event->getUrl()."\n" : '').
					$url."\n".
					"Powered by ".$CONFIG->siteTitle;
			$txt .= $this->getIcalLine('DESCRIPTION',$description);
			
			$descriptionHTML = "<html><body>";
			foreach($this->extraHeaders as $extraHeader) {
				$descriptionHTML .= "<p>".$extraHeader->getHtml()."</p>";
			}
			$descriptionHTML .=	"<p>".str_replace("\r","",str_replace("\n","<br>",htmlentities($event->getDescription())))."</p>";
			//if ($event->getUrl()) $descriptionHTML .= '<p>More info: <a href="'.$event->getUrl().'">'.$event->getUrl().'</a></p>';
			$descriptionHTML .= '<p>More info: <a href="'.$url.'">'.$url.'</a></p>';
			$descriptionHTML .= '<p style="font-style:italic;font-size:80%">Powered by <a href="'.$url.'">'.$CONFIG->siteTitle.'</a>';
			if ($CONFIG->sponsor1Html && $CONFIG->sponsor1Link && $CONFIG->sponsor2Html && $CONFIG->sponsor2Link) {
				$descriptionHTML .= ', Sponsored by <a href="'.$CONFIG->sponsor1Link.'">'.$CONFIG->sponsor1Html.'</a> and <a href="'.$CONFIG->sponsor2Link.'">'.$CONFIG->sponsor2Html.'</a>';
			} else if ($CONFIG->sponsor1Html && $CONFIG->sponsor1Link) {
				$descriptionHTML .= ', Sponsored by <a href="'.$CONFIG->sponsor1Link.'">'.$CONFIG->sponsor1Html.'</a>';
			}
			$descriptionHTML .= '</p>';
			$descriptionHTML .= '</body></html>';
			$txt .= $this->getIcalLine("X-ALT-DESC;FMTTYPE=text/html", $descriptionHTML);
			
			//$locationDetails = '';
			//if ($event->getAddress()) $locationDetails .= $event->getAddress();
			//if ($event->getPostcode()) $locationDetails .= " ". $event->getPostcode();
			//if ($event->getLocation()) $locationDetails .= " ".$event->getLocation()->getTitle();
			//$txt .= $this->getIcalLine('LOCATION',$locationDetails);
		}
		
		$txt .= $this->getIcalLine('DTSTART',$event->getStartAt()->format("Ymd")."T".$event->getStartAt()->format("His")."Z");
		$txt .= $this->getIcalLine('DTEND',$event->getEndAt()->format("Ymd")."T".$event->getEndAt()->format("His")."Z");
		
		$txt .= $this->getIcalLine('END','VEVENT');
		$this->events[] = $txt;
	}

}
