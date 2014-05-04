<?php

namespace tasks;


use repositories\GroupHistoryRepository;
use repositories\AreaHistoryRepository;
use repositories\VenueHistoryRepository;
use repositories\SiteHistoryRepository;
use repositories\EventHistoryRepository;
use models\GroupHistoryModel;
use models\AreaHistoryModel;
use models\VenueHistoryModel;
use models\SiteHistoryModel;
use models\EventHistoryModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateHistoryChangedFlags {

	public static function update($verbose = false) {
		global $DB;
		
		if ($verbose) print "Starting ".date("c")."\n";



		################################################################################

		if ($verbose) print "Sites ";
		$siteHistoryRepo = new SiteHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM site_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$siteHistory = new SiteHistoryModel();
			$siteHistory->setFromDataBaseRow($data);

			$siteHistoryRepo->ensureChangedFlagsAreSet($siteHistory);
			if ($verbose) print ".";
		}
		if ($verbose) print "\n\n";


		################################################################################

		if ($verbose) print "Groups ";
		$groupHistoryRepo = new GroupHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM group_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$groupHistory = new GroupHistoryModel();
			$groupHistory->setFromDataBaseRow($data);

			$groupHistoryRepo->ensureChangedFlagsAreSet($groupHistory);
			if ($verbose) print ".";
		}
		if ($verbose) print "\n\n";

		################################################################################

		if ($verbose) print "Venues ";
		$venueHistoryRepo = new VenueHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM venue_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$venueHistory = new VenueHistoryModel();
			$venueHistory->setFromDataBaseRow($data);

			$venueHistoryRepo->ensureChangedFlagsAreSet($venueHistory);
			if ($verbose) print ".";
		}
		if ($verbose) print "\n\n";

		################################################################################

		if ($verbose) print "Areas ";
		$areaHistoryRepo = new AreaHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM area_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$areaHistory = new AreaHistoryModel();
			$areaHistory->setFromDataBaseRow($data);

			$areaHistoryRepo->ensureChangedFlagsAreSet($areaHistory);
			if ($verbose) print ".";
		}
		if ($verbose) print "\n\n";


		################################################################################

		if ($verbose) print "Events ";
		$eventHistoryRepo = new EventHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM event_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$eventHistory = new EventHistoryModel();
			$eventHistory->setFromDataBaseRow($data);

			$eventHistoryRepo->ensureChangedFlagsAreSet($eventHistory);
			if ($verbose) print ".";
		}
		if ($verbose) print "\n\n";



		if ($verbose) print "Finished ".date("c")."\n";

		
		
	}

	
}

