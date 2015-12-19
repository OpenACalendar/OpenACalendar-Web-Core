<?php

namespace sysadmin;

/**
 * At the moment this only allows one param. 
 * Later we should make it so it allows several, with "" escaping to allow spaces.
 * The API takes an $idx now to reflect this
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ActionParser {

	protected $command;
	protected $params = array();
	
	public function __construct($in) {
		$bits = explode(" ", $in, 2);
		$this->command = $bits[0];
		$this->params = isset($bits[1]) ? array( $bits[1] ) : array();
	}

	/**
	 * @return mixed In Lower Case
	 */
	public function getCommand() {
		return strtolower($this->command);
	}

	public function getParam($idx) {
		if ((count($this->params) - 1) >= $idx) {
			return $this->params[$idx];
		}
	}
	
	public function getParamBoolean($idx) {
		if ((count($this->params) - 1) >= $idx) {
			$data = $this->params[$idx];
			if (in_array(substr($data,0,1),array('Y','y','T','t','1'))) {
				return true;
			}
			if (in_array(substr($data,0,1),array('N','n','F','f','0'))) {
				return false;
			}
		}
		return null;
	}


}


