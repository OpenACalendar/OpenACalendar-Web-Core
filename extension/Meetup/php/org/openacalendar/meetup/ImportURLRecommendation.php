<?php

namespace org\openacalendar\meetup;

/**
 *
 * @package org.openacalendar.meetup
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLRecommendation implements  \InterfaceImportURLRecommendation {

	protected $newURL;

	function __construct( $url ) {
		$meetupURLParser = new MeetupURLParser($url);
		if ($meetupURLParser->getGroupName() && $meetupURLParser->getEventId()) {
			$this->newURL = 'http://www.meetup.com/'.$meetupURLParser->getGroupName().'/';
		}
	}

	public function hasNewURL() {
		return (boolean)$this->newURL;
	}

	public function getNewURL() {
		return $this->newURL;
	}

	public function getTitle() {
		return "Do you want to import all events in this group instead?";
	}

	public function getDescription() {
		return "This will import one event only. Instead, you can import all current and future events in this group.";
	}

	public function getActionAcceptLabel() {
		return "Yes, import all events in this group.";
	}

	public function getActionRefuseLabel() {
		return "No, just import one event.";
	}

	public function getExtensionID() {
		return "org.openacalendar.meetup";
	}

	public function getRecommendationID() {
		return "ImportGroupInstead";
	}

}
