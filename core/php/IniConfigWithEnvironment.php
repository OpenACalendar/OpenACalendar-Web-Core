<?php



/**
 *
 * 
 * This loads ini files with variables in sections by Environments or a Common section for all Environments.
 * 
 * eg
 * 
 * [Common]
 * FromEmail=james@example.com
 * FromName=James Baster
 * 
 * [EnvironmentTest]
 * To=james@example.com
 * 
 * [EnvironmentReal]
 * To=emaillist@example.com
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IniConfigWithEnvironment {
	protected $environment;
	protected $data;
	function __construct($environment, $filename) {
		$this->environment = $environment;
		$this->data = parse_ini_file($filename, true);
	}
	function hasValue($key) {
		if  (isset($this->data['Environment'.$this->environment][$key]) && $this->data['Environment'.$this->environment][$key]) {
			return true;
		} else {
			return isset($this->data['Common'][$key]) && $this->data['Common'][$key];
		}
	}
	function get($key) {
		if (isset($this->data['Environment'.$this->environment][$key])) {
			return $this->data['Environment'.$this->environment][$key];
		} 
		if (isset($this->data['Common'][$key])) {
			return $this->data['Common'][$key];
		} 
	}
}


