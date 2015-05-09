<?php


namespace sitefeatures;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupFeature extends \BaseSiteFeature {

	function __construct()
	{
		$this->is_on = false;
	}

	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getFeatureId()
	{
		return 'Group';
	}

	public function getTitle() {
		return 'Group';
	}

	public function getDescription() {
		return 'Events can be grouped by organiser to make it easy to find other events from the same people.';
	}

}
