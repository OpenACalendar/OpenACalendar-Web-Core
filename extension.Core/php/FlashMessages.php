<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class FlashMessages {
	
	/** @var WebSession **/
	protected $WebSession;
	
	function __construct(WebSession $WebSession) {
		$this->WebSession = $WebSession;
	}

	function addMessage($string) {
		$this->WebSession->appendArray("flashMessage", $string);
	}
	
	function addError($string) {
		$this->WebSession->appendArray("flashError", $string);
	}
	
	function getAndClearMessages() {
		$out = $this->WebSession->getArray("flashMessage");
		if ($out) {
			$this->WebSession->setArray("flashMessage", array());
		}
		return $out;
	}
	
	function getAndClearErrors() {
		$out = $this->WebSession->getArray("flashError");
		if ($out) {
			$this->WebSession->setArray("flashError", array());
		}
		return $out;
	}
	
}