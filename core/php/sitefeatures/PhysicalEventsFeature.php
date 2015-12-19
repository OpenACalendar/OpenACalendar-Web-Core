<?php


namespace sitefeatures;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class PhysicalEventsFeature extends \BaseSiteFeature {

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
		return 'PhysicalEvents';
	}


	public function getTitle() {
		return 'Physical Events';
	}

	public function getDescription() {
		return 'Events happen in a place. Venues can me set and browsed.';
	}

}
