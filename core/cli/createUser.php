<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

use repositories\UserAccountRepository;
use models\UserAccountModel;

/**
 * Creates a user.
 * 
 * This shouldn't really be here; but at the moment it's used by the install process.
 * It should be in cliapi1 and there should be a seperate explicit installer (web, cli, or both)
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */


$email = $argv[1];
$password = $argv[2];
$extraFlags = explode(",", isset($argv[3]) ? strtolower($argv[3]) : '');
$makeSysAdmin = in_array("sysadmin", $extraFlags);
if ( !$email || !$password) {
	print "Email and Password?\n\n";
	exit(1);
}

print "Email: ". $email."\n";
print "Password: ". $password."\n";
print "Sys Admin: ".($makeSysAdmin?"yes":"no")."\n";

sleep(10);

print "Starting ...\n";

$userRepository = new UserAccountRepository($app);

$userExistingEmail = $userRepository->loadByEmail($email);
if ($userExistingEmail) {
	print "That email address already has an account\n";
	exit(1);
}

$user = new UserAccountModel();
$user->setEmail($email);
$user->setDisplayname($email);
$user->setPassword($password);

$userRepository->create($user);

if ($makeSysAdmin) {
	$userRepository->makeSysAdmin($user, null);
}

print "Done!\n";



exit(0);


