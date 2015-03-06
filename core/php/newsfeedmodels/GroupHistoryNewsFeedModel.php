<?php

namespace newsfeedmodels;

use models\GroupHistoryModel;

	/**
	 *
	 * @package Core
	 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
	 * @license http://ican.openacalendar.org/license.html 3-clause BSD
	 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
	 * @author James Baster <james@jarofgreen.co.uk>
	 */

class GroupHistoryNewsFeedModel implements  \InterfaceNewsFeedModel {



	/** @var GroupHistoryModel */
	protected $groupHistoryModel;

	function __construct($groupHistoryModel)
	{
		$this->groupHistoryModel = $groupHistoryModel;
	}


	/** @return \DateTime */
	public function getCreatedAt()
	{
		return $this->groupHistoryModel->getCreatedAt();
	}

	public function getID()
	{
		// For ID, must make sure we use Slug, not SlugForURL otherwise ID will change!
		return $this->getURL().'/history/'.$this->groupHistoryModel->getCreatedAtTimeStamp();
	}

	public function getURL()
	{
		global $CONFIG;
		return $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/group/'.$this->groupHistoryModel->getSlug() :
			'http://'.$this->site_slug.".".$CONFIG->webSiteDomain.'/group/'.$this->groupHistoryModel->getSlug() ;
	}

	public function getTitle()
	{
		return $this->groupHistoryModel->getTitle();
	}

	public function getSummary()
	{
		$txt = '';

		if ($this->groupHistoryModel->getIsNew()) {
			$txt .= 'New! '."\n";
		}
		if ($this->groupHistoryModel->isAnyChangeFlagsUnknown()) {
			$txt .= $this->groupHistoryModel->getDescription();
		} else {
			if ($this->groupHistoryModel->getTitleChanged()) {
				$txt .= 'Title Changed. '."\n";
			}
			if ($this->groupHistoryModel->getDescriptionChanged()) {
				$txt .= 'Description Changed. '."\n";
			}
			if ($this->groupHistoryModel->getUrlChanged()) {
				$txt .= 'URL Changed. '."\n";
			}
			if ($this->groupHistoryModel->getTwitterUsernameChanged()) {
				$txt .= 'Twitter Changed. '."\n";
			}
			if ($this->groupHistoryModel->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($this->groupHistoryModel->getIsDeleted() ? "Deleted":"Restored")."\n\n";
			}
		}
		return $txt;
	}
}

