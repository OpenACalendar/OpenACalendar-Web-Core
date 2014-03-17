<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$app->match("/api1/person/{username}/events.ical", "index\controllers\PublicUserController::ical");
$app->match("/api1/person/{username}/private/{accesskey}/events.a.ical", "index\controllers\PrivateUserController::icalAttending");
$app->match("/api1/person/{username}/private/{accesskey}/events.aw.ical", "index\controllers\PrivateUserController::icalAttendingWatching");
