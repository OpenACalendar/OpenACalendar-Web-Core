<?php
/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$app->match('/curatedlist', "site\controllers\CuratedListListController::index"); 
$app->match('/curatedlist/', "site\controllers\CuratedListListController::index"); 

$app->match('/curatedlist/new/', "site\controllers\CuratedListNewController::newCuratedList")
		->before($permissionCalendarChangeRequired)
		->before($featureCuratedListRequired)
		->before($canChangeSite); 

$app->match('/curatedlist/{slug}', "site\controllers\CuratedListController::show")
		->assert('slug', FRIENDLY_SLUG_REGEX); 
$app->match('/curatedlist/{slug}/', "site\controllers\CuratedListController::show")
		->assert('slug', FRIENDLY_SLUG_REGEX); 
$app->match('/curatedlist/{slug}/edit', "site\controllers\CuratedListController::edit")
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->before($permissionCalendarChangeRequired)
		->before($featureCuratedListRequired)
		->before($canChangeSite); 
$app->match('/curatedlist/{slug}/curators', "site\controllers\CuratedListController::curators")
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->before($canChangeSite); 
$app->match('/curatedlist/{slug}/calendar', "site\controllers\CuratedListController::calendarNow")
		->assert('slug', FRIENDLY_SLUG_REGEX) ; 
$app->match('/curatedlist/{slug}/calendar/', "site\controllers\CuratedListController::calendarNow")
		->assert('slug', FRIENDLY_SLUG_REGEX) ; 
$app->match('/curatedlist/{slug}/calendar/{year}/{month}', "site\controllers\CuratedListController::calendar")
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('year', '\d+')
		->assert('month', '\d+') ; 
$app->match('/curatedlist/{slug}/calendar/{year}/{month}/', "site\controllers\CuratedListController::calendar")
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('year', '\d+')
		->assert('month', '\d+') ; 
$app->match('/curatedlist/{slug}/event/{eslug}/remove', "site\controllers\CuratedListEventController::remove")
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('eslug', '\d+')
		->before($permissionCalendarChangeRequired)
		->before($canChangeSite); 
$app->match('/curatedlist/{slug}/event/{eslug}/add', "site\controllers\CuratedListEventController::add")
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('eslug', '\d+')
		->before($permissionCalendarChangeRequired)
		->before($canChangeSite)
		->before($featureCuratedListRequired);
$app->match('/curatedlist/{slug}/group/{gslug}/remove', "site\controllers\CuratedListGroupController::remove")
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('gslug', '\d+')
		->before($permissionCalendarChangeRequired)
		->before($canChangeSite);
$app->match('/curatedlist/{slug}/group/{gslug}/add', "site\controllers\CuratedListGroupController::add")
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('gslug', '\d+')
		->before($canChangeSite)
		->before($permissionCalendarChangeRequired)
		->before($featureGroupRequired)
		->before($featureCuratedListRequired);
