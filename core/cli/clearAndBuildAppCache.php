<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once (defined('COMPOSER_ROOT_DIR') ? COMPOSER_ROOT_DIR : APP_ROOT_DIR).'/vendor/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */




////////////////////////////////////////////////////////////////////////////// CLASS MAP
$classes  = array();

function processFolder($folder) {
    global $classes;
    $Directory = new RecursiveDirectoryIterator($folder);
    $Iterator = new RecursiveIteratorIterator($Directory);
    foreach($Iterator as $name) {
        if (substr($name , -4) == '.php') {

            $tokens = token_get_all(file_get_contents($name));
            $classTokenFound = false;
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] == T_CLASS) {
                        $classTokenFound = true;
                    }
                }
            }

            if ($classTokenFound) {

                $className = substr($name, strlen($folder) + 1);
                $className = substr($className, 0, -4);

                if (!isset($classes[$className])) {
                    $classes[$className] = substr($name, strlen(realpath(APP_ROOT_DIR)));
                }
            }

        }
    }
}

foreach($app['config']->extensions as $extension) {
    processFolder(realpath(APP_ROOT_DIR.DIRECTORY_SEPARATOR.'extension'.DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR.
        'php'.DIRECTORY_SEPARATOR));
}
processFolder(realpath(APP_ROOT_DIR.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR));

$fp = fopen(APP_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'classmap.php', 'w');
fwrite($fp, '<?php'."\n");
fwrite($fp, '$CLASSMAP = array();'. "\n");
foreach($classes as $className=>$file) {
    fwrite($fp, '$CLASSMAP[\''.$className.'\'] = \''.$file. '\';'."\n");
}

//////////////////////////////////////////////////////////////////////////// EXTENSIONS
foreach($app['extensions']->getExtensionsIncludingCore() as $ext) {
    $ext->clearAppCache();
}
