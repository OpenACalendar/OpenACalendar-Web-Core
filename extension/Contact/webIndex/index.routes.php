<?php
/**
 *
 * @package org.openacalendar.contact
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */



$app->match('/contact', 'org\openacalendar\contact\index\controllers\IndexController::contact');
$app->match('/contact/', 'org\openacalendar\contact\index\controllers\IndexController::contact');

