<?php
namespace api1exportbuilders;


use models\SiteModel;
use models\EventModel;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\VenueHistoryModel;
use repositories\builders\HistoryRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class HistoryListATOMBuilder extends BaseHistoryListBuilder {
	use TraitATOM;

	
	public function __construct(SiteModel $site = null, $timeZone  = null) {
		parent::__construct($site, $timeZone);
		$this->historyRepositoryBuilder = new HistoryRepositoryBuilder();
		$this->historyRepositoryBuilder->setSite($site);
	}
	
	
	public function getContents() {
		global $CONFIG;
		$txt = '<?xml version="1.0" encoding="utf-8"?>';
		$txt .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";
		if ($this->site && !$CONFIG->isSingleSiteMode) {
			$txt .= '<title>'.
					($this->title ? htmlentities($this->title).' - ' : '').
					 htmlentities($this->site->getTitle()).' '.
					 htmlentities($CONFIG->siteTitle).
					'</title>'."\n";
		} else {
			$txt .= '<title>'.
					($this->title ? htmlentities($this->title).' - ' : '').
					 htmlentities($CONFIG->siteTitle).
					'</title>'."\n";
		}
		$txt .= '<id>'.$this->feedURL.'</id>'."\n";
		$txt .= '<link rel="self" href="'.$this->feedURL.'"/>'."\n";
		$txt .= '<updated>'.date("Y-m-d").'T'.date("H:i:s").'Z</updated>';
		$txt .= implode("",$this->histories)."\n";
		$txt .= '</feed>'."\n";		
		return $txt;
	}	

	
	public function addEventHistory(EventHistoryModel $history) {
		global $CONFIG;
		
		$siteSlug = $this->site ? $this->site->getSlug() : $history->getSiteSlug();		
		$ourUrl = $CONFIG->isSingleSiteMode ? 
				'http://'.$CONFIG->webSiteDomain.'/event/'.$history->getEventSlug() : 
				'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/event/'.$history->getEventSlug() ;
		
		$txt = '<entry>';
		$txt .= '<id>'.$ourUrl.'/history/'.$history->getCreatedAtTimeStamp().'</id>';
		$txt .= '<link href="'.$ourUrl.'"/>';
		
		$txt .= '<title>'.  $this->getData($history->getSummaryDisplay()).'</title>';

		$txt .= '<summary>'.$this->getBigData($history->getDescription()).'</summary>';
		
		$txt .= '<updated>'.$history->getCreatedAt()->format("Y-m-d")."T".$history->getCreatedAt()->format("H:i:s")."Z</updated>";
		
		$txt .= '<author><name>'.$CONFIG->siteTitle.'</name></author></entry>'." \r\n";
		
		$this->histories[] = $txt;
	}
	
	public function addGroupHistory(GroupHistoryModel $history) {
		global $CONFIG;
		
		$siteSlug = $this->site ? $this->site->getSlug() : $history->getSiteSlug();		
		$ourUrl = $CONFIG->isSingleSiteMode ? 
				'http://'.$CONFIG->webSiteDomain.'/group/'.$history->getGroupSlug() : 
				'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/group/'.$history->getGroupSlug() ;
		
		$txt = '<entry>';
		$txt .= '<id>'.$ourUrl.'/history/'.$history->getCreatedAtTimeStamp().'</id>';
		$txt .= '<link href="'.$ourUrl.'"/>';
		
		$txt .= '<title>'.  $this->getData($history->getTitle()).'</title>';

		$txt .= '<summary>'.$this->getBigData($history->getDescription()).'</summary>';
		
		$txt .= '<updated>'.$history->getCreatedAt()->format("Y-m-d")."T".$history->getCreatedAt()->format("H:i:s")."Z</updated>";
		
		$txt .= '<author><name>'.$CONFIG->siteTitle.'</name></author></entry>'." \r\n";
		
		$this->histories[] = $txt;
	}
	
	public function addVenueHistory(VenueHistoryModel $history) {
		global $CONFIG;
		
		$siteSlug = $this->site ? $this->site->getSlug() : $history->getSiteSlug();		
		$ourUrl = $CONFIG->isSingleSiteMode ? 
				'http://'.$CONFIG->webSiteDomain.'/venue/'.$history->getVenueSlug() : 
				'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/venue/'.$history->getVenueSlug() ;
		
		$txt = '<entry>';
		$txt .= '<id>'.$ourUrl.'/history/'.$history->getCreatedAtTimeStamp().'</id>';
		$txt .= '<link href="'.$ourUrl.'"/>';
		
		$txt .= '<title>'.  $this->getData($history->getTitle()).'</title>';

		$txt .= '<summary>'.$this->getBigData($history->getDescription()).'</summary>';
		
		$txt .= '<updated>'.$history->getCreatedAt()->format("Y-m-d")."T".$history->getCreatedAt()->format("H:i:s")."Z</updated>";
		
		$txt .= '<author><name>'.$CONFIG->siteTitle.'</name></author></entry>'." \r\n";
		
		$this->histories[] = $txt;		
	}

	
}

