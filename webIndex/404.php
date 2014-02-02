<?php
require 'localConfig.php';
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/extension.Core/php/autoload.php';
require_once APP_ROOT_DIR.'/extension.Core/php/autoloadWebApp.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

header("HTTP/1.0 404 Not Found");
print $app['twig']->render('index/error404.html.twig', array());


