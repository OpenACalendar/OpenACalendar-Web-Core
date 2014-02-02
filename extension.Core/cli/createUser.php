<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/extension.Core/php/autoload.php';
require_once APP_ROOT_DIR.'/extension.Core/php/autoloadCLI.php';

use repositories\UserAccountRepository;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */


$username = $argv[1];
$email = $argv[2];
$password = $argv[3];
$extraFlags = explode(",", isset($argv[4]) ? strtolower($argv[4]) : '');
$makeSysAdmin = in_array("sysadmin", $extraFlags);
if (!$username || !$email || !$password) {
	die("Username and Email and Password?\n\n");
}

print "Username: ". $username."\n";
print "Email: ". $email."\n";
print "Password: ". $password."\n";
print "Sys Admin: ".($makeSysAdmin?"yes":"no")."\n";

sleep(10);

print "Starting ...\n";

$userRepository = new UserAccountRepository();

$userExistingUserName = $userRepository->loadByUserName($username);
if ($userExistingUserName) {
	die('That address is already taken');
}

$userExistingEmail = $userRepository->loadByEmail($email);
if ($userExistingEmail) {
	die('That email address already has an account');
}

$user = new UserAccountModel();
$user->setEmail($email);
$user->setUsername($username);
$user->setPassword($password);

$userRepository->create($user);

if ($makeSysAdmin) {
	$userRepository->makeSysAdmin($user, null);
}

print "Done!\n";

