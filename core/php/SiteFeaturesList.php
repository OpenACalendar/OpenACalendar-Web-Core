<?php
use models\SiteModel;

/**
 *
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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

	/**
	 *
	 * @deprecated */
	public function  setFeaturesOnSite(SiteModel $siteModel)
	{
		if (isset($this->featuresAsTree['org.openacalendar']) && isset($this->featuresAsTree['org.openacalendar']['Map'])) {
			$siteModel->setIsFeatureMap($this->featuresAsTree['org.openacalendar']['Map']->isOn());
		}
		if (isset($this->featuresAsTree['org.openacalendar']) && isset($this->featuresAsTree['org.openacalendar']['Group'])) {
			$siteModel->setIsFeatureGroup($this->featuresAsTree['org.openacalendar']['Group']->isOn());
		}
		if (isset($this->featuresAsTree['org.openacalendar']) && isset($this->featuresAsTree['org.openacalendar']['Tag'])) {
			$siteModel->setIsFeatureTag($this->featuresAsTree['org.openacalendar']['Tag']->isOn());
		}
		if (isset($this->featuresAsTree['org.openacalendar']) && isset($this->featuresAsTree['org.openacalendar']['Importer'])) {
			$siteModel->setIsFeatureImporter($this->featuresAsTree['org.openacalendar']['Importer']->isOn());
		}
		if (isset($this->featuresAsTree['org.openacalendar']) && isset($this->featuresAsTree['org.openacalendar']['PhysicalEvents'])) {
			$siteModel->setIsFeaturePhysicalEvents($this->featuresAsTree['org.openacalendar']['PhysicalEvents']->isOn());
		}
		if (isset($this->featuresAsTree['org.openacalendar']) && isset($this->featuresAsTree['org.openacalendar']['VirtualEvents'])) {
			$siteModel->setIsFeatureVirtualEvents($this->featuresAsTree['org.openacalendar']['VirtualEvents']->isOn());
		}
		if (isset($this->featuresAsTree['org.openacalendar.curatedlists']) && isset($this->featuresAsTree['org.openacalendar.curatedlists']['CuratedList'])) {
			$siteModel->setIsFeatureCuratedList($this->featuresAsTree['org.openacalendar.curatedlists']['CuratedList']->isOn());
		}
	}

}
