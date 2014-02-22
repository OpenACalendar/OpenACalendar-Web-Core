<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/extension.Core/php/autoload.php';
require_once APP_ROOT_DIR.'/extension.Core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

use repositories\GroupHistoryRepository;
use repositories\AreaHistoryRepository;
use models\GroupHistoryModel;
use models\AreaHistoryModel;


################################################################################

print "Areas ";
$areaHistoryRepo = new AreaHistoryRepository();
$stat = $DB->prepare("SELECT * FROM area_history");
$stat->execute();
while($data = $stat->fetch()) {
	$areaHistory = new AreaHistoryModel();
	$areaHistory->setFromDataBaseRow($data);
	
	$areaHistoryRepo->ensureChangedFlagsAreSet($areaHistory);
	print ".";
}
print "\n\n";


################################################################################

print "Groups ";
$groupHistoryRepo = new GroupHistoryRepository();
$stat = $DB->prepare("SELECT * FROM group_history");
$stat->execute();
while($data = $stat->fetch()) {
	$groupHistory = new GroupHistoryModel();
	$groupHistory->setFromDataBaseRow($data);
	
	$groupHistoryRepo->ensureChangedFlagsAreSet($groupHistory);
	print ".";
}
print "\n\n";



print " done\n\n";

