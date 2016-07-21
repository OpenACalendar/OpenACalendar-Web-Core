<?php

namespace newsfeedmodels;

use api1exportbuilders\TraitATOM;
use models\AreaHistoryModel;
use models\SiteModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


class AreaHistoryNewsFeedModel implements  \InterfaceNewsFeedModel {



	/** @var SiteModel */
	protected $siteModel;

	/** @var AreaHistoryModel */
	protected $areaHistoryModel;

	function __construct($areaHistoryModel, SiteModel $siteModel)
	{
		$this->areaHistoryModel = $areaHistoryModel;
		$this->siteModel = $siteModel;
	}


	/** @return \DateTime */
	public function getCreatedAt()
	{
		return $this->areaHistoryModel->getCreatedAt();
	}

	public function getID()
	{
		// For ID, must make sure we use Slug, not SlugForURL otherwise ID will change!
		return $this->getURL().'/history/'.$this->areaHistoryModel->getCreatedAtTimeStamp();
	}

	public function getURL()
	{
		global $CONFIG;
		return $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/area/'.$this->areaHistoryModel->getSlug() :
			'http://'.$this->siteModel->getSlug().".".$CONFIG->webSiteDomain.'/area/'.$this->areaHistoryModel->getSlug() ;
	}

	public function getTitle()
	{
		return $this->areaHistoryModel->getTitle();
	}

	public function getSummary()
	{
		$txt = '';

		if ($this->areaHistoryModel->getIsNew()) {
			$txt .= 'New! '."\n";
		}
		if ($this->areaHistoryModel->isAnyChangeFlagsUnknown()) {
			$txt .= $this->areaHistoryModel->getDescription();
		} else {
			if ($this->areaHistoryModel->getTitleChanged()) {
				$txt .= 'Title Changed. '."\n";
			}
			if ($this->areaHistoryModel->getDescriptionChanged()) {
				$txt .= 'Description Changed. '."\n";
			}
			if ($this->areaHistoryModel->getParentAreaIdChanged()) {
				$txt .= 'Parent Area Changed. '."\n";
			}
			if ($this->areaHistoryModel->getCountryIdChanged()) {
				$txt .= 'Country Changed. '."\n";
			}
			if ($this->areaHistoryModel->getMinMaxLatLngChanged()) {
				$txt .= 'Bounds Changed. '."\n";
			}
			if ($this->areaHistoryModel->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($this->areaHistoryModel->getIsDeleted() ? "Deleted":"Restored")."\n\n";
			}
		}
		return $txt;
	}
}

