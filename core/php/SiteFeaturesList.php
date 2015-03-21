<?php

/**
 *
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteFeaturesList {

	protected $featuresAsTree;

	function __construct($features)
	{
		$this->featuresAsTree = $features;
	}

	public function has($extId, $feature) {
		if (array_key_exists($extId, $this->featuresAsTree) && array_key_exists($feature, $this->featuresAsTree[$extId])) {
			return $this->featuresAsTree[$extId][$feature]->isOn();
		}
		return false;
	}

	public function getAsList() {
		$out = array();
		foreach ($this->featuresAsTree as $features) {
			foreach($features as $feature) {
				$out[] = $feature;
			}
		}
		return $out;
	}

}
