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

$themes = array( 'default' );

foreach($themes as $theme) {

	print "Theme: " . $theme . "\n";

################################################################################ CSS

	if ( $runCSS ) {

		print "CSS ...\n";

		$cssFiles = array();

		foreach ( array( 'index', 'site', 'singleSite', 'sysadmin', 'widget' ) as $type ) {
			$cssFiles[ $type ] = array();
			foreach ( $extensions as $extension ) {
				$dir = APP_ROOT_DIR . $extension . '/theme/'.$theme.'/css/' . $type . DIRECTORY_SEPARATOR;
				if ( is_dir( $dir ) && $handle = opendir( $dir ) ) {
					while ( false !== ( $entry = readdir( $handle ) ) ) {
						if ( $entry != '.' && $entry != '..' && substr( $entry, - 5 ) == '.less' ) {
							$cssFiles[ $type ][ $entry ] = realpath( $dir . $entry );
						}
					}
				}
			}
		}

		$cssFilterLess = new LessphpFilter();
		$vars          = array();
		foreach ( $extensions as $extensionDir ) {
			$file = APP_ROOT_DIR . $extensionDir . '/theme/'.$theme.'/variables.ini';
			if ( file_exists( $file ) ) {
				$vars = array_merge( $vars, parse_ini_file( $file, false ) );
			}
		}
		$cssFilterLess->setPresets( $vars );
		foreach ( $CONFIG->extensions as $extension ) {
			$dir = APP_ROOT_DIR . '/extension/' . $extension . '/theme/'.$theme.'/css/';
			if ( is_dir( $dir ) ) {
				$cssFilterLess->addLoadPath( realpath( $dir ) );
			}
		}
		$cssFilterLess->addLoadPath( APP_ROOT_DIR . '/core/theme/'.$theme.'/css/' );
		$cssFilters = array( $cssFilterLess );

		# Index
		if ( APP_WEB_INDEX_DIR ) {
			$am = new AssetManager();
			foreach ( $cssFiles['index'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $cssFilters );
				$fa->setTargetPath( substr( $nameonly, 0, - 5 ) . '.css' );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			$writer = new AssetWriter( APP_WEB_INDEX_DIR . '/theme/'.$theme.'/css/' );
			$writer->writeManagerAssets( $am );
		}

		# Site
		if ( APP_WEB_SITE_DIR ) {
			$am = new AssetManager();
			foreach ( $cssFiles['site'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $cssFilters );
				$fa->setTargetPath( substr( $nameonly, 0, - 5 ) . '.css' );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			$writer = new AssetWriter( APP_WEB_SITE_DIR . '/theme/'.$theme.'/css/' );
			$writer->writeManagerAssets( $am );
		}

		# SingleSite
		if ( APP_WEB_SINGLE_SITE_DIR ) {
			$am = new AssetManager();
			foreach ( $cssFiles['singleSite'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $cssFilters );
				$fa->setTargetPath( substr( $nameonly, 0, - 5 ) . '.css' );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/css/' );
			$writer->writeManagerAssets( $am );
		}

		# Sysadmin
		if ( APP_WEB_SYSADMIN_DIR || APP_WEB_SINGLE_SITE_DIR || APP_WEB_INDEX_DIR ) {
			$am = new AssetManager();
			foreach ( $cssFiles['sysadmin'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $cssFilters );
				$fa->setTargetPath( substr( $nameonly, 0, - 5 ) . '.css' );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			if ( APP_WEB_SYSADMIN_DIR ) {
				$writer = new AssetWriter( APP_WEB_SYSADMIN_DIR . '/theme/'.$theme.'/csssysadmin/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SINGLE_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/csssysadmin/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_INDEX_DIR ) {
				$writer = new AssetWriter( APP_WEB_INDEX_DIR . '/theme/'.$theme.'/csssysadmin/' );
				$writer->writeManagerAssets( $am );
			}
		}
	}


################################################################################ JS

	if ( $runJS ) {

		print "JS ...\n";

		$jsFiles = array();

		foreach ( array( 'commonIndexAndSite', 'index', 'site', 'widget' ) as $type ) {
			$jsFiles[ $type ] = array();
			foreach ( $extensions as $extension ) {
				$dir = APP_ROOT_DIR . $extension . '/theme/'.$theme.'/js/' . $type . DIRECTORY_SEPARATOR;
				if ( is_dir( $dir ) && $handle = opendir( $dir ) ) {
					while ( false !== ( $entry = readdir( $handle ) ) ) {
						if ( $entry != '.' && $entry != '..' && substr( $entry, - 3 ) == '.js' ) {
							$jsFiles[ $type ][ $entry ] = $dir . $entry;
						}
					}
				}
			}
		}

		$jsFilters = array();

		# IndexAndSite
		if ( APP_WEB_INDEX_DIR || APP_WEB_SITE_DIR || APP_WEB_SINGLE_SITE_DIR ) {
			$am = new AssetManager();
			foreach ( $jsFiles['commonIndexAndSite'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $jsFilters );
				$fa->setTargetPath( $nameonly );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			if ( APP_WEB_INDEX_DIR ) {
				$writer = new AssetWriter( APP_WEB_INDEX_DIR . '/theme/'.$theme.'/js/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SITE_DIR . '/theme/'.$theme.'/js/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SINGLE_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/js/' );
				$writer->writeManagerAssets( $am );
			}
		}

		# Index
		if ( APP_WEB_INDEX_DIR || APP_WEB_SINGLE_SITE_DIR ) {
			$am = new AssetManager();
			foreach ( $jsFiles['index'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $jsFilters );
				$fa->setTargetPath( $nameonly );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			if ( APP_WEB_INDEX_DIR ) {
				$writer = new AssetWriter( APP_WEB_INDEX_DIR . '/theme/'.$theme.'/js/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SINGLE_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/js/' );
				$writer->writeManagerAssets( $am );
			}
		}

		# Site
		if ( APP_WEB_SITE_DIR || APP_WEB_SINGLE_SITE_DIR ) {
			$am = new AssetManager();
			foreach ( $jsFiles['site'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $jsFilters );
				$fa->setTargetPath( $nameonly );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			if ( APP_WEB_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SITE_DIR . '/theme/'.$theme.'/js/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SINGLE_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/js/' );
				$writer->writeManagerAssets( $am );
			}
		}

	}


################################################################################ IMG

	if ( $runIMG ) {

		print "IMG ...\n";

		$imgFiles = array();

		foreach ( array( 'commonIndexAndSite', 'index', 'site', 'sysadmin' ) as $type ) {
			$imgFiles[ $type ] = array();
			foreach ( $extensions as $extension ) {
				$dir = APP_ROOT_DIR . $extension . '/theme/'.$theme.'/img/' . $type . DIRECTORY_SEPARATOR;
				if ( is_dir( $dir ) && $handle = opendir( $dir ) ) {
					while ( false !== ( $entry = readdir( $handle ) ) ) {
						if ( $entry != '.' && $entry != '..' && in_array( substr( $entry, - 4 ), array(
									'.png',
									'.gif',
									'.jpg'
								) )
						) {
							$imgFiles[ $type ][ $entry ] = $dir . $entry;
						}
					}
				}
			}
		}

		$imgFilters = array();

		# IndexAndSite
		if ( APP_WEB_INDEX_DIR || APP_WEB_SITE_DIR || APP_WEB_SINGLE_SITE_DIR ) {
			$am = new AssetManager();
			foreach ( $imgFiles['commonIndexAndSite'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $imgFilters );
				$fa->setTargetPath( $nameonly );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			if ( APP_WEB_INDEX_DIR ) {
				$writer = new AssetWriter( APP_WEB_INDEX_DIR . '/theme/'.$theme.'/img/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SITE_DIR . '/theme/'.$theme.'/img/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SINGLE_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/img/' );
				$writer->writeManagerAssets( $am );
			}
		}

		# Index
		if ( APP_WEB_INDEX_DIR || APP_WEB_SINGLE_SITE_DIR ) {
			$am = new AssetManager();
			foreach ( $imgFiles['index'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $imgFilters );
				$fa->setTargetPath( $nameonly );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			if ( APP_WEB_INDEX_DIR ) {
				$writer = new AssetWriter( APP_WEB_INDEX_DIR . '/theme/'.$theme.'/img/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SINGLE_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/img/' );
				$writer->writeManagerAssets( $am );
			}
		}

		# Site
		if ( APP_WEB_SITE_DIR || APP_WEB_SINGLE_SITE_DIR ) {
			$am = new AssetManager();
			foreach ( $imgFiles['site'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $imgFilters );
				$fa->setTargetPath( $nameonly );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			if ( APP_WEB_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SITE_DIR . '/theme/'.$theme.'/img/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SINGLE_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/img/' );
				$writer->writeManagerAssets( $am );
			}
		}


		# Sysadmin
		if ( APP_WEB_SYSADMIN_DIR || APP_WEB_SINGLE_SITE_DIR || APP_WEB_INDEX_DIR ) {
			$am = new AssetManager();
			foreach ( $imgFiles['sysadmin'] as $nameonly => $filename ) {
				$fa = new FileAsset( $filename, $imgFilters );
				$fa->setTargetPath( $nameonly );
				$am->set( str_replace( '.', '_', $nameonly ), $fa );
			}
			if ( APP_WEB_SYSADMIN_DIR ) {
				$writer = new AssetWriter( APP_WEB_SYSADMIN_DIR . '/theme/'.$theme.'/imgsysadmin/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_SINGLE_SITE_DIR ) {
				$writer = new AssetWriter( APP_WEB_SINGLE_SITE_DIR . '/theme/'.$theme.'/imgsysadmin/' );
				$writer->writeManagerAssets( $am );
			}
			if ( APP_WEB_INDEX_DIR ) {
				$writer = new AssetWriter( APP_WEB_INDEX_DIR . '/theme/'.$theme.'/imgsysadmin/' );
				$writer->writeManagerAssets( $am );
			}
		}

	}

}

