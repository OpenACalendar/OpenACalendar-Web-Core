<?php
use models\SiteModel;
use models\UserAccountModel;
use repositories\SiteFeatureRepository;
use repositories\SiteRepository;
use repositories\UserAccountRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteFeatureRepositoryTest  extends \BaseAppWithDBTest
{

	/**
	 *
	 */
	function test1() {

		$feature = new \sitefeatures\EditCommentsFeature();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");

		$userRepo = new UserAccountRepository();
		$userRepo->create($user);

		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");

		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$siteFeatureRepo = new SiteFeatureRepository($this->app);


		// Test Get Default Option
		$this->app['timesource']->mock(2015,1,1,1,1,1);

		$data = $siteFeatureRepo->getForSiteAsTree($site);
		$this->assertEquals(false, $data[$feature->getExtensionId()][$feature->getFeatureId()]->isOn());


		// Test Set True
		$this->app['timesource']->mock(2015,1,1,1,1,2);

		$siteFeatureRepo->setFeature($site, $feature, true, $user);

		$data = $siteFeatureRepo->getForSiteAsTree($site);
		$this->assertEquals(true, $data[$feature->getExtensionId()][$feature->getFeatureId()]->isOn());


		// Test Set False
		$this->app['timesource']->mock(2015,1,1,1,1,3);

		$siteFeatureRepo->setFeature($site, $feature, false, $user);

		$data = $siteFeatureRepo->getForSiteAsTree($site);
		$this->assertEquals(false, $data[$feature->getExtensionId()][$feature->getFeatureId()]->isOn());


		// Test Set False whilst already false
		$this->app['timesource']->mock(2015,1,1,1,1,4);

		$siteFeatureRepo->setFeature($site, $feature, false, $user);

		$data = $siteFeatureRepo->getForSiteAsTree($site);
		$this->assertEquals(false, $data[$feature->getExtensionId()][$feature->getFeatureId()]->isOn());

	}

}