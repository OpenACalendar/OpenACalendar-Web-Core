<?php


use Silex\Application;

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
	
	protected $coreExtension;
			
	function __construct(Application $app) {
		$this->coreExtension = new \ExtensionCore($app);
	}
	
	public function addExtension($dir, BaseExtension $extension) {
		$this->extensions[array_pop(explode("/",$dir))] = $extension;
	}
	
	public function getExtensions() {
		return $this->extensions;
	}
	
	public function getExtensionsIncludingCore() {
		return array_merge(array($this->coreExtension), $this->extensions);
	}
	
	public function getExtensionByDir($dir) {
		return $this->extensions[$dir];
	}
		
	public function getCoreExtension() {
		return $this->coreExtension;
	}
	
	public function getExtensionById($id) {
		if ($this->coreExtension->getId() == $id) {
			return $this->coreExtension;
		}
		foreach($this->extensions as $extension) {
			if ($extension->getId() == $id) {
				return $extension;
			}
		}
	}

	public function hasExtensionID($id) {
		if ($this->coreExtension->getId() == $id) {
			return true;
		}
		foreach($this->extensions as $extension) {
			if ($extension->getId() == $id) {
				return true;
			}
		}
		return false;
	}

}

