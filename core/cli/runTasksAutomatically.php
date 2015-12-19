<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

$verbosePrint = true;

if ($verbosePrint) {
	print "Starting all tasks check ". $app['timesource']->getDateTime()->format("c")."\n";
}
foreach($app['extensions']->getExtensionsIncludingCore() as $extension) {
	if ($verbosePrint) {
		print "Extension ".$extension->getId()."\n";
	}
	foreach($extension->getTasks() as $task) {
		if ($verbosePrint) {
			print "    Task ".$task->getTaskId()."\n";
		}
		try {
			$task->runAutomaticallyNowIfShould($verbosePrint);
		} catch (\Exception $e) {
			$app['monolog']->addError("Exception Running Tasks Automatically: ".$e->getMessage());
			// If we get an exception from runAutomaticallyNowIfShould() we want to crash out so no more tasks are run.
			// The Exception may have left the DB or other resources in a bad state.
			throw $e;
		}
	}
}
if ($verbosePrint) {
	print "Done all tasks check ". $app['timesource']->getDateTime()->format("c")."\n";
}
