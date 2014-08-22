<?php
/**
 * This file does one thing; defines APP_* as constants. 
 * This allows the build scripts, web roots and the app to be in different places.
 * 
 * @author James Baster <james@jarofgreen.co.uk>
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @license All rights reserved. Do not distribute.  
 */

/** If the build scripts are under the APP_ROOT_DIR use this. This is the default. **/
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);


/** If the web roots are under the APP_ROOT_DIR use this. This is the default.
 *
 * If you don't have one of these you can set it's value to null.
 *
 * So if you are using Multi Site Mode you can set APP_WEB_SINGLE_SITE_DIR to null.
 *
 * So if you are using Single Site Mode you can set APP_WEB_INDEX_DIR, APP_WEB_SITE_DIR and APP_WEB_SYSADMIN_DIR to null.
 **/
define('APP_WEB_INDEX_DIR',APP_ROOT_DIR.DIRECTORY_SEPARATOR.'webIndex'.DIRECTORY_SEPARATOR);
define('APP_WEB_SITE_DIR',APP_ROOT_DIR.DIRECTORY_SEPARATOR.'webSite'.DIRECTORY_SEPARATOR);
define('APP_WEB_SINGLE_SITE_DIR',APP_ROOT_DIR.DIRECTORY_SEPARATOR.'webSingleSite'.DIRECTORY_SEPARATOR);
define('APP_WEB_SYSADMIN_DIR',APP_ROOT_DIR.DIRECTORY_SEPARATOR.'webSysAdmin'.DIRECTORY_SEPARATOR);


