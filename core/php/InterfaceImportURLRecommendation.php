<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
interface InterfaceImportURLRecommendation {

	public function getNewURL();

	public function getTitle();

	public function getDescription();

	public function getActionAcceptLabel();

	public function getActionRefuseLabel();

	public function getExtensionID();

	public function getRecommendationID();

}
