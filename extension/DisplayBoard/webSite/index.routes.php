<?php
/**
 *
 * @package org.openacalendar.displayboard
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

// Old routes, at some point I want to depreciate.
$app->match('/displayboard', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::index");
$app->match('/displayboard/', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::index");
$app->match('/displayboard/run', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::run");
$app->match('/displayboard/run/', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::run");


// New routes!

// type todaynextlater
$app->match('/displayboard/todaynextlater/', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::index");
$app->match('/displayboard/todaynextlater/', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::index");
$app->match('/displayboard/todaynextlater/run', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::run");
$app->match('/displayboard/todaynextlater/run/', "org\openacalendar\displayboard\site\controllers\DisplayBoardController::run");

// type cycledetails
$app->match('/displayboard/cycledetails', "org\openacalendar\displayboard\site\controllers\DisplayBoardCycleDetailsController::index");
$app->match('/displayboard/cycledetails/', "org\openacalendar\displayboard\site\controllers\DisplayBoardCycleDetailsController::index");
$app->match('/displayboard/cycledetails/run', "org\openacalendar\displayboard\site\controllers\DisplayBoardCycleDetailsController::run");
$app->match('/displayboard/cycledetails/run/', "org\openacalendar\displayboard\site\controllers\DisplayBoardCycleDetailsController::run");


