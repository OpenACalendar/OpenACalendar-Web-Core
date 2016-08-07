<?php

namespace tests\actions;

use actions\GetAreaForLatLng;
use models\AreaModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\AreaRepository;
use repositories\CountryRepository;
use repositories\SiteRepository;
use repositories\UserAccountRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GetAreaForLatLngTest extends \BaseAppWithDBTest {


    public function testNoAreas() {

        $this->addCountriesToTestDB();
        $countryRepo = new CountryRepository($this->app);

        $user = new UserAccountModel();
        $user->setEmail("test@jarofgreen.co.uk");
        $user->setUsername("test");
        $user->setPassword("password");

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->create($user);

        $site = new SiteModel();
        $site->setTitle("Test");
        $site->setSlug("test");

        $siteRepo = new SiteRepository($this->app);
        $siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

        $action = new GetAreaForLatLng($this->app, $site);
        $this->assertNull($action->getArea(55.950580, -3.203751, $countryRepo->loadByTwoCharCode('GB')));

    }

    public function testAreaWithNoLatLng() {

        $this->addCountriesToTestDB();
        $countryRepo = new CountryRepository($this->app);

        $user = new UserAccountModel();
        $user->setEmail("test@jarofgreen.co.uk");
        $user->setUsername("test");
        $user->setPassword("password");

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->create($user);

        $site = new SiteModel();
        $site->setTitle("Test");
        $site->setSlug("test");

        $siteRepo = new SiteRepository($this->app);
        $siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());


        $area = new AreaModel();
        $area->setSiteId($site->getId());
        $area->setTitle('Scotland');
        $area->setCountryId($countryRepo->loadByTwoCharCode('GB'));

        $areaRepo = new AreaRepository($this->app);
        $areaRepo->create($area, null, $site, $countryRepo->loadByTwoCharCode('GB'), $user);

        $action = new GetAreaForLatLng($this->app, $site);
        $this->assertNull($action->getArea(55.950580, -3.203751, $countryRepo->loadByTwoCharCode('GB')));

    }

    public function testOnlyOneArea() {

        $this->addCountriesToTestDB();
        $countryRepo = new CountryRepository($this->app);

        $user = new UserAccountModel();
        $user->setEmail("test@jarofgreen.co.uk");
        $user->setUsername("test");
        $user->setPassword("password");

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->create($user);

        $site = new SiteModel();
        $site->setTitle("Test");
        $site->setSlug("test");

        $siteRepo = new SiteRepository($this->app);
        $siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

        $areaRepo = new AreaRepository($this->app);

        $areaScotland = new AreaModel();
        $areaScotland->setSiteId($site->getId());
        $areaScotland->setTitle('Scotland');
        $areaScotland->setMinLat(55.573169);
        $areaScotland->setMinLng(-6.317139);
        $areaScotland->setMaxLat(60.893935);
        $areaScotland->setMaxLng(-0.604248);
        $areaScotland->setCountryId($countryRepo->loadByTwoCharCode('GB'));
        $areaRepo->create($areaScotland, null, $site, $countryRepo->loadByTwoCharCode('GB'), $user);


        $areaEngland = new AreaModel();
        $areaEngland->setSiteId($site->getId());
        $areaEngland->setTitle('England');
        $areaEngland->setMinLat(49.643919);
        $areaEngland->setMinLng(-6.635742);
        $areaEngland->setMaxLat(55.980970);
        $areaEngland->setMaxLng(2.010498);
        $areaEngland->setCountryId($countryRepo->loadByTwoCharCode('GB'));
        $areaRepo->create($areaEngland, null, $site, $countryRepo->loadByTwoCharCode('GB'), $user);

        // Manchester
        $action = new GetAreaForLatLng($this->app, $site);
        $gotArea = $action->getArea(53.456439, -2.252197, $countryRepo->loadByTwoCharCode('GB'));
        $this->assertNotNull($gotArea);
        $this->assertEquals($areaEngland->getId(), $gotArea->getId());

        // Orkney
        $action = new GetAreaForLatLng($this->app, $site);
        $gotArea = $action->getArea(58.962784, -2.914585, $countryRepo->loadByTwoCharCode('GB'));
        $this->assertNotNull($gotArea);
        $this->assertEquals($areaScotland->getId(), $gotArea->getId());


    }

    public function testAreaInArea() {

        $this->addCountriesToTestDB();
        $countryRepo = new CountryRepository($this->app);

        $user = new UserAccountModel();
        $user->setEmail("test@jarofgreen.co.uk");
        $user->setUsername("test");
        $user->setPassword("password");

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->create($user);

        $site = new SiteModel();
        $site->setTitle("Test");
        $site->setSlug("test");

        $siteRepo = new SiteRepository($this->app);
        $siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

        $areaRepo = new AreaRepository($this->app);

        $areaScotland = new AreaModel();
        $areaScotland->setSiteId($site->getId());
        $areaScotland->setTitle('Scotland');
        $areaScotland->setMinLat(55.573169);
        $areaScotland->setMinLng(-6.317139);
        $areaScotland->setMaxLat(60.893935);
        $areaScotland->setMaxLng(-0.604248);
        $areaScotland->setCountryId($countryRepo->loadByTwoCharCode('GB'));
        $areaRepo->create($areaScotland, null, $site, $countryRepo->loadByTwoCharCode('GB'), $user);


        $areaStAndrews = new AreaModel();
        $areaStAndrews->setSiteId($site->getId());
        $areaStAndrews->setTitle('St. Andrews');
        $areaStAndrews->setParentAreaId($areaScotland->getId());
        $areaStAndrews->setMinLat(56.322090);
        $areaStAndrews->setMinLng(-2.837563);
        $areaStAndrews->setMaxLat(56.360527);
        $areaStAndrews->setMaxLng(-2.761345);
        $areaStAndrews->setCountryId($countryRepo->loadByTwoCharCode('GB'));
        $areaRepo->create($areaStAndrews, $areaScotland, $site, $countryRepo->loadByTwoCharCode('GB'), $user);


        $areaEngland = new AreaModel();
        $areaEngland->setSiteId($site->getId());
        $areaEngland->setTitle('England');
        $areaEngland->setMinLat(49.643919);
        $areaEngland->setMinLng(-6.635742);
        $areaEngland->setMaxLat(55.980970);
        $areaEngland->setMaxLng(2.010498);
        $areaEngland->setCountryId($countryRepo->loadByTwoCharCode('GB'));
        $areaRepo->create($areaEngland, null, $site, $countryRepo->loadByTwoCharCode('GB'), $user);

        // TEST
        $action = new GetAreaForLatLng($this->app, $site);
        $gotArea = $action->getArea(56.341938, -2.789176, $countryRepo->loadByTwoCharCode('GB'));
        $this->assertNotNull($gotArea);
        $this->assertEquals($areaStAndrews->getId(), $gotArea->getId());

    }

}
