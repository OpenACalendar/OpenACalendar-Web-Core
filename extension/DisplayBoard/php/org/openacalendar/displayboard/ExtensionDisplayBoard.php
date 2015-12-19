<?php

namespace org\openacalendar\displayboard;

use appconfiguration\AppConfigurationDefinition;

/**
 *
 * @package org.openacalendar.displayboard
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionDisplayBoard extends \BaseExtension {
	
	public function getId() {
		return 'org.openacalendar.displayboard';
	}
	
	public function getTitle() {
		return "Displayboard";
	}
	
	public function getDescription() {
		return "Displayboard";
	}
	
}
