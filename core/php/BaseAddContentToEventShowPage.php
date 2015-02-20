<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseAddContentToEventShowPage {

	public abstract function  getParameters();

	public function getTemplatesAfterDetails() {
		return array();
	}

	public function getTemplatesAtEnd() {
		return array();
	}

}

