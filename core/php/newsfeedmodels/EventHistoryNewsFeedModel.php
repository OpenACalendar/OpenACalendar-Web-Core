<?php

namespace newsfeedmodels;

use models\EventHistoryModel;
use models\SiteModel;

/**
	 *
	 * @package Core
	 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
	 * @license http://ican.openacalendar.org/license.html 3-clause BSD
	 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
	 * @author James Baster <james@jarofgreen.co.uk>
	 */

class EventHistoryNewsFeedModel implements  \InterfaceNewsFeedModel {


	/** @var SiteModel */
	protected $siteModel;

	/** @var EventHistoryModel */
	protected $eventHistoryModel;

	function __construct($eventHistoryModel, SiteModel $siteModel)
	{
		$this->eventHistoryModel = $eventHistoryModel;
		$this->siteModel = $siteModel;
	}


	/** @return \DateTime */
	public function getCreatedAt()
	{
		return $this->eventHistoryModel->getCreatedAt();
	}

	public function getID()
	{
		/// For ID, must make sure we use Slug, not SlugForURL otherwise ID will change!
		return $this->getURL().'/history/'.$this->eventHistoryModel->getCreatedAtTimeStamp();
	}

	public function getURL()
	{
		// this should use slugForURL tho @TODO
		global $CONFIG;
		return $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/event/'.$this->eventHistoryModel->getSlug() :
			'http://'.$this->siteModel->getSlug().".".$CONFIG->webSiteDomain.'/event/'.$this->eventHistoryModel->getSlug() ;
	}

	public function getTitle()
	{
		return $this->eventHistoryModel->getSummaryDisplay();
	}

	public function getSummary()
	{
		$txt = '';
		if ($this->eventHistoryModel->getIsNew()) {
			$txt .= "New! \n";
		}
		if ($this->eventHistoryModel->isAnyChangeFlagsUnknown()) {
			$txt .= $this->eventHistoryModel->getDescription();
		} else {
			if ($this->eventHistoryModel->getSummaryChanged()) {
				$txt .= "Summary Changed. \n";
			}
			if ($this->eventHistoryModel->getDescriptionChanged()) {
				$txt .= "Description Changed. \n";
			}
			if ($this->eventHistoryModel->getUrlChanged()) {
				$txt .= "URL Changed. \n";
			}
			if ($this->eventHistoryModel->getStartAtChanged()) {
				$txt .= "Start Changed. \n";
			}
			if ($this->eventHistoryModel->getEndAtChanged()) {
				$txt .= "End Changed. \n";
			}
			if ($this->eventHistoryModel->getCountryIdChanged()) {
				$txt .= "Country Changed.\n";
			}
			if ($this->eventHistoryModel->getTimezoneChanged()) {
				$txt .= "Timezone Changed. \n";
			}
			if ($this->eventHistoryModel->getAreaIdChanged()) {
				$txt .= "Area Changed. \n";
			}
			if ($this->eventHistoryModel->getVenueIdChanged()) {
				$txt .= "Venue Changed. \n";
			}
			if ($this->eventHistoryModel->getIsVirtualChanged()) {
				$txt .= "Is Virtual Changed. \n";
			}
			if ($this->eventHistoryModel->getIsPhysicalChanged()) {
				$txt .= "Is Physical Changed.\n";
			}
			if ($this->eventHistoryModel->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($this->eventHistoryModel->getIsDeleted() ? "Deleted":"Restored")."\n";
			}
			if ($this->eventHistoryModel->getIsCancelledChanged()) {
				$txt .= 'cancelled Changed: '.($this->eventHistoryModel->getIsCancelled() ? "Cancelled":"Restored")."\n";
			}
		}
		return $txt;
	}
}

