<?php
namespace api1exportbuilders;


use models\SiteModel;
use models\EventModel;
use models\EventHistoryModel;
use models\GroupHistoryModel;
use models\VenueHistoryModel;
use models\AreaHistoryModel;
use models\TagHistoryModel;
use models\ImportURLHistoryModel;
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
		if ($history->getIsNew()) {
				$txt .= 'New! ';
		}
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getSummaryChanged()) {
				$txt .= 'Summary Changed. ';
			}
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed. ';
			}
			if ($history->getUrlChanged()) {
				$txt .= 'URL Changed. ';
			}
			if ($history->getStartAtChanged()) {
				$txt .= 'Start Changed. ';
			}
			if ($history->getEndAtChanged()) {
				$txt .= 'End Changed. ';
			}
			if ($history->getCountryIdChanged()) {
				$txt .= 'Country Changed.';
			}
			if ($history->getTimezoneChanged()) {
				$txt .= 'Timezone Changed. ';
			}
			if ($history->getAreaIdChanged()) {
				$txt .= 'Area Changed. ';
			}
			if ($history->getVenueIdChanged()) {
				$txt .= 'Venue Changed. ';
			}
			if ($history->getIsVirtualChanged()) {
				$txt .= 'Is Virtual Changed. ';
			}
			if ($history->getIsPhysicalChanged()) {
				$txt .= 'Is Physical Changed.';
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
		if ($history->getIsNew()) {
				$txt .= 'New! ';
		}
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getTitleChanged()) {
				$txt .= 'Title Changed. ';
			}	
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed. ';
			}
			if ($history->getUrlChanged()) {
				$txt .= 'URL Changed. ';
			}
			if ($history->getTwitterUsernameChanged()) {
				$txt .= 'Twitter Changed. ';
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
	
	
	public function addTagHistory(TagHistoryModel $history) {
		global $CONFIG;
		
		$siteSlug = $this->site ? $this->site->getSlug() : $history->getSiteSlug();		
		$ourUrl = $CONFIG->isSingleSiteMode ? 
				'http://'.$CONFIG->webSiteDomain.'/tag/'.$history->getTagSlug() : 
				'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/tag/'.$history->getTagSlug() ;
		
		$txt = '<entry>';
		$txt .= '<id>'.$ourUrl.'/history/'.$history->getCreatedAtTimeStamp().'</id>';
		$txt .= '<link href="'.$ourUrl.'"/>';
		
		$txt .= '<title>'.  $this->getData($history->getTitle()).'</title>';

		$txt .= '<summary>';
		if ($history->getIsNew()) {
				$txt .= 'New! ';
		}
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getTitleChanged()) {
				$txt .= 'Title Changed. ';
			}	
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed. ';
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
		if ($history->getIsNew()) {
				$txt .= 'New! ';
		}
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getTitleChanged()) {
				$txt .= 'Title Changed. ';
			}	
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed. ';
			}
			if ($history->getAddressChanged()) {
				$txt .= 'Address Changed. ';
			}
			if ($history->getAddressCodeChanged()) {
				$txt .= 'Address Code Changed. ';
			}	
			if ($history->getLatChanged() || $history->getLngChanged()) {
				$txt .= 'Position on Map Changed. ';
			}
			if ($history->getAreaIdChanged()) {
				$txt .= 'Area Changed. ';
			}			
			if ($history->getCountryIdChanged()) {
				$txt .= 'Country Changed. ';
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
		$txt .= '<id>'.$ourUrl.'/history/'.$history->getCreatedAtTimeStamp().'</id>';
		$txt .= '<link href="'.$ourUrl.'"/>';
		
		$txt .= '<title>'.  $this->getData($history->getTitle()).'</title>';

		$txt .= '<summary>';
		if ($history->getIsNew()) {
				$txt .= 'New! ';
		}
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getDescription());
		} else {
			if ($history->getTitleChanged()) {
				$txt .= 'Title Changed. ';
			}
			if ($history->getDescriptionChanged()) {
				$txt .= 'Description Changed. ';
			}
			if ($history->getParentAreaIdChanged()) {
				$txt .= 'Parent Area Changed. ';
			}			
			if ($history->getCountryIdChanged()) {
				$txt .= 'Country Changed. ';
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
	
	public function addImportURLHistory(ImportURLHistoryModel $history) {
		global $CONFIG;
		
		$siteSlug = $this->site ? $this->site->getSlug() : $history->getSiteSlug();		
		$ourUrl = $CONFIG->isSingleSiteMode ? 
				'http://'.$CONFIG->webSiteDomain.'/importurl/'.$history->getSlug() : 
				'http://'.$siteSlug.".".$CONFIG->webSiteDomain.'/importurl/'.$history->getSlug() ;
		
		$txt = '<entry>';
		$txt .= '<id>'.$ourUrl.'/history/'.$history->getCreatedAtTimeStamp().'</id>';
		$txt .= '<link href="'.$ourUrl.'"/>';
		
		$txt .= '<title>'.  $this->getData($history->getTitle()).'</title>';

		$txt .= '<summary>';
		if ($history->getIsNew()) {
				$txt .= 'New! ';
		}
		if ($history->isAnyChangeFlagsUnknown()) {
			$txt .= $this->getBigData($history->getTitle());
		} else {
			if ($history->getTitleChanged()) {
				$txt .= 'Title Changed. ';
			}
			if ($history->getCountryIdChanged()) {
				$txt .= 'Country Changed. ';
			}
			if ($history->getAreaIdChanged()) {
				$txt .= 'Area Changed. ';
			}
			if ($history->getIsEnabledChanged()) {
				$txt .= 'Enabled Changed: '.($history->getIsEnabled() ? "Enabled":"Disabled")."\n\n";
			}
			if ($history->getExpiredAtChanged()) {
				$txt .= 'Expired Changed: '.($history->getExpiredAt() ? "Expired":"Not Expired")."\n\n";
			}
		}
		$txt .= '</summary>';
		
		$txt .= '<updated>'.$history->getCreatedAt()->format("Y-m-d")."T".$history->getCreatedAt()->format("H:i:s")."Z</updated>";
		
		$txt .= '<author><name>'.$CONFIG->siteTitle.'</name></author></entry>'." \r\n";
		
		$this->histories[] = $txt;		
	}

	
}

