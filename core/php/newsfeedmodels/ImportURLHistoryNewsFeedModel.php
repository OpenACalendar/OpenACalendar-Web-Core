<?php

namespace newsfeedmodels;

use models\ImportURLHistoryModel;
use models\SiteModel;

/**
	 *
	 * @package Core
	 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
	 * @license http://ican.openacalendar.org/license.html 3-clause BSD
	 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
	 * @author James Baster <james@jarofgreen.co.uk>
	 */


class ImportURLHistoryNewsFeedModel implements  \InterfaceNewsFeedModel {


	/** @var SiteModel */
	protected $siteModel;



	/** @var ImportURLHistoryModel */
	protected $importURLHistoryModel;

	function __construct($importURLHistoryModel, SiteModel $siteModel)
	{
		$this->importURLHistoryModel = $importURLHistoryModel;
		$this->siteModel = $siteModel;
	}


	/** @return \DateTime */
	public function getCreatedAt()
	{
		return $this->importURLHistoryModel->getCreatedAt();
	}

	public function getID()
	{
		// For ID, must make sure we use Slug, not SlugForURL otherwise ID will change!
		return $this->getURL().'/history/'.$this->importURLHistoryModel->getCreatedAtTimeStamp();
	}

	public function getURL()
	{
		global $CONFIG;
		return $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/importurl/'.$this->importURLHistoryModel->getSlug() :
			'http://'.$this->siteModel->getSlug().".".$CONFIG->webSiteDomain.'/importurl/'.$this->importURLHistoryModel->getSlug() ;
	}

	public function getTitle()
	{
		return $this->importURLHistoryModel->getTitle();
	}

	public function getSummary()
	{
		$txt = '';

		if ($this->importURLHistoryModel->getIsNew()) {
			$txt .= 'New! '."\n";
		}
		if ($this->importURLHistoryModel->isAnyChangeFlagsUnknown()) {
			$txt .= $this->importURLHistoryModel->getTitle();
		} else {
			if ($this->importURLHistoryModel->getTitleChanged()) {
				$txt .= 'Title Changed. '."\n";
			}
			if ($this->importURLHistoryModel->getCountryIdChanged()) {
				$txt .= 'Country Changed. '."\n";
			}
			if ($this->importURLHistoryModel->getAreaIdChanged()) {
				$txt .= 'Area Changed. '."\n";
			}
			if ($this->importURLHistoryModel->getIsEnabledChanged()) {
				$txt .= 'Enabled Changed: '.($this->importURLHistoryModel->getIsEnabled() ? "Enabled":"Disabled")."\n\n";
			}
			if ($this->importURLHistoryModel->getExpiredAtChanged()) {
				$txt .= 'Expired Changed: '.($this->importURLHistoryModel->getExpiredAt() ? "Expired":"Not Expired")."\n\n";
			}
		}
		return $txt;
	}
}

