<?php
/**
 * This file does one thing; defines APP_ROOT_DIR as a constant. 
 * This allows the web roots and the app to be in different places.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

/** If the web roots are under the APP_ROOT_DIR use this. This is the default. **/
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);

/**
 * If you are using the vendor/, composer.json and composer.lock that came with 
 * this software COMPOSER_ROOT_DIR should be the same as APP_ROOT_DIR
 **/
define('COMPOSER_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);


