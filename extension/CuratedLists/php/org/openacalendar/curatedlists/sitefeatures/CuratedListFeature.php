<?php


namespace org\openacalendar\curatedlists\sitefeatures;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListFeature extends \BaseSiteFeature {

	function __construct()
	{
		$this->is_on = false;
	}

	public function getExtensionId()
	{
		return 'org.openacalendar.curatedlists';
	}

	public function getFeatureId()
	{
		return 'CuratedList';
	}


	public function getTitle() {
		return 'Curated Lists';
	}

	public function getDescription() {
		return 'Curated Lists allow users to build lists of events and share them with others.';
	}

}
