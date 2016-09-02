<?php

$CONFIG->isDebug = true;
$CONFIG->actuallySendEmail = false;

$CONFIG->databaseName = 'openacalendartest';
$CONFIG->databaseHost = 'localhost';
$CONFIG->databaseUser = 'openacalendartest';
$CONFIG->databasePassword = 'testpassword';

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

$CONFIG->installTitle = "Open A Calendar TESTS";



$CONFIG->siteSlugDemoSite = "test1";

$CONFIG->userNameReserved = array('admin','superadmin');

$CONFIG->fileStoreLocation= '/home/vagrant/fileStore';

$CONFIG->extensions = array('AddressCodeGBOpenCodePoint','Facebook','Meetup','DisplayBoard','CuratedLists','Contact');


$CONFIG->logFile = '/home/vagrant/logs/openacalendar.log';

$CONFIG->CLIAPI1Enabled = true;
