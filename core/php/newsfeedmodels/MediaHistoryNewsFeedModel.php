<?php

namespace newsfeedmodels;

use models\SiteModel;
use models\MediaHistoryModel;

	/**
	 *
	 * @package Core
	 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
	 * @license http://ican.openacalendar.org/license.html 3-clause BSD
	 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
	 * @author James Baster <james@jarofgreen.co.uk>
	 */

class MediaHistoryNewsFeedModel implements  \InterfaceNewsFeedModel {


	/** @var SiteModel */
	protected $siteModel;


	/** @var MediaHistoryModel */
	protected $mediaHistoryModel;

	function __construct($mediaHistoryModel, SiteModel $siteModel)
	{
		$this->mediaHistoryModel = $mediaHistoryModel;
		$this->siteModel = $siteModel;
	}


	/** @return \DateTime */
	public function getCreatedAt()
	{
		return $this->mediaHistoryModel->getCreatedAt();
	}

	public function getID()
	{
		// For ID, must make sure we use Slug, not SlugForURL otherwise ID will change!
		return $this->getURL().'/history/'.$this->mediaHistoryModel->getCreatedAtTimeStamp();
	}

	public function getURL()
	{
		global $CONFIG;
		return $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/media/'.$this->mediaHistoryModel->getSlug() :
			'http://'.$this->siteModel->getSlug().".".$CONFIG->webSiteDomain.'/media/'.$this->mediaHistoryModel->getSlug() ;
	}

	public function getTitle()
	{
		return $this->mediaHistoryModel->getTitle();
	}

	public function getSummary()
	{
		$txt = '';

		if ($this->mediaHistoryModel->getIsNew()) {
			$txt .= 'New! '."\n";
		}
		if ($this->mediaHistoryModel->isAnyChangeFlagsUnknown()) {
			$txt .= $this->mediaHistoryModel->getDescription();
		} else {
			if ($this->mediaHistoryModel->getTitleChanged()) {
				$txt .= 'Title Changed. '."\n";
			}
			if ($this->mediaHistoryModel->getSourceTextChanged()) {
				$txt .= 'Source Changed. '."\n";
			}
			if ($this->mediaHistoryModel->getSourceURLChanged()) {
				$txt .= 'Source URL Changed. '."\n";
			}
		}
		return $txt;
	}
}

