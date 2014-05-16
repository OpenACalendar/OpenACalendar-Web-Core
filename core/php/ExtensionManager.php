<?php



/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


class ExtensionManager {

	protected $extensions = array();
	
	public function addExtension($dir, BaseExtension $extension) {
		$this->extensions[array_pop(explode("/",$dir))] = $extension;
	}
	
	public function getExtensions() {
		return $this->extensions;
	}
	
	public function getExtensionByDir($dir) {
		return $this->extensions[$dir];
	}
	
}

