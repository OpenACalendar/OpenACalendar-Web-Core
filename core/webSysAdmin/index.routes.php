<?php



/**
 *
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$app->match('/', 'sysadmin\controllers\IndexController::index');
$app->match('/sysadmin', 'sysadmin\controllers\IndexController::index');
$app->match('/sysadmin/', 'sysadmin\controllers\IndexController::index');

$app->match('/sysadmin/site', 'sysadmin\controllers\SiteListController::index');
$app->match('/sysadmin/site/', 'sysadmin\controllers\SiteListController::index');
$app->match('/sysadmin/site/new', 'sysadmin\controllers\SiteNewController::index');
$app->match('/sysadmin/site/{id}', 'sysadmin\controllers\SiteController::show')
		->assert('id', '\d+');
$app->match('/sysadmin/site/{id}/', 'sysadmin\controllers\SiteController::show')
		->assert('id', '\d+');
$app->match('/sysadmin/site/{id}/watchers', 'sysadmin\controllers\SiteController::watchers')
		->assert('id', '\d+');
$app->match('/sysadmin/site/{id}/features', 'sysadmin\controllers\SiteController::features')
		->assert('id', '\d+');


$app->match('/sysadmin/sitequota', 'sysadmin\controllers\SiteQuotaListController::index');
$app->match('/sysadmin/sitequota/', 'sysadmin\controllers\SiteQuotaListController::index');
$app->match('/sysadmin/sitequota/{code}', 'sysadmin\controllers\SiteQuotaController::show');
$app->match('/sysadmin/sitequota/{code}/', 'sysadmin\controllers\SiteQuotaController::show');

$app->match('/sysadmin/site/{siteid}/event', 'sysadmin\controllers\EventListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/event/', 'sysadmin\controllers\EventListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/event/{slug}', 'sysadmin\controllers\EventController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');

$app->match('/sysadmin/site/{siteid}/eventcustomfielddefinition', 'sysadmin\controllers\EventCustomFieldDefinitionListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/eventcustomfielddefinition/', 'sysadmin\controllers\EventCustomFieldDefinitionListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/eventcustomfielddefinition/{fieldid}', 'sysadmin\controllers\EventCustomFieldDefinitionController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');


$app->match('/sysadmin/site/{siteid}/venue', 'sysadmin\controllers\VenueListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/venue/', 'sysadmin\controllers\VenueListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/venue/{slug}', 'sysadmin\controllers\VenueController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');
$app->match('/sysadmin/site/{siteid}/venue/{slug}/', 'sysadmin\controllers\VenueController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');

$app->match('/sysadmin/site/{siteid}/media', 'sysadmin\controllers\MediaListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/media/', 'sysadmin\controllers\MediaListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/media/{slug}', 'sysadmin\controllers\MediaController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');
$app->match('/sysadmin/site/{siteid}/media/{slug}/', 'sysadmin\controllers\MediaController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');



$app->match('/sysadmin/site/{siteid}/group', 'sysadmin\controllers\GroupListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/group/', 'sysadmin\controllers\GroupListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/group/{slug}', 'sysadmin\controllers\GroupController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');
$app->match('/sysadmin/site/{siteid}/group/{slug}/', 'sysadmin\controllers\GroupController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');
$app->match('/sysadmin/site/{siteid}/group/{slug}/watchers', 'sysadmin\controllers\GroupController::watchers')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');



$app->match('/sysadmin/site/{siteid}/area', 'sysadmin\controllers\AreaListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/area/', 'sysadmin\controllers\AreaListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/area/{slug}', 'sysadmin\controllers\AreaController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');
$app->match('/sysadmin/site/{siteid}/area/{slug}/', 'sysadmin\controllers\AreaController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');
$app->match('/sysadmin/site/{siteid}/area/{slug}/watchers', 'sysadmin\controllers\AreaController::watchers')
	->assert('siteid', '\d+')
	->assert('slug', '\d+');


$app->match('/sysadmin/site/{siteid}/tag', 'sysadmin\controllers\TagListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/tag/', 'sysadmin\controllers\TagListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/tag/{slug}', 'sysadmin\controllers\TagController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');
$app->match('/sysadmin/site/{siteid}/tag/{slug}/', 'sysadmin\controllers\TagController::index')
		->assert('siteid', '\d+')
		->assert('slug', '\d+');



$app->match('/sysadmin/site/{siteid}/usergroup', 'sysadmin\controllers\SiteUserGroupListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/usergroup/', 'sysadmin\controllers\SiteUserGroupListController::index')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/usergroup/{id}', 'sysadmin\controllers\SiteUserGroupController::index')
		->assert('siteid', '\d+')
		->assert('id', '\d+');
$app->match('/sysadmin/site/{siteid}/usergroup/{id}/', 'sysadmin\controllers\SiteUserGroupController::index')
		->assert('siteid', '\d+')
		->assert('id', '\d+');


$app->match('/sysadmin/site/{siteid}/usernoteditor', 'sysadmin\controllers\SiteController::listUsersNotEditors')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/usernoteditor/', 'sysadmin\controllers\SiteController::listUsersNotEditors')
		->assert('siteid', '\d+');


$app->match('/sysadmin/site/{siteid}/country', 'sysadmin\controllers\SiteController::listCountries')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/country/', 'sysadmin\controllers\SiteController::listCountries')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/country/{countrycode}', 'sysadmin\controllers\SiteController::showCountry')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/country/{countrycode}/', 'sysadmin\controllers\SiteController::showCountry')
		->assert('siteid', '\d+');

$app->match('/sysadmin/site/{siteid}/incominglink', 'sysadmin\controllers\IncomingLinkListController::listForSite')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/incominglink/', 'sysadmin\controllers\IncomingLinkListController::listForSite')
		->assert('siteid', '\d+');



$app->match('/sysadmin/site/{siteid}/neweventdraft', 'sysadmin\controllers\NewEventDraftListController::listForSite')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/neweventdraft/', 'sysadmin\controllers\NewEventDraftListController::listForSite')
		->assert('siteid', '\d+');


$app->match('/sysadmin/site/{siteid}/neweventdraft/{slug}', 'sysadmin\controllers\NewEventDraftController::show')
		->assert('siteid', '\d+');
$app->match('/sysadmin/site/{siteid}/neweventdraft/{slug}', 'sysadmin\controllers\NewEventDraftController::show')
		->assert('siteid', '\d+');





$app->match('/sysadmin/user', 'sysadmin\controllers\UserListController::index');
$app->match('/sysadmin/user/', 'sysadmin\controllers\UserListController::index');
$app->match('/sysadmin/user/{id}', 'sysadmin\controllers\UserController::show')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/', 'sysadmin\controllers\UserController::show')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/verify', 'sysadmin\controllers\UserController::verify')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/reset', 'sysadmin\controllers\UserController::reset')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/notification', 'sysadmin\controllers\UserController::listNotifications')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/notificationprefs', 'sysadmin\controllers\UserController::listNotificationPreferences')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/event/edited', 'sysadmin\controllers\UserController::eventEdited')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/area/edited', 'sysadmin\controllers\UserController::areaEdited')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/venue/edited', 'sysadmin\controllers\UserController::venueEdited')
		->assert('id', '\d+');
$app->match('/sysadmin/user/{id}/group/edited', 'sysadmin\controllers\UserController::groupEdited')
		->assert('id', '\d+');



$app->match('/sysadmin/usergroup', 'sysadmin\controllers\UserGroupListController::index');

$app->match('/sysadmin/usergroup/new', 'sysadmin\controllers\UserGroupNewController::index');

$app->match('/sysadmin/usergroup/{id}', 'sysadmin\controllers\UserGroupController::index')
		->assert('id', '\d+');



$app->match('/sysadmin/history', 'sysadmin\controllers\HistoryController::index');
$app->match('/sysadmin/history/', 'sysadmin\controllers\HistoryController::index');


$app->match('/sysadmin/server', 'sysadmin\controllers\ServerController::index');
$app->match('/sysadmin/server/', 'sysadmin\controllers\ServerController::index');

$app->match('/sysadmin/server/phpinfo', 'sysadmin\controllers\ServerController::phpinfo');

$app->match('/sysadmin/config', 'sysadmin\controllers\ConfigController::index');
$app->match('/sysadmin/config', 'sysadmin\controllers\ConfigController::index');
$app->match('/sysadmin/config/tasks', 'sysadmin\controllers\ConfigController::tasks');
$app->match('/sysadmin/config/messageQue', 'sysadmin\controllers\ConfigController::messageQue');
$app->match('/sysadmin/config/database', 'sysadmin\controllers\ConfigController::database');
$app->match('/sysadmin/config/newSites', 'sysadmin\controllers\ConfigController::newSites');
$app->match('/sysadmin/config/media', 'sysadmin\controllers\ConfigController::media');
$app->match('/sysadmin/config/urls', 'sysadmin\controllers\ConfigController::urls');
$app->match('/sysadmin/config/sysadminUI', 'sysadmin\controllers\ConfigController::sysadminUI');
$app->match('/sysadmin/config/smtp', 'sysadmin\controllers\ConfigController::smtp');
$app->match('/sysadmin/config/logging', 'sysadmin\controllers\ConfigController::logging');
$app->match('/sysadmin/config/externalAnalytics', 'sysadmin\controllers\ConfigController::externalAnalytics');
$app->match('/sysadmin/config/import', 'sysadmin\controllers\ConfigController::import');
$app->match('/sysadmin/config/themes', 'sysadmin\controllers\ConfigController::themes');


$app->match('/sysadmin/extension', 'sysadmin\controllers\ExtensionListController::index');

$app->match('/sysadmin/extension/{id}', 'sysadmin\controllers\ExtensionController::index');


$app->match('/sysadmin/api2app', 'sysadmin\controllers\API2ApplicationList::index');
$app->match('/sysadmin/api2app/', 'sysadmin\controllers\API2ApplicationList::index');
$app->match('/sysadmin/api2app/{id}', 'sysadmin\controllers\API2Application::show')
		->assert('id', '\d+');
$app->match('/sysadmin/api2app/{id}/', 'sysadmin\controllers\API2Application::show')
		->assert('id', '\d+');

$app->match('/sysadmin/api2app/{id}/history', 'sysadmin\controllers\API2Application::history')
		->assert('id', '\d+');
$app->match('/sysadmin/api2app/{id}/history', 'sysadmin\controllers\API2Application::history')
		->assert('id', '\d+');



$app->match('/sysadmin/task', 'sysadmin\controllers\TaskListController::index');

$app->match('/sysadmin/tasklog', 'sysadmin\controllers\TaskLogListController::index');

$app->match('/sysadmin/task/{extid}/{taskid}', 'sysadmin\controllers\TaskController::index');


$app->match('/sysadmin/report', 'sysadmin\controllers\ReportListController::index');
$app->match('/sysadmin/report/', 'sysadmin\controllers\ReportListController::index');


$app->match('/sysadmin/report/series/{extid}/{reportid}' , 'sysadmin\controllers\SeriesReportController::index');
$app->match('/sysadmin/report/series/{extid}/{reportid}/' , 'sysadmin\controllers\SeriesReportController::index');

$app->match('/sysadmin/report/series/{extid}/{reportid}/run' , 'sysadmin\controllers\SeriesReportController::run');



$app->match('/sysadmin/report/value/{extid}/{reportid}' , 'sysadmin\controllers\ValueReportController::index');
$app->match('/sysadmin/report/value/{extid}/{reportid}/' , 'sysadmin\controllers\ValueReportController::index');

$app->match('/sysadmin/report/value/{extid}/{reportid}/run' , 'sysadmin\controllers\ValueReportController::run');


$app->match('/sysadmin/report/valuebytime/{extid}/{reportid}' , 'sysadmin\controllers\ValueByTimeReportController::index');
$app->match('/sysadmin/report/valuebytime/{extid}/{reportid}/' , 'sysadmin\controllers\ValueByTimeReportController::index');

$app->match('/sysadmin/report/valuebytime/{extid}/{reportid}/run' , 'sysadmin\controllers\ValueByTimeReportController::run');

$app->match('/sysadmin/report/seriesbytime/{extid}/{reportid}' , 'sysadmin\controllers\SeriesByTimeReportController::index');
$app->match('/sysadmin/report/seriesbytime/{extid}/{reportid}/' , 'sysadmin\controllers\SeriesByTimeReportController::index');

$app->match('/sysadmin/report/seriesbytime/{extid}/{reportid}/run' , 'sysadmin\controllers\SeriesByTimeReportController::run');
