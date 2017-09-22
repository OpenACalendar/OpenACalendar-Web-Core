<?php
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */



$app->match('/terms', "index\controllers\IndexController::terms"); 
$app->match('/terms/', "index\controllers\IndexController::terms");
$app->match('/privacy', "index\controllers\IndexController::privacy"); 
$app->match('/privacy/', "index\controllers\IndexController::privacy");
$app->match('/about', "index\controllers\IndexController::about"); 
$app->match('/about/', "index\controllers\IndexController::about");

$app->match('/discover',"index\controllers\IndexController::discover");
$app->match('/discover/',"index\controllers\IndexController::discover");

$app->match('/credits',"index\controllers\IndexController::credits");
$app->match('/credits/',"index\controllers\IndexController::credits");


$app->match('/mytimezone', "index\controllers\IndexController::myTimeZone") ; 
$app->match('/mytimezone/', "index\controllers\IndexController::myTimeZone") ; 


// Logged out user actions
// ... routes all under "you" - as they happen to you, the person using the site
// (Yes, the "me" vs "you" thing is a bit idiotic, it's just a way to seperate the URLs)
$app->match('/you', "index\controllers\UserController::index"); 
$app->match('/you/', "index\controllers\UserController::index"); 
$app->match('/you/register', "index\controllers\UserController::register")
		->before($canChangeSite); 
$app->match('/you/login', "index\controllers\UserController::login"); 
$app->match('/you/forgot', "index\controllers\UserController::forgot")
		->before($canChangeSite); 
// these routes could live here, or they could go under /user/{username}
// we'll go with here as 
// * they are one shot things a human does, not a bot regularly gets
// *  using {id} not {username} gives an extra layer of security
$app->match('/you/verify/{id}/{code}', "index\controllers\UserController::verify")
		->before($canChangeSite);
$app->match('/you/reset/{id}/{code}', "index\controllers\UserController::reset")
		->before($canChangeSite); 
$app->match('/you/emails/{id}/{code}', "index\controllers\UserController::emails")
		->before($canChangeSite);
$app->match('/you/listunsub/{id}/{code}', "index\controllers\UserController::listUnsub")
		->before($canChangeSite);

$app->match('/you/aftergetuserapi', "index\controllers\UserController::afterGetUserAPI");


// Logged in user actions for current user
// ... all happen under me, as in me, the verified user!
// (Yes, the "me" vs "you" thing is a bit idiotic, it's just a way to seperate the URLs)
$app->match('/me', "index\controllers\CurrentUserController::index")
		->before($appUserRequired);
$app->match('/me/', "index\controllers\CurrentUserController::index")
		->before($appUserRequired);
// Do NOT require a user here. If you look in the controller you'll notice we do a redirect so we need this to be open to all.
$app->match('/me/logout', "index\controllers\CurrentUserController::logout"); 
$app->match('/me/verifyneeded', "index\controllers\CurrentUserController::verifyNeeded")
		->before($appUnverifiedUserRequired);
$app->match('/me/resendverifyemail', "index\controllers\CurrentUserController::resendVerifyEmail")
		->before($canChangeSite)
		->before($appUnverifiedUserRequired);
$app->match('/me/password', "index\controllers\CurrentUserController::changePassword")
		->before($canChangeSite)
		->before($appUserRequired);
$app->match('/me/emails', "index\controllers\CurrentUserController::emails")
		->before($canChangeSite)
		->before($appUserRequired);
$app->match('/me/prefs', "index\controllers\CurrentUserController::prefs")
		->before($canChangeSite)
		->before($appUserRequired);
$app->match('/me/profile', "index\controllers\CurrentUserController::profile")
		->before($canChangeSite)
		->before($appUserRequired);
$app->match('/me/sites', "index\controllers\CurrentUserController::sites")
		->before($appUserRequired);
$app->match('/me/agenda', "index\controllers\CurrentUserController::agenda")
		->before($appUserRequired); 
$app->match('/me/calendar', "index\controllers\CurrentUserController::calendarNow") 
		->before($appUserRequired); 
$app->match('/me/calendar/', "index\controllers\CurrentUserController::calendarNow") 
		->before($appUserRequired); 
$app->match('/me/calendar/{year}/{month}', "index\controllers\CurrentUserController::calendar")
		->assert('year', '\d+')
		->assert('month', '\d+')
		->before($appUserRequired) ; 
$app->match('/me/calendar/{year}/{month}/', "index\controllers\CurrentUserController::calendar")
		->assert('year', '\d+')
		->assert('month', '\d+')
		->before($appUserRequired) ; 
$app->match('/me/notification', "index\controllers\CurrentUserController::listNotifications") 
		->before($appUserRequired); 
$app->match('/me/notification.json', "index\controllers\CurrentUserController::listNotificationsJson") 
		->before($appUserRequired); 
$app->match('/me/notification/{id}', "index\controllers\CurrentUserController::showNotification") 
		->assert('id', '\d+')
		->before($appUserRequired); 

$app->match("/site/{siteSlug}/event/{eventSlug}/myAttendance.json", "index\controllers\SiteController::eventMyAttendanceJson");

$app->match("/person/{username}", "index\controllers\PublicUserController::index");
$app->match("/person/{username}/", "index\controllers\PublicUserController::index");


