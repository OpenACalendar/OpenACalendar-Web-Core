<?php

namespace newsfeedmodels;

use models\TagHistoryModel;

	/**
	 *
	 * @package Core
	 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
	 * @license http://ican.openacalendar.org/license.html 3-clause BSD
	 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
	 * @author James Baster <james@jarofgreen.co.uk>
	 */

class TagHistoryNewsFeedModel implements  \InterfaceNewsFeedModel {


	/** @var TagHistoryModel */
	protected $tagHistoryModel;

	function __construct($tagHistoryModel)
	{
		$this->tagHistoryModel = $tagHistoryModel;
	}


	/** @return \DateTime */
	public function getCreatedAt()
	{
		return $this->tagHistoryModel->getCreatedAt();
	}

	public function getID()
	{
		// For ID, must make sure we use Slug, not SlugForURL otherwise ID will change!
		return $this->getURL().'/history/'.$this->tagHistoryModel->getCreatedAtTimeStamp();
	}

	public function getURL()
	{
		global $CONFIG;
		return $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/tag/'.$this->tagHistoryModel->getSlug() :
			'http://'.$this->site_slug.".".$CONFIG->webSiteDomain.'/tag/'.$this->tagHistoryModel->getSlug() ;
	}

	public function getTitle()
	{
		return $this->tagHistoryModel->getTitle();
	}

	public function getSummary()
	{
		$txt = '';

		if ($this->tagHistoryModel->getIsNew()) {
			$txt .= 'New! '."\n";
		}
		if ($this->tagHistoryModel->isAnyChangeFlagsUnknown()) {
			$txt .= $this->tagHistoryModel->getDescription();
		} else {
			if ($this->tagHistoryModel->getTitleChanged()) {
				$txt .= 'Title Changed. '."\n";
			}
			if ($this->tagHistoryModel->getDescriptionChanged()) {
				$txt .= 'Description Changed. '."\n";
			}
			if ($this->tagHistoryModel->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($this->tagHistoryModel->getIsDeleted() ? "Deleted":"Restored")."\n\n";
			}
		}
		return $txt;
	}
}

