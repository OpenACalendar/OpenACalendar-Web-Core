<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$app->match("/api1/person/{username}/events.ical", "index\controllers\PublicUserController::ical");
$app->match("/api1/person/{username}/events.ics", "index\controllers\PublicUserController::ical");
$app->match("/api1/person/{username}/events.json", "index\controllers\PublicUserController::json");
$app->match("/api1/person/{username}/events.jsonp", "index\controllers\PublicUserController::jsonp");
$app->match("/api1/person/{username}/private/{accesskey}/events.a.ical", "index\controllers\PrivateUserController::icalAttending");
$app->match("/api1/person/{username}/private/{accesskey}/events.a.ics", "index\controllers\PrivateUserController::icalAttending");
$app->match("/api1/person/{username}/private/{accesskey}/events.aw.ical", "index\controllers\PrivateUserController::icalAttendingWatching");
$app->match("/api1/person/{username}/private/{accesskey}/events.aw.ics", "index\controllers\PrivateUserController::icalAttendingWatching");
