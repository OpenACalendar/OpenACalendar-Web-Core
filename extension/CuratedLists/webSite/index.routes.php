<?php
/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$permissionCuratedListsChangeRequired = function(Request $request, Application $app) {
    global $CONFIG;
    if (!$app['currentUserPermissions']->hasPermission("org.openacalendar.curatedlists","CURATED_LISTS_CHANGE")) {
        if ($app['currentUser']) {
            return $app->abort(403); // TODO
        } else {
            return new RedirectResponse($CONFIG->getWebIndexDomainSecure().'/you/login');
        }

    }
};

$app->match('/event/{slug}/edit/curatedlists', 'org\openacalendar\curatedlists\site\controllers\EventController::editCuratedLists')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->before($permissionCuratedListsChangeRequired)
		->before($featureCuratedListRequired)
		->before($canChangeSite);


$app->match('/group/{slug}/edit/curatedlists', 'org\openacalendar\curatedlists\site\controllers\GroupController::editCuratedLists')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->before($permissionCuratedListsChangeRequired)
		->before($featureCuratedListRequired)
		->before($featureGroupRequired)
		->before($canChangeSite);


$app->match('/curatedlist', 'org\openacalendar\curatedlists\site\controllers\CuratedListListController::index');
$app->match('/curatedlist/', 'org\openacalendar\curatedlists\site\controllers\CuratedListListController::index');

$app->match('/curatedlist/new/', 'org\openacalendar\curatedlists\site\controllers\CuratedListNewController::newCuratedList')
		->before($permissionCuratedListsChangeRequired)
		->before($featureCuratedListRequired)
		->before($canChangeSite); 

$app->match('/curatedlist/{slug}', 'org\openacalendar\curatedlists\site\controllers\CuratedListController::show')
		->assert('slug', FRIENDLY_SLUG_REGEX); 
$app->match('/curatedlist/{slug}/', 'org\openacalendar\curatedlists\site\controllers\CuratedListController::show')
		->assert('slug', FRIENDLY_SLUG_REGEX); 
$app->match('/curatedlist/{slug}/edit/details', 'org\openacalendar\curatedlists\site\controllers\CuratedListController::editDetails')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->before($permissionCuratedListsChangeRequired)
		->before($featureCuratedListRequired)
		->before($canChangeSite); 
$app->match('/curatedlist/{slug}/curators', 'org\openacalendar\curatedlists\site\controllers\CuratedListController::curators')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->before($canChangeSite); 
$app->match('/curatedlist/{slug}/calendar', 'org\openacalendar\curatedlists\site\controllers\CuratedListController::calendarNow')
		->assert('slug', FRIENDLY_SLUG_REGEX) ; 
$app->match('/curatedlist/{slug}/calendar/', 'org\openacalendar\curatedlists\site\controllers\CuratedListController::calendarNow')
		->assert('slug', FRIENDLY_SLUG_REGEX) ; 
$app->match('/curatedlist/{slug}/calendar/{year}/{month}', 'org\openacalendar\curatedlists\site\controllers\CuratedListController::calendar')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('year', '\d+')
		->assert('month', '\d+') ; 
$app->match('/curatedlist/{slug}/calendar/{year}/{month}/', 'org\openacalendar\curatedlists\site\controllers\CuratedListController::calendar')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('year', '\d+')
		->assert('month', '\d+') ; 
$app->match('/curatedlist/{slug}/event/{eslug}/remove', 'org\openacalendar\curatedlists\site\controllers\CuratedListEventController::remove')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('eslug', '\d+')
		->before($permissionCuratedListsChangeRequired)
		->before($canChangeSite); 
$app->match('/curatedlist/{slug}/event/{eslug}/add', 'org\openacalendar\curatedlists\site\controllers\CuratedListEventController::add')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('eslug', '\d+')
		->before($permissionCuratedListsChangeRequired)
		->before($canChangeSite)
		->before($featureCuratedListRequired);
$app->match('/curatedlist/{slug}/group/{gslug}/remove', 'org\openacalendar\curatedlists\site\controllers\CuratedListGroupController::remove')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('gslug', '\d+')
		->before($permissionCuratedListsChangeRequired)
		->before($canChangeSite);
$app->match('/curatedlist/{slug}/group/{gslug}/add', 'org\openacalendar\curatedlists\site\controllers\CuratedListGroupController::add')
		->assert('slug', FRIENDLY_SLUG_REGEX)
		->assert('gslug', '\d+')
		->before($canChangeSite)
		->before($permissionCuratedListsChangeRequired)
		->before($featureGroupRequired)
		->before($featureCuratedListRequired);
