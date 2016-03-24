<?php

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$app->match('/sysadmin/site/{siteid}/curatedlist', 'org\openacalendar\curatedlists\sysadmin\controllers\CuratedListListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/curatedlist/', 'org\openacalendar\curatedlists\sysadmin\controllers\CuratedListListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/curatedlist/{slug}', 'org\openacalendar\curatedlists\sysadmin\controllers\CuratedListController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');
$app->match('/sysadmin/site/{siteid}/curatedlist/{slug}/', 'org\openacalendar\curatedlists\sysadmin\controllers\CuratedListController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');

