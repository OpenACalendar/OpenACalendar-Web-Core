<?php

namespace org\openacalendar\curatedlists;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionCuratedLists extends \BaseExtension {

	public function getId() {
		return 'org.openacalendar.curatedlists';
	}

	public function getTitle() {
		return "Curated Lists";
	}

	public function getDescription() {
		return "Curated Lists";
	}

	public function getTasks() {
		return array(
			new \org\openacalendar\curatedlists\tasks\UpdateCuratedListHistoryChangeFlagsTask($this->app),
		);
	}

}
