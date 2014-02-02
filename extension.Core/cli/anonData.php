<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/extension.Core/php/autoload.php';
require_once APP_ROOT_DIR.'/extension.Core/php/autoloadCLI.php';

/**
 *
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$actuallyANON = isset($argv[1]) && strtolower($argv[1]) == 'yes';
print "Actually ANON: ". ($actuallyANON ? "YES":"nah")."\n";
if (!$actuallyANON) die("DIE\n");

$actuallyReallyANON = isset($argv[2]) && strtolower($argv[2]) == 'really';
print "Really ANON: ". ($actuallyReallyANON ? "YES":"nah")."\n";
if (!$actuallyReallyANON) die("DIE\n");

die("GONNA DIE ANYWAY\n");

print "Waiting ...\n";
sleep(5);
print "Running\n";

$stat = $DB->prepare("UPDATE user_account_information ".
		" SET email= id || '@jarofgreen.co.uk',  email_canonical= id || '@jarofgreen.co.uk'  ".
		" WHERE email != 'james@jarofgreen.co.uk' AND email != 'james@doubtlesshouse.org.uk' ");
$stat->execute();

print "Done\n";


