<?php
/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */



$app->match('/api1/curatedlist/{slug}/events.ical', "org\openacalendar\curatedlists\siteapi1\controllers\CuratedListController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/curatedlist/{slug}/events.json', "org\openacalendar\curatedlists\siteapi1\controllers\CuratedListController::json")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/curatedlist/{slug}/events.jsonp', "org\openacalendar\curatedlists\siteapi1\controllers\CuratedListController::jsonp")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/curatedlist/{slug}/events.create.atom', "org\openacalendar\curatedlists\siteapi1\controllers\CuratedListController::atomCreate")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/curatedlist/{slug}/events.before.atom', "org\openacalendar\curatedlists\siteapi1\controllers\CuratedListController::atomBefore")
		->assert('slug', FRIENDLY_SLUG_REGEX);
