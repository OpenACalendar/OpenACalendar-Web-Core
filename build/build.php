<?php
require __DIR__.'/localConfig.php';
require __DIR__.'/../core/php/Config.php';
$CONFIG = new Config();
require APP_ROOT_DIR.'/config.php';
require __DIR__.'/vendor/autoload.php';


/**
 *
 * 
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */



print "OpenACalendar Build Script\n";

$runCSS = true;
$runJS = true;
$runIMG = true;


// TODO param flags to choose which?


use Assetic\Asset\FileAsset;
use Assetic\AssetWriter;
use Assetic\Filter\LessphpFilter;
use Assetic\AssetManager;

$extensions = array('/core/');
foreach($CONFIG->extensions as $ext) {
	$extensions[] = '/extension/'.$ext.'/';
}

################################################################################ CSS

if ($runCSS) {

	print "CSS ...\n";

	$cssFiles = array();

	foreach(array('index','site','singleSite','sysadmin','widget') as $type) {
		$cssFiles[$type] = array();
		foreach ($extensions as $extension) {
			$dir = APP_ROOT_DIR.$extension.'/theme/default/css/'.$type.DIRECTORY_SEPARATOR;
			if (is_dir($dir) && $handle = opendir($dir)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != '.' && $entry != '..' && substr($entry, -5) == '.less') {
						$cssFiles[$type][$entry] = $dir.$entry;
					}
				}
			}
		}
	}

	$cssFilters = array(  new LessphpFilter()  );

	# Index
	if (APP_WED_INDEX_DIR) {
		$am = new AssetManager();
		foreach($cssFiles['index'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$cssFilters);
			$fa->setTargetPath(substr($nameonly,0,-5).'.css');
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		$writer = new AssetWriter(APP_WED_INDEX_DIR.'/theme/default/css/');
		$writer->writeManagerAssets($am);
	}
	
	# Site
	if (APP_WED_SITE_DIR) {
		$am = new AssetManager();
		foreach($cssFiles['site'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$cssFilters);
			$fa->setTargetPath(substr($nameonly,0,-5).'.css');
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		$writer = new AssetWriter(APP_WED_SITE_DIR.'/theme/default/css/');
		$writer->writeManagerAssets($am);
	}

	# SingleSite
	if (APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($cssFiles['singleSite'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$cssFilters);
			$fa->setTargetPath(substr($nameonly,0,-5).'.css');
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/css/');
		$writer->writeManagerAssets($am);
	}

	# Sysadmin
	if (APP_WED_SYSADMIN_DIR || APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($cssFiles['sysadmin'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$cssFilters);
			$fa->setTargetPath(substr($nameonly,0,-5).'.css');
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		if (APP_WED_SYSADMIN_DIR) {
			$writer = new AssetWriter(APP_WED_SYSADMIN_DIR.'/theme/default/csssysadmin/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SINGLE_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/csssysadmin/');
			$writer->writeManagerAssets($am);	
		}
	}
}


################################################################################ JS

if ($runJS) {
	
	print "JS ...\n";

	$jsFiles = array();

	foreach(array('commonIndexAndSite','index','site','widget') as $type) {
		$jsFiles[$type] = array();
		foreach ($extensions as $extension) {
			$dir = APP_ROOT_DIR.$extension.'/theme/default/js/'.$type.DIRECTORY_SEPARATOR;
			if (is_dir($dir) && $handle = opendir($dir)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != '.' && $entry != '..' && substr($entry, -3) == '.js') {
						$jsFiles[$type][$entry] = $dir.$entry;
					}
				}
			}
		}
	}

	$jsFilters = array(   );

	# IndexAndSite
	if (APP_WED_INDEX_DIR || APP_WED_SITE_DIR || APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($jsFiles['commonIndexAndSite'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$jsFilters);
			$fa->setTargetPath($nameonly);
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		if (APP_WED_INDEX_DIR) {
			$writer = new AssetWriter(APP_WED_INDEX_DIR.'/theme/default/js/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SITE_DIR.'/theme/default/js/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SINGLE_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/js/');
			$writer->writeManagerAssets($am);
		}
	}

	# Index
	if (APP_WED_INDEX_DIR || APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($jsFiles['index'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$jsFilters);
			$fa->setTargetPath($nameonly);
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		if (APP_WED_INDEX_DIR) {
			$writer = new AssetWriter(APP_WED_INDEX_DIR.'/theme/default/js/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SINGLE_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/js/');
			$writer->writeManagerAssets($am);
		}
	}
	
	# Site
	if (APP_WED_SITE_DIR || APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($jsFiles['site'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$jsFilters);
			$fa->setTargetPath($nameonly);
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		if (APP_WED_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SITE_DIR.'/theme/default/js/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SINGLE_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/js/');
			$writer->writeManagerAssets($am);
		}
	}
	
}


################################################################################ IMG

if ($runIMG) {

	print "IMG ...\n";
	
	$imgFiles = array();

	foreach(array('commonIndexAndSite','index','site','sysadmin') as $type) {
		$imgFiles[$type] = array();
		foreach ($extensions as $extension) {
			$dir = APP_ROOT_DIR.$extension.'/theme/default/img/'.$type.DIRECTORY_SEPARATOR;
			if (is_dir($dir) && $handle = opendir($dir)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != '.' && $entry != '..' && in_array(substr($entry, -4), array('.png','.gif','.jpg'))) {
						$imgFiles[$type][$entry] = $dir.$entry;
					}
				}
			}
		}
	}

	$imgFilters = array(   );

	# IndexAndSite
	if (APP_WED_INDEX_DIR || APP_WED_SITE_DIR || APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($imgFiles['commonIndexAndSite'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$imgFilters);
			$fa->setTargetPath($nameonly);
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		if (APP_WED_INDEX_DIR) {
			$writer = new AssetWriter(APP_WED_INDEX_DIR.'/theme/default/img/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SITE_DIR.'/theme/default/img/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SINGLE_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/img/');
			$writer->writeManagerAssets($am);
		}
	}

	# Index
	if (APP_WED_INDEX_DIR || APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($imgFiles['index'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$imgFilters);
			$fa->setTargetPath($nameonly);
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		if (APP_WED_INDEX_DIR) {
			$writer = new AssetWriter(APP_WED_INDEX_DIR.'/theme/default/img/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SINGLE_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/img/');
			$writer->writeManagerAssets($am);
		}
	}
	
	# Site
	if (APP_WED_SITE_DIR || APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($imgFiles['site'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$imgFilters);
			$fa->setTargetPath($nameonly);
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		if (APP_WED_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SITE_DIR.'/theme/default/img/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SINGLE_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/img/');
			$writer->writeManagerAssets($am);
		}
	}


	# Sysadmin
	if (APP_WED_SYSADMIN_DIR || APP_WED_SINGLE_SITE_DIR) {
		$am = new AssetManager();
		foreach($imgFiles['sysadmin'] as $nameonly=>$filename) {
			$fa = new FileAsset($filename,$imgFilters);
			$fa->setTargetPath($nameonly);
			$am->set(str_replace('.','_',$nameonly),$fa);
		}
		if (APP_WED_SYSADMIN_DIR) {
			$writer = new AssetWriter(APP_WED_SYSADMIN_DIR.'/theme/default/imgsysadmin/');
			$writer->writeManagerAssets($am);
		}
		if (APP_WED_SINGLE_SITE_DIR) {
			$writer = new AssetWriter(APP_WED_SINGLE_SITE_DIR.'/theme/default/imgsysadmin/');
			$writer->writeManagerAssets($am);
		}
	}
	
}

