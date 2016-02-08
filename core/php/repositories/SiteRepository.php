<?php


namespace repositories;

use dbaccess\SiteDBAccess;
use models\SiteModel;
use models\UserAccountModel;
use models\SiteQuotaModel;
use models\UserGroupModel;
use Silex\Application;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteRepository {

    /** @var Application */
    private  $app;


    /** @var  \dbaccess\SiteDBAccess */
	protected $siteDBAccess;

	function __construct(Application $app)
	{
        $this->app = $app;
		$this->siteDBAccess = new SiteDBAccess($app);
	}

	public function create(SiteModel $site, UserAccountModel $owner, $countries, SiteQuotaModel $siteQuota, $canAnyUserVerifiedEdit = false) {
		$createdat = $this->app['timesource']->getFormattedForDataBase();

		if (!$site->isSlugValid($site->getSlug(), $this->app['config'])) {
			throw new Exception("Slug not valid");
		}

		try {
			$this->app['db']->beginTransaction();

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

			$stat = $this->app['db']->prepare("INSERT INTO site_information (title, slug, slug_canonical, ".
						"created_at,cached_is_multiple_timezones,cached_is_multiple_countries,".
						"cached_timezones, ".
						"is_listed_in_index,is_web_robots_allowed, ".
						" prompt_emails_days_in_advance,site_quota_id ) ".
					"VALUES (:title, :slug, :slug_canonical, ".
						" :created_at,:cached_is_multiple_timezones,:cached_is_multiple_countries,".
						":cached_timezones, ".
						":is_listed_in_index,:is_web_robots_allowed, ".
						" :prompt_emails_days_in_advance, :site_quota_id ) RETURNING id");
			$stat->execute(array(
					'title'=>substr($site->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED), 
					'slug'=> $site->getSlug(), 
					'slug_canonical'=>SiteModel::makeCanonicalSlug($site->getSlug()), 
					'cached_is_multiple_timezones'=>$site->getCachedIsMultipleTimezones() ? 1 : 0,
					'cached_is_multiple_countries'=>$site->getCachedIsMultipleCountries() ? 1 : 0,
					'cached_timezones'=>$site->getCachedTimezones(),
					'created_at'=>  $createdat,
					'is_listed_in_index'=>$site->getIsListedInIndex() ? 1 : 0,
					'is_web_robots_allowed'=>$site->getIsWebRobotsAllowed() ? 1 : 0,
					'prompt_emails_days_in_advance'=>$site->getPromptEmailsDaysInAdvance(),
					'site_quota_id'=>$siteQuota->getId(),
				));
			$data = $stat->fetch();
			$site->setId($data['id']);
			
			$stat = $this->app['db']->prepare("INSERT INTO site_history (site_id, user_account_id, ".
						"title, slug, slug_canonical, created_at,is_listed_in_index,is_web_robots_allowed, ".
						" prompt_emails_days_in_advance, is_new ) ".
					"VALUES (:site_id, :user_account_id, :title, ".
						":slug, :slug_canonical,  :created_at, :is_listed_in_index,:is_web_robots_allowed, ".
						" :prompt_emails_days_in_advance, '1' )");
			$stat->execute(array(
					'site_id'=>$site->getId(),
					'user_account_id'=>$owner->getId(),
					'title'=>substr($site->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED), 
					'slug'=> $site->getSlug(), 
					'slug_canonical'=>SiteModel::makeCanonicalSlug($site->getSlug()), 
					'created_at'=>  $createdat,
					'is_listed_in_index'=>$site->getIsListedInIndex() ? 1 : 0,
					'is_web_robots_allowed'=>$site->getIsWebRobotsAllowed() ? 1 : 0,
					'prompt_emails_days_in_advance'=>$site->getPromptEmailsDaysInAdvance(),
				));


			// Permissions

			$ugr = new UserGroupRepository($this->app);

			$userGroupEditors = new UserGroupModel();
			$userGroupEditors->setTitle("Editors");
			$userGroupEditors->setIsIncludesVerifiedUsers($canAnyUserVerifiedEdit);
			$ugr->createForSite($site, $userGroupEditors, $owner, array(array('org.openacalendar','CALENDAR_CHANGE')), array($owner));

			$userGroupEditors = new UserGroupModel();
			$userGroupEditors->setTitle("Administrators");
			$ugr->createForSite($site, $userGroupEditors, $owner, array(array('org.openacalendar','CALENDAR_ADMINISTRATE')), array($owner));

			// Countries!

			$stat = $this->app['db']->prepare("INSERT INTO country_in_site_information (site_id,country_id,is_in,is_previously_in,created_at) VALUES (:site_id,:country_id,'1','1',:created_at)");
			foreach($countries as $country) {
				$stat->execute(array( 'country_id'=>$country->getId(), 'site_id'=>$site->getId(), 'created_at'=>$createdat ));				
			}
						
			$stat = $this->app['db']->prepare("INSERT INTO user_watches_site_information (user_account_id,site_id,is_watching,is_was_once_watching,last_watch_started,created_at) ".
					"VALUES (:user_account_id,:site_id,:is_watching,:is_was_once_watching,:last_watch_started,:created_at)");
			$stat->execute(array(
					'user_account_id'=>$owner->getId(),
					'site_id'=>$site->getId(),
					'is_watching'=>'1',
					'is_was_once_watching'=>'1',
					'created_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'last_watch_started'=>  $this->app['timesource']->getFormattedForDataBase(),
				));			
			
			$this->app['db']->commit();

			// Features
			$statFeatureOn = $this->app['db']->prepare("INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) VALUES (:id, :ext, :feature, '1')");
			if($this->app['config']->newSiteHasFeatureCuratedList) {
				$statFeatureOn->execute(array('id'=>$site->getId(),'ext'=>'org.openacalendar.curatedlists','feature'=>'CuratedList'));
			}
			if($this->app['config']->newSiteHasFeatureImporter) {
				$statFeatureOn->execute(array('id'=>$site->getId(),'ext'=>'org.openacalendar','feature'=>'Importer'));
			}
			if($this->app['config']->newSiteHasFeatureMap) {
				$statFeatureOn->execute(array('id'=>$site->getId(),'ext'=>'org.openacalendar','feature'=>'Map'));
			}
			if($this->app['config']->newSiteHasFeatureVirtualEvents) {
				$statFeatureOn->execute(array('id'=>$site->getId(),'ext'=>'org.openacalendar','feature'=>'VirtualEvents'));
			}
			if($this->app['config']->newSiteHasFeaturePhysicalEvents) {
				$statFeatureOn->execute(array('id'=>$site->getId(),'ext'=>'org.openacalendar','feature'=>'PhysicalEvents'));
			}
			if($this->app['config']->newSiteHasFeatureGroup) {
				$statFeatureOn->execute(array('id'=>$site->getId(),'ext'=>'org.openacalendar','feature'=>'Group'));
			}
			if ($this->app['config']->newSiteHasFeatureTag) {
				$statFeatureOn->execute(array('id'=>$site->getId(),'ext'=>'org.openacalendar','feature'=>'Tag'));
			}

			$this->app['extensionhookrunner']->afterSiteCreate($site, $owner);

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'SiteSaved', array('site_id'=>$site->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}

	/** @deprecated */
	public function loadLegacyFeaturesOnSite(SiteModel $siteModel) {

		$stat = $this->app['db']->prepare("SELECT extension_id,feature_id,is_on FROM site_feature_information WHERE site_id=:site_id AND is_on = '1' ");
		$stat->execute(array(
			'site_id'=>$siteModel->getId(),
		));
		while($data = $stat->fetch()) {
			if ($data['extension_id'] == 'org.openacalendar.curatedlists' && $data['feature_id'] == 'CuratedList') {
				$siteModel->setIsFeatureCuratedList(true);
			}
			if ($data['extension_id'] == 'org.openacalendar' && $data['feature_id'] == 'Importer') {
				$siteModel->setIsFeatureImporter(true);
			}
			if ($data['extension_id'] == 'org.openacalendar' && $data['feature_id'] == 'Map') {
				$siteModel->setIsFeatureMap(true);
			}
			if ($data['extension_id'] == 'org.openacalendar' && $data['feature_id'] == 'VirtualEvents') {
				$siteModel->setIsFeatureVirtualEvents(true);
			}
			if ($data['extension_id'] == 'org.openacalendar' && $data['feature_id'] == 'PhysicalEvents') {
				$siteModel->setIsFeaturePhysicalEvents(true);
			}
			if ($data['extension_id'] == 'org.openacalendar' && $data['feature_id'] == 'Group') {
				$siteModel->setIsFeatureGroup(true);
			}
			if ($data['extension_id'] == 'org.openacalendar' && $data['feature_id'] == 'Tag') {
				$siteModel->setIsFeatureTag(true);
			}
		}
	}

	public function loadByDomain($domain) {
		$compareTo = $this->app['config']->webSiteDomain;
		if (strpos($compareTo, ":") > 0) {
			$compareTo = array_shift(explode(":", $compareTo));
		}
		if (substr(strtolower($_SERVER['SERVER_NAME']), 0-  strlen($compareTo)) == $compareTo) {
			$siteSlug = substr(strtolower($_SERVER['SERVER_NAME']), 0, 0- strlen($compareTo)-1);
			return $this->loadBySlug($siteSlug);
		}
		foreach($this->app['config']->webSiteAlternateDomains as $compareTo) {
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
		foreach(array( $this->app['config']->webAPI1Domain ) as $compareTo) {
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

		$stat = $this->app['db']->prepare("SELECT site_information.*, site_profile_media_information.logo_media_id ".
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

		$stat = $this->app['db']->prepare("SELECT site_information.* FROM site_information WHERE id =:id");
		$stat->execute(array( 'id'=>$id ));
		if ($stat->rowCount() > 0) {
			$site = new SiteModel();
			$site->setFromDataBaseRow($stat->fetch());
			return $site;
		}
	}
	
	public function edit(SiteModel $site, UserAccountModel $user) {


		try {
			$this->app['db']->beginTransaction();

			$fields = array('title','description_text','footer_text','is_web_robots_allowed',
				'is_closed_by_sys_admin','is_listed_in_index','closed_by_sys_admin_reason',
				'prompt_emails_days_in_advance');

			$this->siteDBAccess->update($site, $fields, $user);

			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'SiteSaved', array('site_id'=>$site->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}
	
	
	public function editCached(SiteModel $site) {

	
		$stat = $this->app['db']->prepare("UPDATE site_information SET cached_is_multiple_timezones=:cached_is_multiple_timezones, ".
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

	
		$stat = $this->app['db']->prepare("UPDATE site_information SET site_quota_id=:site_quota_id WHERE id=:id");
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

		

		try {
			$this->app['db']->beginTransaction();


			$this->siteDBAccess->update($site, array('slug'), $user);


			$this->app['db']->commit();
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
		
	}
	
}

