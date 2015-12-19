<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$app->match('/api2/', "indexapi2\controllers\IndexController::index"); 

## Login Process
$app->match('/api2/request_token.json', "indexapi2\controllers\IndexController::requestTokenJson"); 
$app->match('/api2/login.html', "indexapi2\controllers\IndexController::login"); 
$app->match('/api2/user_token.json', "indexapi2\controllers\IndexController::userTokenJson"); 

## User data
$app->match('/api2/current_user.json', "indexapi2\controllers\IndexController::currentUserJson")
			->before($appUserRequired); 

