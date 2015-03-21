<?php


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseSiteFeature
{

	protected $is_on = false;

	abstract public function getExtensionId();
	abstract public function getFeatureId();

	/**
	 * @return boolean
	 */
	public function isOn()
	{
		return $this->is_on;
	}

	/**
	 * @param boolean $is_on
	 */
	public function setOn($is_on)
	{
		$this->is_on = $is_on;
	}


	public function getTitle() {
		return $this->getFeatureId();
	}

	public function getDescription() {
		return '';
	}


}