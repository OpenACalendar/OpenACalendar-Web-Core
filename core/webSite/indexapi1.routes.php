<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */



$app->match('/api1/events.ical', "siteapi1\controllers\EventListController::ical"); 
$app->match('/api1/events.json', "siteapi1\controllers\EventListController::json"); 
$app->match('/api1/events.jsonp', "siteapi1\controllers\EventListController::jsonp"); 
$app->match('/api1/events.create.atom', "siteapi1\controllers\EventListController::atomCreate"); 
$app->match('/api1/events.before.atom', "siteapi1\controllers\EventListController::atomBefore"); 


$app->match('/api1/event/{slug}/info.ical', "siteapi1\controllers\EventController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/event/{slug}/info.json', "siteapi1\controllers\EventController::json")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/event/{slug}/info.jsonp', "siteapi1\controllers\EventController::jsonp")
		->assert('slug', FRIENDLY_SLUG_REGEX);



$app->match('/api1/groups.json', "siteapi1\controllers\GroupListController::json"); 


$app->match('/api1/group/{slug}/events.ical', "siteapi1\controllers\GroupController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/group/{slug}/events.json', "siteapi1\controllers\GroupController::json")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/group/{slug}/events.jsonp', "siteapi1\controllers\GroupController::jsonp")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/group/{slug}/events.create.atom', "siteapi1\controllers\GroupController::atomCreate")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/group/{slug}/events.before.atom', "siteapi1\controllers\GroupController::atomBefore")
		->assert('slug', FRIENDLY_SLUG_REGEX);

$app->match('/api1/tag/{slug}/events.ical', "siteapi1\controllers\TagController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/tag/{slug}/events.json', "siteapi1\controllers\TagController::json")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/tag/{slug}/events.jsonp', "siteapi1\controllers\TagController::jsonp")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/tag/{slug}/events.create.atom', "siteapi1\controllers\TagController::atomCreate")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/tag/{slug}/events.before.atom', "siteapi1\controllers\TagController::atomBefore")
		->assert('slug', FRIENDLY_SLUG_REGEX);
		
		
$app->match('/api1/area/{slug}/events.ical', "siteapi1\controllers\AreaController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/area/{slug}/events.json', "siteapi1\controllers\AreaController::json")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/area/{slug}/events.jsonp', "siteapi1\controllers\AreaController::jsonp")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/area/{slug}/events.create.atom', "siteapi1\controllers\AreaController::atomCreate")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/area/{slug}/events.before.atom', "siteapi1\controllers\AreaController::atomBefore")
		->assert('slug', FRIENDLY_SLUG_REGEX);

$app->match('/api1/venue/virtual/events.ical', "siteapi1\controllers\VenueVirtualController::ical") ; 
$app->match('/api1/venue/virtual/events.json', "siteapi1\controllers\VenueVirtualController::json") ; 
$app->match('/api1/venue/virtual/events.jsonp', "siteapi1\controllers\VenueVirtualController::jsonp") ; 
$app->match('/api1/venue/virtual/events.create.atom', "siteapi1\controllers\VenueVirtualController::atomCreate") ; 
$app->match('/api1/venue/virtual/events.before.atom', "siteapi1\controllers\VenueVirtualController::atomBefore") ; 

$app->match('/api1/venue/{slug}/events.ical', "siteapi1\controllers\VenueController::ical")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/venue/{slug}/events.json', "siteapi1\controllers\VenueController::json")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/venue/{slug}/events.jsonp', "siteapi1\controllers\VenueController::jsonp")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/venue/{slug}/events.create.atom', "siteapi1\controllers\VenueController::atomCreate")
		->assert('slug', FRIENDLY_SLUG_REGEX);
$app->match('/api1/venue/{slug}/events.before.atom', "siteapi1\controllers\VenueController::atomBefore")
		->assert('slug', FRIENDLY_SLUG_REGEX);


$app->match('/api1/country/{slug}/events.ical', "siteapi1\controllers\CountryController::eventsIcal"); 
$app->match('/api1/country/{slug}/events.json', "siteapi1\controllers\CountryController::eventsJson"); 
$app->match('/api1/country/{slug}/events.jsonp', "siteapi1\controllers\CountryController::eventsJsonp"); 
$app->match('/api1/country/{slug}/events.create.atom', "siteapi1\controllers\CountryController::eventsAtomCreate"); 
$app->match('/api1/country/{slug}/events.before.atom', "siteapi1\controllers\CountryController::eventsAtomBefore"); 


$app->match('/api1/histories.atom', "siteapi1\controllers\HistoryListController::atom"); 


$app->match("/api1/person/{username}/events.ical", "siteapi1\controllers\PublicUserController::ical");
$app->match("/api1/person/{username}/events.json", "siteapi1\controllers\PublicUserController::json");
$app->match("/api1/person/{username}/events.jsonp", "siteapi1\controllers\PublicUserController::jsonp");

