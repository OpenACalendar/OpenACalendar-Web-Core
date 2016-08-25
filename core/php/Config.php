<?php



/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class Config {
	
	public $isDebug = false;

	public $databaseType= 'pgsql';
	public $databaseName;
	public $databaseHost;
	public $databaseUser;
	public $databasePassword;

	public $userNameReserved = array();

	public $siteSlugReserved = array('www',	'blog');
	
	public $siteSlugDemoSite = "demo";
	
	public $isSingleSiteMode = false;
	public $singleSiteID = 1;

	public $webIndexDomain = "www.example.com";
	public $webSiteDomain = "example.com";
	/** @deprecated **/
	public $webAPI1Domain = "api1.example.com";

	public $hasSSL = true;
    public $forceSSL = false;
	public $webIndexDomainSSL = "www.example.com";
	public $webSiteDomainSSL = "example.com";

	public $webSiteAlternateDomains = array("example.org");

	public $webCommonSessionDomain = "example.com";

	public $bcryptRounds = 5;
	
	public $siteTitle = "Open A Calendar";
	
	public $emailFrom = "hello@example.com";
	public $emailFromName = "Open A Calendar";
	
	public $cacheFeedsInSeconds = 3600;
	
	public $cacheSiteLogoInSeconds = 604800; // 1 week
	
	public $siteReadOnly = false;
	public $siteReadOnlyReason = null;
	
	public $contactTwitter = null;
	
	public $contactEmail = null;
	public $contactEmailHTML = null;
	
	public $facebookPage=null;
	public $googlePlusPage=null;
	public $ourBlog= "http://blog.example.com/";
	
	/** "12hr" or "24hr" **/
	public $clockDisplayDefault = "12hr";
	
	public $assetsVersion = 1;

	public $eventsCantBeMoreThanYearsInFuture = 2; 
	public $eventsCantBeMoreThanYearsInPast = 1; 	
	public $calendarEarliestYearAllowed = 2012;

	public $sysAdminLogInTimeOutSeconds = 900;  // 15 mins
	
	
	public $newUsersAreEditors = true;
	public $allowNewUsersToRegister = true;
	
	
	public $userAccountVerificationSecondsBetweenAllowedSends = 900;  // 15 mins

	public $googleAnalyticsTracking = null;

	public $piwikServerHTTP = null;
	public $piwikServerHTTPS = null;
	public $piwikSiteID = null;
	
	public $fileStoreLocation = null;
	public $tmpFileCacheLocation = '/tmp/openacalendarCache/';
	public $tmpFileCacheCreationPermissions = 0733;


	public $logFile = '/tmp/openacalendar.log';
	public $logLevel = 'error';
	public $logToStdError = false;
		
	public $logFileParseDateTimeRange = '/tmp/openacalendarParseDateTimeRange.log';
	
	public $sysAdminExtraPassword = "1234";
	public $sysAdminTimeZone = "Europe/London";

	public $sysAdminExtraPurgeEventPassword = null;
	public $sysAdminExtraPurgeGroupPassword = null;
	public $sysAdminExtraPurgeVenuePassword = null;
	public $sysAdminExtraPurgeAreaPassword = null;
	public $sysAdminExtraPurgeCuratedListPassword = null;


	public $sessionLastsInSeconds = 14400; // 4 hours, 4 * 60 * 60
	
	public $resetEmailsGapBetweenInSeconds = 600; // 10 mins
	
	public $userWatchesGroupPromptEmailShowEventsMax = 100;
	public $userWatchesSitePromptEmailShowEventsMax = 100;
	public $userWatchesSiteGroupPromptEmailShowEventsMax = 100;
	public $userWatchesPromptEmailSafeGapDays = 30;
	
	public $newSiteHasFeatureMap = true;
	public $newSiteHasFeatureCuratedList = false;
	public $newSiteHasFeatureImporter = false;
	public $newSiteHasFeatureGroup = true;
	public $newSiteHasFeatureVirtualEvents = false;
	public $newSiteHasFeaturePhysicalEvents = true;
	public $newSiteHasFeatureTag = false;
	public $newSitePromptEmailsDaysInAdvance = 10;
	public $newSiteHasQuotaCode = 'BASIC';
	
	public $newUserRegisterAntiSpam = false;
	public $contactFormAntiSpam = false;
	
	public $importExpireSecondsAfterLastEdit = 7776000; // 90 days
	public $importSecondsBetweenImports = 36000; // 10 hours
	public $importAllowEventsSecondsIntoFuture = 7776000; // 90 days

    public $importLimitToSaveOnEachRunImportedEvents = 1000;
    public $importLimitToSaveOnEachRunEvents = 100;
	
	public $upcomingEventsForUserEmailTextListsEvents = 20;
	public $upcomingEventsForUserEmailHTMLListsEvents = 5;
	
	public $siteSeenCookieStoreForDays = 30;

	public $extensions  = array('CuratedLists');
	
	public $mediaNormalSize = 500;
	public $mediaThumbnailSize = 100;
	public $mediaQualityJpeg = 90;
	public $mediaQualityPng = 2;
	public $mediaBrowserCacheExpiresInseconds = 7776000; // 90 days

	public $apiExtraHeader1Html = null;
	public $apiExtraHeader1Text = null;

	public $apiExtraFooter1Html = null;
	public $apiExtraFooter1Text = null;

	public $api1EventListLimit = 1000;
	public $api1CountryListLimit = 1000;
	public $api1AreaListLimit = 1000;
	public $api1TagListLimit = 1000;
	public $api1GroupListLimit = 1000;

	public $findDuplicateEventsShow = 3;
	public $findDuplicateEventsThreshhold = 2;
	public $findDuplicateEventsNoMatchSummary = array();

	public $SMTPPort = 25;
	public $SMTPHost = "localhost";
	public $SMTPUsername = null;
	public $SMTPPassword = null;
	public $SMTPEncyption = null;
	public $SMTPAuthMode = null;
		
	public $recurEventForDaysInFutureWhenWeekly = 93; // 3 * 31
	public $recurEventForDaysInFutureWhenMonthly = 186; // 6 * 31
	
	public $CLIAPI1Enabled = false;

	public $taskUpdateVenueFutureEventsCacheAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateGroupFutureEventsCacheAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateAreaFutureEventsCacheAutomaticUpdateInterval = 1800; // 30 mins
	public $taskSendUserWatchesNotifyAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateAreaBoundsCacheAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateAreaParentCacheAutomaticUpdateInterval = 3600; // 60 mins
	public $taskUpdateSiteCacheAutomaticUpdateInterval = 3600; // 60 mins
	public $taskUpdateAreaHistoryChangeFlagsAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateEventHistoryChangeFlagsAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateGroupHistoryChangeFlagsAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateImportURLHistoryChangeFlagsAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateSiteHistoryChangeFlagsAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateMediaHistoryChangeFlagsAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateTagHistoryChangeFlagsAutomaticUpdateInterval = 1800; // 30 mins
	public $taskUpdateHistoryChangeFlagsTaskAutomaticUpdateInterval = 1800; // 30 mins
	public $taskRunImportURLsAutomaticUpdateInterval = 1800; // 30 mins

    public $taskDeleteOldTaskLogsAutomaticRunInterval = 86400; // 1 day
    public $taskDeleteOldTaskLogsDeleteOlderThan = 15552000;  // 180 days, 6 months


    public $useBeanstalkd = false;
    // This is the default connection if Debian package is used.
    public $beanstalkdHost = 'localhost';
    public $beanstalkdPort = 11300;
    public $beanstalkdTube = 'openacalendar';
    public $beanstalkdProducerConnectTimeOut = 5;
    public $beanstalkdConsumerConnectTimeOut = 60;

    public $messageQueConsumerProcessRunsForSeconds = 3900; // 65 * 60;
    public $messageQueConsumerProcessChecksEverySeconds = 60;

	public $formWidgetTimeMinutesMultiples = 1;

    public $slugMaxLength = 50;

    public $useLibraryCDNs = true;

	public $themeVariables = array('default'=>array());

	/** DEPRECATED */
	public $canCreateSitesVerifiedEditorUsers = true;

	function getWebIndexDomainSecure() {
		return $this->hasSSL ? "https://".$this->webIndexDomainSSL : "http://".$this->webIndexDomain;
	}
	function getWebSiteDomainSecure($siteslug) {
		if ($this->isSingleSiteMode) {
			return $this->hasSSL ? "https://".$this->webSiteDomainSSL : "http://".$this->webSiteDomain;
		} else {
			return $this->hasSSL ? "https://".$siteslug.".".$this->webSiteDomainSSL : "http://".$siteslug.".".$this->webSiteDomain;
		}
	}
	function isFileStore() {
		return (boolean)$this->fileStoreLocation;
	}
}
	

