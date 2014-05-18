<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$app->match('/api2/', "siteapi2\controllers\IndexController::index"); 

## User data
$app->match('/api2/current_user_on_site.json', "siteapi2\controllers\IndexController::currentUserOnSiteJson")
		->before($appUserRequired); 

## country
$app->get('/api2/country/{slug}/info.json', "siteapi2\controllers\CountryController::infoJson")
		->before($appUserRequired); 

## Area
$app->get('/api2/area/{slug}/info.json', "siteapi2\controllers\AreaController::infoJson")
		->before($appUserRequired)
		->assert('slug', '\d+'); 
$app->post('/api2/area/{slug}/info.json', "siteapi2\controllers\AreaController::postInfoJson")
		->before($appUserRequired)
		->before($appVerifiedEditorUserRequired)
		->assert('slug', '\d+'); 

## Venue
$app->get('/api2/venue/{slug}/info.json', "siteapi2\controllers\VenueController::infoJson")
		->before($appUserRequired)
		->assert('slug', '\d+'); 

## Group
$app->get('/api2/group/{slug}/info.json', "siteapi2\controllers\GroupController::infoJson")
		->before($appUserRequired)
		->assert('slug', '\d+'); 

## Event
$app->get('/api2/event/{slug}/info.json', "siteapi2\controllers\EventController::infoJson")
		->before($appUserRequired)
		->assert('slug', '\d+'); 

