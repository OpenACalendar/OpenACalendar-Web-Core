<?php

use models\SiteModel;
use models\VenueModel;
use models\UserAccountModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
interface InterfaceNewsFeedModel {


	/** @return \DateTime */
	public function getCreatedAt();

	public function getID();

	public function getURL();

	public function getTitle();

	public function getSummary();

}