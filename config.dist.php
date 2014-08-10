<?php

/**
 * Config file. For a full list of options, please see 
 * http://docs.openacalendar.org/
 * 
 * @link http://docs.openacalendar.org/ Config File Options
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
 * Site Title.
 */
$CONFIG->siteTitle = "Open A Calendar";

/**
 * To understand the differences between Single Site and Multi Site mode see
 * http://ican.openacalendar.org/docs/singlesite.html
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
$CONFIG->webSysAdminDomain = "sysadmin.example.co.uk";

/** Is SSL available? **/ 
$CONFIG->hasSSL = FALSE;
$CONFIG->webIndexDomainSSL = "www.example.co.uk";
$CONFIG->webSiteDomainSSL = "example.co.uk";
$CONFIG->webSysAdminDomainSSL = "sysadmin.example.co.uk";

/**
 * For single site mode set same as webIndexDomain.
 * In multi site mode, cookies must travel between webIndexDomain, webSiteDomain and webSysAdminDomain.
 * Set this to be a common root such that cookies with this domain set can travel across all 3.
 */
$CONFIG->webCommonSessionDomain = "example.co.uk";

/**
 * A folder writeable by the app in which to store uploaded files.
 * Back this up along side your database!
 */
//$CONFIG->fileStoreLocation= '/OpenACalendarFileStore';


