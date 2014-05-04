<?php

namespace tasks;

use repositories\builders\AreaRepositoryBuilder;
use repositories\AreaRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateAreaParentCache {

	public static function update($verbose = false) {

		

		if ($verbose) print "Starting ".date("c")."\n";

		$areaRepository = new AreaRepository();

		$arb = new AreaRepositoryBuilder();
		$arb->setCacheNeedsBuildingOnly(true);

		foreach($arb->fetchAll() as $area) {
			$areaRepository->buildCacheAreaHasParent($area);
			if ($verbose) print ".";
		}

		if ($verbose) print "\nFinished ".date("c")."\n";

		
		
	}

	
}

