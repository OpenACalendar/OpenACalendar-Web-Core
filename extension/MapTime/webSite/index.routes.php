<?php
/**
 *
 * @package org.openacalendar.maptime
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$app->match('/map/time', 'org\openacalendar\maptime\site\controllers\MapTimeController::index');
$app->match('/map/time/', 'org\openacalendar\maptime\site\controllers\MapTimeController::index');
$app->match('/map/time/getdata.json', 'org\openacalendar\maptime\site\controllers\MapTimeController::getDataJson');

