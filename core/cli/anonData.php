<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

/**
 *
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
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

// User Email Addresses
print "user_account_information\n";
$stat = $DB->prepare("UPDATE user_account_information ".
		" SET email= id || '@example.com',  email_canonical= id || '@example.com'  ".
		" WHERE email != 'james@jarofgreen.co.uk' AND email != 'james@doubtlesshouse.org.uk' ");
$stat->execute();

$stat = $DB->prepare("UPDATE user_account_information ".
		" SET  password_hash=:password ");
$stat->execute(array('password'=>password_hash('1234', PASSWORD_BCRYPT, array("cost" => 5))));

// user_account_general_security_key Table
print "user_account_general_security_key\n";
$stat = $DB->prepare('SELECT * FROM user_account_general_security_key ');
$stat->execute();
$stat2 = $DB->prepare('UPDATE user_account_general_security_key SET access_key=:new WHERE access_key=:old AND user_account_id=:user');
while($data = $stat->fetch()) {
    $stat2->execute(array('old'=>$data['access_key'],'new'=>createKey(), 'user'=>$data['user_account_id']));
}


// user_account_private_feed_key Table
print "user_account_private_feed_key\n";
$stat = $DB->prepare('SELECT * FROM user_account_private_feed_key ');
$stat->execute();
$stat2 = $DB->prepare('UPDATE user_account_private_feed_key SET access_key=:new WHERE access_key=:old AND user_account_id=:user');
while($data = $stat->fetch()) {
    $stat2->execute(array('old'=>$data['access_key'],'new'=>createKey(), 'user'=>$data['user_account_id']));
}


// user_account_remember_me Table
print "user_account_remember_me\n";
$stat = $DB->prepare('SELECT * FROM user_account_remember_me ');
$stat->execute();
$stat2 = $DB->prepare('UPDATE user_account_remember_me SET access_key=:new WHERE access_key=:old AND user_account_id=:user');
while($data = $stat->fetch()) {
    $stat2->execute(array('old'=>$data['access_key'],'new'=>createKey(), 'user'=>$data['user_account_id']));
}

// user_account_reset Table
print "user_account_reset\n";
$stat = $DB->prepare('SELECT * FROM user_account_reset ');
$stat->execute();
$stat2 = $DB->prepare('UPDATE user_account_reset SET access_key=:new WHERE access_key=:old AND user_account_id=:user');
while($data = $stat->fetch()) {
    $stat2->execute(array('old'=>$data['access_key'],'new'=>createKey(), 'user'=>$data['user_account_id']));
}



// user_account_verify_email Table
print "user_account_verify_email\n";
$stat = $DB->prepare('SELECT * FROM user_account_verify_email ');
$stat->execute();
$stat2 = $DB->prepare('UPDATE user_account_verify_email SET email=\'x@example.com\', access_key=:new WHERE access_key=:old AND user_account_id=:user');
while($data = $stat->fetch()) {
    $stat2->execute(array('old'=>$data['access_key'],'new'=>createKey(), 'user'=>$data['user_account_id']));
}

// user_at_event_information Table
print "user_at_event_information\n";
$stat = $DB->prepare('DELETE FROM user_at_event_information ');
$stat->execute();

// import_url_history Table
print "import_url_history\n";
$stat = $DB->prepare('UPDATE import_url_history SET from_ip=\'1.1.1.1\' WHERE from_ip IS NOT NULL ');
$stat->execute();

// media_history Table
print "media_history\n";
$stat = $DB->prepare('UPDATE media_history SET from_ip=\'1.1.1.1\' WHERE from_ip IS NOT NULL ');
$stat->execute();

// site_history Table
print "site_history\n";
$stat = $DB->prepare('UPDATE site_history SET from_ip=\'1.1.1.1\' WHERE from_ip IS NOT NULL ');
$stat->execute();

// tag_history Table
print "tag_history\n";
$stat = $DB->prepare('UPDATE tag_history SET from_ip=\'1.1.1.1\' WHERE from_ip IS NOT NULL ');
$stat->execute();

// user_group_history Table
print "user_group_history\n";
$stat = $DB->prepare('UPDATE user_group_history SET from_ip=\'1.1.1.1\' WHERE from_ip IS NOT NULL ');
$stat->execute();

// venue_history Table
print "venue_history\n";
$stat = $DB->prepare('UPDATE venue_history SET from_ip=\'1.1.1.1\' WHERE from_ip IS NOT NULL ');
$stat->execute();

// area_history Table
print "area_history\n";
$stat = $DB->prepare('UPDATE area_history SET from_ip=\'1.1.1.1\' WHERE from_ip IS NOT NULL ');
$stat->execute();

// sysadmin_comment_information Table
print "sysadmin_comment_information\n";
$stat = $DB->prepare('UPDATE sysadmin_comment_information SET comment=\'REMOVED\'');
$stat->execute();

print "Done\n";


