<?php
/**
 *
 * This contains routes that are available in multi site mode only!
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$app->match('/create', "index\controllers\IndexController::create")
	->before($permissionCreateSiteRequired)
	->before($canChangeSite);

