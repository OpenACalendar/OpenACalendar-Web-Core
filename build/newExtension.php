<?php
require __DIR__.'/localConfig.php';

// Previous versions of localConfig.php had a stupid spelling mistake I had to correct.
// To make sure old versions of localConfig.php with the spelling mistake still work ......
if (!defined('APP_WEB_INDEX_DIR') && defined('APP_WED_INDEX_DIR')) define('APP_WEB_INDEX_DIR', APP_WED_INDEX_DIR);
if (!defined('APP_WEB_SITE_DIR') && defined('APP_WED_SITE_DIR')) define('APP_WEB_SITE_DIR', APP_WED_SITE_DIR);
if (!defined('APP_WEB_SINGLE_SITE_DIR') && defined('APP_WED_SINGLE_SITE_DIR')) define('APP_WEB_SINGLE_SITE_DIR', APP_WED_SINGLE_SITE_DIR);
if (!defined('APP_WEB_SYSADMIN_DIR') && defined('APP_WED_SYSADMIN_DIR')) define('APP_WEB_SYSADMIN_DIR', APP_WED_SYSADMIN_DIR);

// Now the stupid I've discovered is out of the way I can get on with the rest of the, cough, intelligent stuff ....

require __DIR__.'/../core/php/Config.php';
$CONFIG = new Config();
require APP_ROOT_DIR.'/config.php';
require __DIR__.'/vendor/autoload.php';


/**
 *
 *
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


$folderName = $argv[1];
$packageName = $argv[2];

print "Create an Extension\n";
print "Folder Name: ". $folderName. "\n";
print "Package Name: ". $packageName. "\n";
print "\n";

if (!$folderName) {
	print "You must set a folder name!\n";
	die();
}
if (!$packageName) {
	print "You must set a package name!\n";
	die();
}

if (strpos($packageName, '.') !== false) {
    print "Your package name has a dot in -  you should use \\ as a package separator instead\n";
    die();
}

if (strpos($packageName, '/') !== false) {
    print "Your package name has / in -  you should use \\ as a package separator instead\n";
    die();
}

$extFolder = APP_ROOT_DIR. DIRECTORY_SEPARATOR. 'extension'. DIRECTORY_SEPARATOR.$folderName.DIRECTORY_SEPARATOR;
if (file_exists($extFolder)) {
	print "That folder already seems to exist!\n";
	die();
}

mkdir($extFolder);
mkdir($extFolder. DIRECTORY_SEPARATOR. 'theme');
mkdir($extFolder. DIRECTORY_SEPARATOR. 'theme'. DIRECTORY_SEPARATOR.'default');
mkdir($extFolder. DIRECTORY_SEPARATOR. 'theme'. DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'templates');
mkdir($extFolder. DIRECTORY_SEPARATOR. 'theme'. DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'templatesSingleSite');
mkdir($extFolder. DIRECTORY_SEPARATOR. 'webIndex');
mkdir($extFolder. DIRECTORY_SEPARATOR. 'webSysAdmin');
mkdir($extFolder. DIRECTORY_SEPARATOR. 'webSite');
mkdir($extFolder. DIRECTORY_SEPARATOR. 'webSingleSite');


file_put_contents($extFolder. DIRECTORY_SEPARATOR. "extension.php", "<?php \n\$app['extensions']->addExtension(__DIR__, new ".$packageName."\Extension(\$app));\n\n");
file_put_contents($extFolder. DIRECTORY_SEPARATOR. "readme.txt", "Extension created automatically.\n\n");


$currentFolder = $extFolder . DIRECTORY_SEPARATOR. 'php'.DIRECTORY_SEPARATOR;
mkdir($currentFolder);
foreach(explode("\\", $packageName) as $bit) {
	$currentFolder .= $bit. DIRECTORY_SEPARATOR;
	mkdir($currentFolder);
}
file_put_contents($currentFolder. DIRECTORY_SEPARATOR. "Extension.php",
	"<?php \n\n".
    "namespace ". $packageName.";\n\n".
    "class Extension extends \BaseExtension {\n\n".
    "    public function getId() { return '".str_replace('\\','.',$packageName)."'; }\n\n".
    "    public function getTitle() { return '".$folderName."'; }\n\n".
    "    public function getDescription() { return '".$folderName."'; }\n\n".
    "}\n\n"
);

print "To activate new extension, add '". $folderName. "' to \$CONFIG->extensions = array(...)\n\n\n";