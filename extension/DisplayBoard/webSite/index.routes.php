<?php
/**
 *
 * @package org.openacalendar.displayboard
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$app->match('/displayboard', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::index");
$app->match('/displayboard/', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::index");
$app->match('/displayboard/run', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::run");
$app->match('/displayboard/run/', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::run");


