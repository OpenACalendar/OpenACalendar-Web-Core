<?php


namespace repositories;

use dbaccess\SiteDBAccess;
use models\SiteModel;
use models\UserAccountModel;
use models\SiteQuotaModel;
use models\UserGroupModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteRepository {
	

	/** @var  \dbaccess\SiteDBAccess */
	protected $siteDBAccess;

	function __construct()
	{
		global $DB, $USERAGENT;
		$this->siteDBAccess = new SiteDBAccess($DB, new \TimeSource(), $USERAGENT);
	}

	public function create(SiteModel $site, UserAccountModel $owner, $countries, SiteQuotaModel $siteQuota, $canAnyUserVerifiedEdit = false) {
		global $DB, $CONFIG, $EXTENSIONHOOKRUNNER;
		$createdat = \TimeSource::getFormattedForDataBase();
		
		try {
			$DB->beginTransaction();

			// TODO should check slug not already exist and nice error
			
			$timezones = array();
			foreach($countries as $country) {
				foreach(explode(",", $country->getTimezones()) as $timeZone) {
					$timezones[] = $timeZone;
				}
			}
			$site->setCachedTimezonesAsList($timezones);
			$site->setCachedIsMultipleCountries(count($countries) > 1);

			// Site

			$stat = $DB->prepare("INSERT INTO site_information (title, slug, slug_canonical, ".
						"created_at,cached_is_multiple_timezones,cached_is_multiple_countries,".
						"cached_timezones,is_feature_map,is_feature_importer,is_feature_curated_list,".
						"is_listed_in_index,is_web_robots_allowed, ".
						" prompt_emails_days_in_advance,site_quota_id, ".
						"is_feature_tag,is_feature_physical_events,is_feature_virtual_events) ".
					"VALUES (:title, :slug, :slug_canonical, ".
						" :created_at,:cached_is_multiple_timezones,:cached_is_multiple_countries,".
						":cached_timezones,:is_feature_map,:is_feature_importer,:is_feature_curated_list,".
						":is_listed_in_index,:is_web_robots_allowed, ".
						" :prompt_emails_days_in_advance, :site_quota_id, ".
						":is_feature_tag,:is_feature_physical_events,:is_feature_virtual_events) RETURNING id");
			$stat->execute(array(
					'title'=>substr($site->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED), 
					'slug'=> $site->getSlug(), 
					'slug_canonical'=>SiteModel::makeCanonicalSlug($site->getSlug()), 
					'cached_is_multiple_timezones'=>$site->getCachedIsMultipleTimezones() ? 1 : 0,
					'cached_is_multiple_countries'=>$site->getCachedIsMultipleCountries() ? 1 : 0,
					'cached_timezones'=>$site->getCachedTimezones(),
					'created_at'=>  $createdat,
					'is_feature_curated_list'=>$site->getIsFeatureCuratedList() ? 1 : 0,
					'is_feature_importer'=>$site->getIsFeatureImporter() ? 1 : 0,
					'is_feature_map'=>$site->getIsFeatureMap() ? 1 : 0,
					'is_feature_tag'=>$site->getIsFeatureTag() ? 1 : 0,
					'is_feature_virtual_events'=>$site->getIsFeatureVirtualEvents() ? 1 : 0,
					'is_feature_physical_events'=>$site->getIsFeaturePhysicalEvents() ? 1 : 0,
					'is_listed_in_index'=>$site->getIsListedInIndex() ? 1 : 0,
					'is_web_robots_allowed'=>$site->getIsWebRobotsAllowed() ? 1 : 0,
					'prompt_emails_days_in_advance'=>$site->getPromptEmailsDaysInAdvance(),
					'site_quota_id'=>$siteQuota->getId(),
				));
			$data = $stat->fetch();
			$site->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO site_history (site_id, user_account_id, ".
						"title, slug, slug_canonical, created_at,is_feature_map,is_feature_importer,".
						"is_feature_curated_list,is_listed_in_index,is_web_robots_allowed, ".
						" prompt_emails_days_in_advance, is_new,".
						"is_feature_tag,is_feature_physical_events,is_feature_virtual_events) ".
					"VALUES (:site_id, :user_account_id, :title, ".
						":slug, :slug_canonical,  :created_at,:is_feature_map,:is_feature_importer,".
						":is_feature_curated_list,:is_listed_in_index,:is_web_robots_allowed, ".
						" :prompt_emails_days_in_advance, '1', ".
						":is_feature_tag,:is_feature_physical_events,:is_feature_virtual_events)");
			$stat->execute(array(
					'site_id'=>$site->getId(),
					'user_account_id'=>$owner->getId(),
					'title'=>substr($site->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED), 
					'slug'=> $site->getSlug(), 
					'slug_canonical'=>SiteModel::makeCanonicalSlug($site->getSlug()), 
					'created_at'=>  $createdat,
					'is_feature_curated_list'=>$site->getIsFeatureCuratedList() ? 1 : 0,
					'is_feature_importer'=>$site->getIsFeatureImporter() ? 1 : 0,
					'is_feature_map'=>$site->getIsFeatureMap() ? 1 : 0,
					'is_feature_tag'=>$site->getIsFeatureTag() ? 1 : 0,
					'is_feature_virtual_events'=>$site->getIsFeatureVirtualEvents() ? 1 : 0,
					'is_feature_physical_events'=>$site->getIsFeaturePhysicalEvents() ? 1 : 0,
					'is_listed_in_index'=>$site->getIsListedInIndex() ? 1 : 0,
					'is_web_robots_allowed'=>$site->getIsWebRobotsAllowed() ? 1 : 0,
					'prompt_emails_days_in_advance'=>$site->getPromptEmailsDaysInAdvance(),
				));


			// Permissions

			$ugr = new UserGroupRepository();

			$userGroupEditors = new UserGroupModel();
			$userGroupEditors->setTitle("Editors");
			$userGroupEditors->setIsIncludesVerifiedUsers($canAnyUserVerifiedEdit);
			$ugr->createForSite($site, $userGroupEditors, $owner, array(array('org.openacalendar','CALENDAR_CHANGE')), array($owner));

			$userGroupEditors = new UserGroupModel();
			$userGroupEditors->setTitle("Administrators");
			$ugr->createForSite($site, $userGroupEditors, $owner, array(array('org.openacalendar','CALENDAR_ADMINISTRATE')), array($owner));

			// Countries!

			$stat = $DB->prepare("INSERT INTO country_in_site_information (site_id,country_id,is_in,is_previously_in,created_at) VALUES (:site_id,:country_id,'1','1',:created_at)");
			foreach($countries as $country) {
				$stat->execute(array( 'country_id'=>$country->getId(), 'site_id'=>$site->getId(), 'created_at'=>$createdat ));				
			}
						
			$stat = $DB->prepare("INSERT INTO user_watches_site_information (user_account_id,site_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:site_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$owner->getId(),
					'site_id'=>$site->getId(),
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  \TimeSource::getFormattedForDataBase(),
					'last_watch_started'=>  \TimeSource::getFormattedForDataBase(),
				));			
			
			$DB->commit();

			$EXTENSIONHOOKRUNNER->afterSiteCreate($site, $owner);
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function loadByDomain($domain) {
		global $CONFIG;
		$compareTo = $CONFIG->webSiteDomain;
		if (strpos($compareTo, ":") > 0) {
			$compareTo = array_shift(explode(":", $compareTo));
		}
		if (substr(strtolower($_SERVER['SERVER_NAME']), 0-  strlen($compareTo)) == $compareTo) {
			$siteSlug = substr(strtolower($_SERVER['SERVER_NAME']), 0, 0- strlen($compareTo)-1);
			return $this->loadBySlug($siteSlug);
		}
		foreach($CONFIG->webSiteAlternateDomains as $compareTo) {
			if (strpos($compareTo, ":") > 0) {
				$compareTo = array_shift(explode(":", $compareTo));
			}
			if (substr(strtolower($_SERVER['SERVER_NAME']), 0-  strlen($compareTo)) == $compareTo) {
				$siteSlug = substr(strtolower($_SERVER['SERVER_NAME']), 0, 0- strlen($compareTo)-1);
				return $this->loadBySlug($siteSlug);
			}
		}
		die("ERROR");
	}
	
	/** 
	 * 
	 * @deprecated
	 */
	public function loadByAPIDomain($domain) {
		global $CONFIG;
		foreach(array( $CONFIG->webAPI1Domain ) as $compareTo) {
			if (strpos($compareTo, ":") > 0) {
				$compareTo = array_shift(explode(":", $compareTo));
			}
			if (substr(strtolower($_SERVER['SERVER_NAME']), 0-  strlen($compareTo)) == $compareTo) {
				$siteSlug = substr(strtolower($_SERVER['SERVER_NAME']), 0, 0- strlen($compareTo)-1);
				return $this->loadBySlug($siteSlug);
			}
		}
		die("ERROR");
	}
	
	public function loadBySlug($slug) {
		global $DB;
		$stat = $DB->prepare("SELECT site_information.*, site_profile_media_information.logo_media_id ".
				"FROM site_information ".
				"LEFT JOIN site_profile_media_information ON site_profile_media_information.site_id = site_information.id ".
				"WHERE slug_canonical =:detail");
		$stat->execute(array( 'detail'=>SiteModel::makeCanonicalSlug($slug) ));
		if ($stat->rowCount() > 0) {
			$site = new SiteModel();
			$site->setFromDataBaseRow($stat->fetch());
			return $site;
		}
	}
	
	
	public function loadById($id) {
		global $DB;
		$stat = $DB->prepare("SELECT site_information.* FROM site_information WHERE id =:id");
		$stat->execute(array( 'id'=>$id ));
		if ($stat->rowCount() > 0) {
			$site = new SiteModel();
			$site->setFromDataBaseRow($stat->fetch());
			return $site;
		}
	}
	
	public function edit(SiteModel $site, UserAccountModel $user) {
		global $DB;

		try {
			$DB->beginTransaction();

			$fields = array('title','description_text','footer_text','is_web_robots_allowed',
				'is_closed_by_sys_admin','is_listed_in_index','closed_by_sys_admin_reason',
				'is_feature_curated_list','is_feature_importer','is_feature_map',
				'is_feature_tag','is_feature_virtual_events','is_feature_physical_events','is_feature_group',
				'prompt_emails_days_in_advance');

			$this->siteDBAccess->update($site, $fields, $user);

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function editCached(SiteModel $site) {
		global $DB;
	
		$stat = $DB->prepare("UPDATE site_information SET cached_is_multiple_timezones=:cached_is_multiple_timezones, ".
				" cached_is_multiple_countries = :cached_is_multiple_countries, ".
				" cached_timezones= :cached_timezones".
				" WHERE id=:id");
		$stat->execute(array(
				'cached_is_multiple_timezones'=>$site->getCachedIsMultipleTimezones() ? 1 : 0,
				'cached_is_multiple_countries'=>$site->getCachedIsMultipleCountries() ? 1 : 0,
				'cached_timezones'=>$site->getCachedTimezones(),
				'id'=>$site->getId(),
			));
	}
	
	
	public function editQuota(SiteModel $site, UserAccountModel $user = null) {
		global $DB;
	
		$stat = $DB->prepare("UPDATE site_information SET site_quota_id=:site_quota_id WHERE id=:id");
		$stat->execute(array(
				'site_quota_id'=>$site->getSiteQuotaId(),
				'id'=>$site->getId(),
			));
	}
	
	
	
	/**
	 * 
	 * @TODO Nice error on duplicate slug
	 */
	public function editSlug(SiteModel $site, UserAccountModel $user = null) {
		global $DB;
		

		try {
			$DB->beginTransaction();


			$this->siteDBAccess->update($site, array('slug'), $user);


			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
		
	}
	
}

