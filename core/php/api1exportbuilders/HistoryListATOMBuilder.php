<?php
namespace api1exportbuilders;


use models\SiteModel;
use models\EventModel;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\VenueHistoryModel;
use models\AreaHistoryModel;
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

		$txt .= '<summary>';
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getSummaryChanged()) {
				$txt .= 'Summary Changed: '.$this->getData($history->getSummary())."\n\n";
			}
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed: '.$this->getBigData($history->getDescription())."\n\n";
			}
			if ($history->getUrlChanged()) {
				$txt .= 'URL Changed: '.$this->getData($history->getUrl())."\n\n";
			}
			if ($history->getStartAtChanged()) {
				$txt .= 'Start Changed'."\n\n"; // TODO show time, but in what timezone?
			}
			if ($history->getEndAtChanged()) {
				$txt .= 'End Changed'."\n\n"; // TODO show time, but in what timezone?
			}
			if ($history->getCountryIdChanged()) {
				$txt .= 'Country Changed'."\n\n";
			}
			if ($history->getTimezoneChanged()) {
				$txt .= 'Timezone Changed: '.$this->getData($history->getTimezone())."\n\n";
			}
			if ($history->getAreaIdChanged()) {
				$txt .= 'Area Changed'."\n\n";
			}
			if ($history->getVenueIdChanged()) {
				$txt .= 'Venue Changed'."\n\n";
			}
			if ($history->getIsVirtualChanged()) {
				$txt .= 'Is Virtual Changed'."\n\n";
			}
			if ($history->getIsPhysicalChanged()) {
				$txt .= 'Is Physical Changed'."\n\n";
			}
			if ($history->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($history->getIsDeleted() ? "Deleted":"Restored")."\n\n";
			}
		}
		$txt .= '</summary>';
		
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

		$txt .= '<summary>';
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getTitleChanged()) {
				$txt .= 'Title Changed: '.$this->getData($history->getTitle())."\n\n";
			}	
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed: '.$this->getBigData($history->getDescription())."\n\n";
			}
			if ($history->getUrlChanged()) {
				$txt .= 'URL Changed: '.$this->getData($history->getUrl())."\n\n";
			}
			if ($history->getTwitterUsernameChanged()) {
				$txt .= 'Twitter Changed: '.$this->getData($history->getTwitterUsername())."\n\n";
			}
			if ($history->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($history->getIsDeleted() ? "Deleted":"Restored")."\n\n";
			}
		}
		$txt .= '</summary>';
		
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

		$txt .= '<summary>';
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getTitleChanged()) {
				$txt .= 'Title Changed: '.$this->getData($history->getTitle())."\n\n";
			}	
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed: '.$this->getBigData($history->getDescription())."\n\n";
			}
			if ($history->getAddressChanged()) {
				$txt .= 'Address Changed: '.$this->getBigData($history->getAddress())."\n\n";
			}
			if ($history->getAddressCodeChanged()) {
				$txt .= 'Address Code Changed: '.$this->getData($history->getAddressCode())."\n\n";
			}	
			if ($history->getLatChanged() || $history->getLngChanged()) {
				$txt .= 'Position on Map Changed'."\n\n";
			}
			if ($history->getAreaIdChanged()) {
				$txt .= 'Area Changed'."\n\n";
			}			
			if ($history->getCountryIdChanged()) {
				$txt .= 'Country Changed'."\n\n";
			}
			if ($history->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($history->getIsDeleted() ? "Deleted":"Restored")."\n\n";
			}
		}
		$txt .= '</summary>';
		
		$txt .= '<updated>'.$history->getCreatedAt()->format("Y-m-d")."T".$history->getCreatedAt()->format("H:i:s")."Z</updated>";
		
		$txt .= '<author><name>'.$CONFIG->siteTitle.'</name></author></entry>'." \r\n";
		
		$this->histories[] = $txt;		
	}
	
	public function addAreaHistory(AreaHistoryModel $history) {
		global $CONFIG;
		
		$siteSlug = $this->site ? $this->site->getSlug() : $history->getSiteSlug();		
		$ourUrl = $CONFIG->isSingleSiteMode ? 
				'http://'.$CONFIG->webSiteDomain.'/area/'.$history->getSlug() : 
				'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/area/'.$history->getSlug() ;
		
		$txt = '<entry>';
		$txt .= '<id>'.$ourUrl.'/area/'.$history->getCreatedAtTimeStamp().'</id>';
		$txt .= '<link href="'.$ourUrl.'"/>';
		
		$txt .= '<title>'.  $this->getData($history->getTitle()).'</title>';

		$txt .= '<summary>';
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getTitleChanged()) {
				$txt .= 'Title Changed: '.$this->getData($history->getTitle())."\n\n";
			}
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed: '.$this->getBigData($history->getDescription())."\n\n";
			}
			if ($history->getParentAreaIdChanged()) {
				$txt .= 'Parent Area Changed'."\n\n";
			}			
			if ($history->getCountryIdChanged()) {
				$txt .= 'Country Changed'."\n\n";
			}
			if ($history->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($history->getIsDeleted() ? "Deleted":"Restored")."\n\n";
			}
		}
		$txt .= '</summary>';
		
		$txt .= '<updated>'.$history->getCreatedAt()->format("Y-m-d")."T".$history->getCreatedAt()->format("H:i:s")."Z</updated>";
		
		$txt .= '<author><name>'.$CONFIG->siteTitle.'</name></author></entry>'." \r\n";
		
		$this->histories[] = $txt;		
	}

	
}

