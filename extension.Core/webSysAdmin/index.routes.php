<?php



/**
 *
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$app->match('/', "sysadmin\controllers\IndexController::index"); 
$app->match('/sysadmin', "sysadmin\controllers\IndexController::index"); 
$app->match('/sysadmin/', "sysadmin\controllers\IndexController::index"); 

$app->match('/sysadmin/site', "sysadmin\controllers\SiteListController::index"); 
$app->match('/sysadmin/site/', "sysadmin\controllers\SiteListController::index"); 
$app->match('/sysadmin/site/{id}', "sysadmin\controllers\SiteController::show")
		->assert('id', '\d+'); 
$app->match('/sysadmin/site/{id}/', "sysadmin\controllers\SiteController::show")
		->assert('id', '\d+'); 
$app->match('/sysadmin/site/{id}/editors', "sysadmin\controllers\SiteController::editors")
		->assert('id', '\d+'); 
$app->match('/sysadmin/site/{id}/watchers', "sysadmin\controllers\SiteController::watchers")
		->assert('id', '\d+'); 


$app->match('/sysadmin/sitequota', "sysadmin\controllers\SiteQuotaListController::index"); 
$app->match('/sysadmin/sitequota/', "sysadmin\controllers\SiteQuotaListController::index"); 
$app->match('/sysadmin/sitequota/{code}', "sysadmin\controllers\SiteQuotaController::show"); 
$app->match('/sysadmin/sitequota/{code}/', "sysadmin\controllers\SiteQuotaController::show"); 

$app->match('/sysadmin/site/{siteid}/event', "sysadmin\controllers\EventListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/event/', "sysadmin\controllers\EventListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/event/{slug}', "sysadmin\controllers\EventController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 


$app->match('/sysadmin/site/{siteid}/venue', "sysadmin\controllers\VenueListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/venue/', "sysadmin\controllers\VenueListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/venue/{slug}', "sysadmin\controllers\VenueController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 
$app->match('/sysadmin/site/{siteid}/venue/{slug}/', "sysadmin\controllers\VenueController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 

$app->match('/sysadmin/site/{siteid}/media', "sysadmin\controllers\MediaListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/media/', "sysadmin\controllers\MediaListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/media/{slug}', "sysadmin\controllers\MediaController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 
$app->match('/sysadmin/site/{siteid}/media/{slug}/', "sysadmin\controllers\MediaController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 



$app->match('/sysadmin/site/{siteid}/group', "sysadmin\controllers\GroupListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/group/', "sysadmin\controllers\GroupListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/group/{slug}', "sysadmin\controllers\GroupController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 
$app->match('/sysadmin/site/{siteid}/group/{slug}/', "sysadmin\controllers\GroupController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 
$app->match('/sysadmin/site/{siteid}/group/{slug}/watchers', "sysadmin\controllers\GroupController::watchers")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 


$app->match('/sysadmin/site/{siteid}/curatedlist', "sysadmin\controllers\CuratedListListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/curatedlist/', "sysadmin\controllers\CuratedListListController::index")
		->assert('siteid', '\d+'); 
$app->match('/sysadmin/site/{siteid}/curatedlist/{slug}', "sysadmin\controllers\CuratedListController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 
$app->match('/sysadmin/site/{siteid}/curatedlist/{slug}/', "sysadmin\controllers\CuratedListController::index")
		->assert('siteid', '\d+')
		->assert('slug', '\d+'); 

$app->match('/sysadmin/user', "sysadmin\controllers\UserListController::index"); 
$app->match('/sysadmin/user/', "sysadmin\controllers\UserListController::index"); 
$app->match('/sysadmin/user/{id}', "sysadmin\controllers\UserController::show")
		->assert('id', '\d+'); 
$app->match('/sysadmin/user/{id}/', "sysadmin\controllers\UserController::show")
		->assert('id', '\d+'); 
$app->match('/sysadmin/user/{id}/verify', "sysadmin\controllers\UserController::verify")
		->assert('id', '\d+'); 
$app->match('/sysadmin/user/{id}/reset', "sysadmin\controllers\UserController::reset")
		->assert('id', '\d+'); 
$app->match('/sysadmin/user/{id}/watchesSitePromptEmail', "sysadmin\controllers\UserController::watchesSitePromptEmail")
		->assert('id', '\d+'); 
$app->match('/sysadmin/user/{id}/watchesSiteGroupPromptEmail', "sysadmin\controllers\UserController::watchesSiteGroupPromptEmail")
		->assert('id', '\d+'); 
$app->match('/sysadmin/user/{id}/watchesGroupPromptEmail', "sysadmin\controllers\UserController::watchesGroupPromptEmail")
		->assert('id', '\d+'); 
$app->match('/sysadmin/user/{id}/watchesSiteNotifyEmail', "sysadmin\controllers\UserController::watchesSiteNotifyEmail")
		->assert('id', '\d+'); 
$app->match('/sysadmin/user/{id}/watchesGroupNotifyEmail', "sysadmin\controllers\UserController::watchesGroupNotifyEmail")
		->assert('id', '\d+'); 

$app->match('/sysadmin/history', "sysadmin\controllers\HistoryController::index"); 
$app->match('/sysadmin/history/', "sysadmin\controllers\HistoryController::index"); 


$app->match('/sysadmin/contactsupport', "sysadmin\controllers\ContactSupportListController::index"); 
$app->match('/sysadmin/contactsupport/', "sysadmin\controllers\ContactSupportListController::index"); 

$app->match('/sysadmin/contactsupport/{id}/', "sysadmin\controllers\ContactSupportController::index")
		->assert('id', '\d+'); 

