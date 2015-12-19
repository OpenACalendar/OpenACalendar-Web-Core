<?php


/**
 *
 * @package org.openacalendar.contact
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$app->match('/sysadmin/contactsupport', "org\openacalendar\contact\sysadmin\controllers\ContactSupportListController::index");
$app->match('/sysadmin/contactsupport/', "org\openacalendar\contact\sysadmin\controllers\ContactSupportListController::index");

$app->match('/sysadmin/contactsupport/{id}/', "org\openacalendar\contact\sysadmin\controllers\ContactSupportController::index")
		->assert('id', '\d+');

