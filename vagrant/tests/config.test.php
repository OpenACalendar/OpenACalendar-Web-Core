<?php

$CONFIG->isDebug = true;

$CONFIG->databaseName = 'openacalendar';
$CONFIG->databaseHost = 'localhost';
$CONFIG->databaseUser = 'openacalendar';
$CONFIG->databasePassword = 'password';

$CONFIG->isSingleSiteMode = true;
$CONFIG->singleSiteID = 1;

$CONFIG->webIndexDomain = "openacalendar.org";
$CONFIG->webSiteDomain = "openacalendar.org";
$CONFIG->webSysAdminDomain = "openacalendar.org";

$CONFIG->hasSSL = true;
$CONFIG->webIndexDomainSSL = "openacalendar.org";
$CONFIG->webSiteDomainSSL = "openacalendar.org";
$CONFIG->webSysAdminDomainSSL = "openacalendar.org";

$CONFIG->webCommonSessionDomain = "openacalendar.org";

$CONFIG->siteTitle = "Open A Calendar TESTS";



$CONFIG->siteSlugDemoSite = "test1";

$CONFIG->userNameReserved = array('admin','superadmin');

$CONFIG->fileStoreLocation= '/home/vagrant/fileStore';

$CONFIG->extensions = array('AddressCodeGBOpenCodePoint','Facebook','Meetup','DisplayBoard','CuratedLists','Contact');


$CONFIG->logFile = '/home/vagrant/logs/openacalendar.log';

$CONFIG->CLIAPI1Enabled = true;
