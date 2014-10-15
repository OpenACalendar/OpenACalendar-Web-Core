<?php



/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class Config {
	
	public $isDebug = false;

	public $databaseType= 'pgsql';
	public $databaseName;
	public $databaseHost;
	public $databaseUser;
	public $databasePassword;
	
	public $siteSlugReserved = array('www',	'blog');
	
	public $siteSlugDemoSite = "demo";
	
	public $isSingleSiteMode = false;
	public $singleSiteID = 1;

	public $webIndexDomain = "www.example.com";
	public $webSiteDomain = "example.com";
	/** @deprecated **/
	public $webAPI1Domain = "api1.example.com";

	public $hasSSL = true;
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
	
	public $piwikServerHTTP = null;
	public $piwikServerHTTPS = null;
	public $piwikSiteID = null;
	
	public $fileStoreLocation = null;
	public $tmpFileCacheLocation = '/tmp/openacalendarCache/';
	public $tmpFileCacheCreationPermissions = 0733;


	public $logFile = '/tmp/openacalendar.log';
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
	
	public $userWatchesGroupPromptEmailShowEvents = 3;
	public $userWatchesSitePromptEmailShowEvents = 3;
	public $userWatchesSiteGroupPromptEmailShowEvents = 3;
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
	
	public $importURLExpireSecondsAfterLastEdit = 7776000; // 90 days
	public $importURLSecondsBetweenImports = 36000; // 10 hours
	public $importURLAllowEventsSecondsIntoFuture = 7776000; // 90 days
	
	public $upcomingEventsForUserEmailTextListsEvents = 20;
	public $upcomingEventsForUserEmailHTMLListsEvents = 5;
	
	public $siteSeenCookieStoreForDays = 30;

	public $extensions = array();
	
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

	public $findDuplicateEventsShow = 3;
	public $findDuplicateEventsThreshhold = 2;
	
	public $SMTPPort = 25;
	public $SMTPHost = "localhost";
	public $SMTPUsername = null;
	public $SMTPPassword = null;
	public $SMTPEncyption = null;
	public $SMTPAuthMode = null;
		
	public $recurEventForDaysInFutureWhenWeekly = 93; // 3 * 31
	public $recurEventForDaysInFutureWhenMonthly = 186; // 6 * 31
	
	public $CLIAPI1Enabled = false;

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
	

