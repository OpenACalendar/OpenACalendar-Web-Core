<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

use cliapi1\CreateEvent;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

if (!$CONFIG->CLIAPI1Enabled) {
	die("CLIAPI1 Not Enabled!\n");
}

######### Get JSON
$f = fopen( 'php://stdin', 'r' );
$stdin = '';
while( $line = fgets( $f ) ) {
  $stdin .= $line;
}
fclose( $f );


######### Create
$createEvent = new CreateEvent();
$json = json_decode(trim($stdin));
$createEvent->setFromJSON($json);


######### Go
if ($createEvent->canGo()) {
	$createEvent->go();
	print "Done \n\n";
} else {
	print "ERRORS!\n\n";
	foreach($createEvent->getErrorMessages() as $msg) {
		print "ERROR: ".$msg."\n\n";
	}	
}

