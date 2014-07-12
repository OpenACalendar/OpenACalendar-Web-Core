<?php
namespace api1exportbuilders;


use models\SiteModel;
use models\EventModel;
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
class EventListATOMBeforeBuilder extends BaseEventListBuilder  {
	use TraitATOM;
	
	protected $daysBefore = 3;
	
	/**
	 * 
	 * @param type $daysBefore Null can be passed here, in which case use our default.
	 */
	public function setDaysBefore($daysBefore = 3) {
		$this->daysBefore = $daysBefore && $daysBefore > 0 && $daysBefore < 700 ? $daysBefore : 3;
	}

	public function __construct(SiteModel $site = null, $timeZone = null, $title = null) {
		parent::__construct($site, $timeZone, $title);
		// order by start at time
		$this->eventRepositoryBuilder->setOrderByStartAt(true);
	}
	
	public function build() {
		// We only want events in X days before now and onwards
		$time = \TimeSource::getDateTime();
		$time->add(new \DateInterval("P".$this->daysBefore."D"));
		$this->eventRepositoryBuilder->setBefore($time);
		parent::build();
	}

	
	public function getContents() {
		global $CONFIG;
		$txt = '<?xml version="1.0" encoding="utf-8"?>';
		$txt .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";
		if ($this->site && !$CONFIG->isSingleSiteMode) {
			$txt .= '<title>'.
					($this->title ? htmlentities($this->title).' - ' : '').
					($this->daysBefore > 1 ? $this->daysBefore.' Days Before Start - ' : ' 1 Day before start - ').
					 htmlentities($this->site->getTitle()).' '.
					 htmlentities($CONFIG->siteTitle).
					'</title>'."\n";
		} else {
			$txt .= '<title>'.
					($this->title ? htmlentities($this->title).' - ' : '').
					($this->daysBefore > 1 ? $this->daysBefore.' Days Before Start - ' : ' 1 Day before start - ').
					 htmlentities($CONFIG->siteTitle).
					'</title>'."\n";
		}
		$txt .= '<id>'.$this->feedURL.'</id>'."\n";
		$txt .= '<link rel="self" href="'.$this->feedURL.'"/>'."\n";
		$txt .= '<updated>'.date("Y-m-d").'T'.date("H:i:s").'Z</updated>';
		$txt .= implode("",$this->events)."\n";
		$txt .= '</feed>'."\n";		
		return $txt;
	}	
	
	protected function getUpdatedString(EventModel $event) {
		$dateIn = clone $event->getStartAt();
		$dateIn->sub(new \DateInterval("P3D"));
		return '<updated>'.$dateIn->format("Y-m-d")."T".$dateIn->format("H:i:s")."Z</updated>";
	}
	
	
	public function addEvent(EventModel $event, $groups = array(), VenueModel $venue = null,
							 AreaModel $area = null, CountryModel $country = null, $eventMedias = array()) {
		global $CONFIG;
		
		if ($event->getIsDeleted()) return false;
		
		
		// ########################################### Get Data
		
		$siteSlug = $this->site ? $this->site->getSlug() : $event->getSiteSlug();
		
		$ourUrl = $CONFIG->isSingleSiteMode ? 
				'http://'.$CONFIG->webSiteDomain.'/event/'.$event->getSlug() : 
				'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/event/'.$event->getSlug() ; 
		
		$dh = new \DateTime('', $this->localTimeZone);
		$dh->setTimestamp($event->getStartAt()->getTimestamp());
		$dateTxt = $dh->format('D j M Y h:ia').' to ';
		$dateTxtShort = $dh->format('D j M');
		$dh->setTimestamp($event->getEndAt()->getTimestamp());
		$dateTxt .= $dh->format('D j M Y h:ia');
		
		
		// ########################################## Build
		$txt = '<entry>';
		$txt .= '<id>'.$ourUrl.'</id>';
		$txt .= '<link href="'.$ourUrl.'"/>';
		
		$txt .= '<title>'.  $this->getData($event->getSummaryDisplay().', '.$dateTxtShort).'</title>';

		$txt .= '<summary>'.$dateTxt.'</summary>';
		
		$content =  '';
		foreach($this->extraHeaders as $extraHeader) {
			$content .= $extraHeader->getHtml()."<br><br>";
		}
		$content .= $dateTxt."<br>";
		if ($event->getDescription()) {
			$content .=  str_replace("\n","<br>",htmlentities($event->getDescription(), ENT_QUOTES, 'UTF-8')).'<br>';
		}
		// TODO $event->getUrl()
		$content .= '<a href="'.htmlentities($ourUrl).'">More details at '.htmlentities($ourUrl).'</a><br>';
		$content .= '<p style="font-style:italic;font-size:80%">'.
					'Powered by <a href="'.$ourUrl.'">'.$CONFIG->siteTitle.'</a>';
		foreach($this->extraFooters as $extraFooter) {
			$content .= "<br>".$extraFooter->getHtml();
		}
		$content .='</p>';
		
		$txt .= '<content type="html">'.$this->getBigData($content).'</content>';
				
		$txt .= $this->getUpdatedString($event);
		
		$txt .= '<author><name>'.$CONFIG->siteTitle.'</name></author></entry>'." \r\n";
		
		$this->events[] = $txt;
	}
	
}


