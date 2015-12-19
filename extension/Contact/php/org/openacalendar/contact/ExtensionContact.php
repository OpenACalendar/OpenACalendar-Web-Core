<?php

namespace org\openacalendar\contact;

/**
 *
 * @package org.openacalendar.contact
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionContact extends \BaseExtension {

	public function getId() {
		return 'org.openacalendar.contact';
	}

	public function getTitle() {
		return "Contact";
	}

	public function getDescription() {
		return "Contact Us Pages";
	}

	public function getSysAdminLinks() {
		return array(array('title'=>'Contact Support','url'=>'/sysadmin/contactsupport'));
	}

}
