<?php

/**
 * Config file. For a full list of options, please see 
 * http://docs-superusers.openacalendar.org/en/v1.6.x/config.html
 * 
 * @link http://docs-superusers.openacalendar.org/en/v1.6.x/config.html Config File Options
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 */

/**
 * Is in Debug mode?
 */
$CONFIG->isDebug = false;

/**
 * DB details.
 */
$CONFIG->databaseName = 'openacalendar';
$CONFIG->databaseHost = 'localhost';
$CONFIG->databaseUser = 'openacalendar';
$CONFIG->databasePassword = 'password';

/**
 * Install Title.
 */
$CONFIG->installTitle = "Open A Calendar";

/**
 * To understand the differences between Single Site and Multi Site mode see
 * http://docs-superusers.openacalendar.org/en/v1.6.x/singleSiteOrMultiSiteMode.html
 */
$CONFIG->isSingleSiteMode = TRUE;

/** 
 * If single site mode, what is the ID of the calendar that this will serve?
 * If you created the calendar as part of the install procedure it will almost certainly be 1,
 */
$CONFIG->singleSiteID = 1;

/**
 * For Single Site mode these are all the same.
 * For Multi Site mode these are all different and webSiteDomain must allow subdomains.
 * eg a value of "example.co.uk" should allow and serve any domain "*.example.co.uk" 
 */
$CONFIG->webIndexDomain = "www.example.co.uk";
$CONFIG->webSiteDomain = "example.co.uk";

/** Is SSL available? **/ 
$CONFIG->hasSSL = FALSE;
$CONFIG->webIndexDomainSSL = "www.example.co.uk";
$CONFIG->webSiteDomainSSL = "example.co.uk";

/**
 * For single site mode set same as webIndexDomain.
 * In multi site mode, cookies must travel between webIndexDomain and webSiteDomain.
 * Set this to be a common root such that cookies with this domain set can travel across all 3.
 */
$CONFIG->webCommonSessionDomain = "example.co.uk";

/**
 * A folder writeable by the app in which to store uploaded files.
 * Back this up along side your database!
 */
//$CONFIG->fileStoreLocation= '/OpenACalendarFileStore';


/**
 * Extensions.
 *
 * It is currently strongly recommended to have 'CuratedLists' installed, Parts of the core code use this.
 */
$CONFIG->extensions = array('CuratedLists');

/**
 * This is used to make sure browser caching works properly. Every time you update the software, extensions or any
 * assets add one to this variable.
 * See http://docs-superusers.openacalendar.org/en/v1.6.x/upgrading.html#assets-version-and-browser-caching for more.
 */
$CONFIG->assetsVersion = 1;

