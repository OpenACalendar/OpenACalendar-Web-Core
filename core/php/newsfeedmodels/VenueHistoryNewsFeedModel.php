<?php

namespace newsfeedmodels;

use models\VenueHistoryModel;

	/**
	 *
	 * @package Core
	 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
	 * @license http://ican.openacalendar.org/license.html 3-clause BSD
	 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
	 * @author James Baster <james@jarofgreen.co.uk>
	 */


class VenueHistoryNewsFeedModel implements  \InterfaceNewsFeedModel {



	/** @var VenueHistoryModel */
	protected $venueHistoryModel;

	function __construct($venueHistoryModel)
	{
		$this->venueHistoryModel = $venueHistoryModel;
	}


	/** @return \DateTime */
	public function getCreatedAt()
	{
		return $this->venueHistoryModel->getCreatedAt();
	}

	public function getID()
	{

		// For ID, must make sure we use Slug, not SlugForURL otherwise ID will change!
		return $this->getURL().'/history/'.$this->venueHistoryModel->getCreatedAtTimeStamp();
	}

	public function getURL()
	{
		global $CONFIG;
		return $CONFIG->isSingleSiteMode ?
			'http://'.$CONFIG->webSiteDomain.'/venue/'.$this->venueHistoryModel->getSlug() :
			'http://'.$this->site_slug.".".$CONFIG->webSiteDomain.'/venue/'.$this->venueHistoryModel->getSlug() ;
	}

	public function getTitle()
	{
		return $this->venueHistoryModel->getTitle();
	}

	public function getSummary()
	{
		$txt = '';

		if ($this->venueHistoryModel->getIsNew()) {
			$txt .= 'New! '."\n";
		}
		if ($this->venueHistoryModel->isAnyChangeFlagsUnknown()) {
			$txt .= $this->venueHistoryModel->getDescription();
		} else {
			if ($this->venueHistoryModel->getTitleChanged()) {
				$txt .= 'Title Changed. '."\n";
			}
			if ($this->venueHistoryModel->getDescriptionChanged()) {
				$txt .= 'Description Changed. '."\n";
			}
			if ($this->venueHistoryModel->getAddressChanged()) {
				$txt .= 'Address Changed. '."\n";
			}
			if ($this->venueHistoryModel->getAddressCodeChanged()) {
				$txt .= 'Address Code Changed. '."\n";
			}
			if ($this->venueHistoryModel->getLatChanged() || $this->venueHistoryModel->getLngChanged()) {
				$txt .= 'Position on Map Changed. '."\n";
			}
			if ($this->venueHistoryModel->getAreaIdChanged()) {
				$txt .= 'Area Changed. '."\n";
			}
			if ($this->venueHistoryModel->getCountryIdChanged()) {
				$txt .= 'Country Changed. '."\n";
			}
			if ($this->venueHistoryModel->getIsDeletedChanged()) {
				$txt .= 'Deleted Changed: '.($this->venueHistoryModel->getIsDeleted() ? "Deleted":"Restored")."\n\n";
			}
		}
		return $txt;
	}
}

