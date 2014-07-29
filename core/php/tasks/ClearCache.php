<?php

namespace tasks;

use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ClearCache {

	public static function run(Application $app, $verbose = false) {
		foreach($app['extensions']->getExtensionsIncludingCore() as $ext) {
			$ext->clearCache();
		}
	}

} 
