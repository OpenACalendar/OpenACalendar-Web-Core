<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

// We could let autoloader get this, but for that to happen the extension has to be enabled in OAC.
// Let's just manually add this so ppl can import then enable.
require_once APP_ROOT_DIR.'/extension/AddressCodeGBOpenCodePoint/php/org/openacalendar/addresscode/gb/opencodepoint/AddressCodeGBOpenCodePointConvert.php';

/**
 *
 * 
 * @package AddressCodeGBOpenCodePoint
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$opencodepointdir = isset($argv[1]) ? $argv[1] : null;
$ourdir = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data');



$dataAdaptor = new \JMBTechnologyLimited\OSData\CodePointOpen\FileDataAdaptor($ourdir);

$service = new \JMBTechnologyLimited\OSData\CodePointOpen\CodePointOpenService($dataAdaptor);

$service->loadData($opencodepointdir);

print "Done";

exit(0);

