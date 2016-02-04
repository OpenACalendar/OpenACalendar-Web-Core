<?php

use models\AreaHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\AreaModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\AreaRepository;
use repositories\AreaHistoryRepository;
use repositories\CountryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaHistoryWithDBTest extends \BaseAppWithDBTest {


	function testIntegration1() {

		$this->addCountriesToTestDB();
		$this->app['timesource']->mock(2014, 1, 1, 12, 0, 0);
		
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
		
		$countryRepo = new CountryRepository($this->app);
		$gb = $countryRepo->loadByTwoCharCode('GB');
		
		## Create area
		$this->app['timesource']->mock(2014, 1, 1, 13, 0, 0);
		$area = new AreaModel();
		$area->setTitle("test");
		$area->setDescription("test test");
		$area->setCountryId($gb->getId());
		
		$areaRepo = new AreaRepository($this->app);
		$areaRepo->create($area, null, $site, $gb, $user);
		
		## Edit area
		$this->app['timesource']->mock(2014, 1, 1, 14, 0, 0);
		
		$area = $areaRepo->loadById($area->getId());
		$area->setDescription("testy");
		$areaRepo->edit($area, $user);
		
		## Now save changed flags on these .....
		$areaHistoryRepo = new AreaHistoryRepository($this->app);
		$stat = $this->app['db']->prepare("SELECT * FROM area_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$areaHistory = new AreaHistoryModel();
			$areaHistory->setFromDataBaseRow($data);
			$areaHistoryRepo->ensureChangedFlagsAreSet($areaHistory);
		}
		
		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder($this->app);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeVenueHistory(false);
		$historyRepo->setIncludeGroupHistory(false);
		$historyRepo->setIncludeAreaHistory(true);
		$histories = $historyRepo->fetchAll();
		
		$this->assertEquals(2, count($histories));
		
		#the edit
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(true, $histories[0]->getDescriptionChanged());
		$this->assertEquals(false, $histories[0]->getCountryIdChanged());
		$this->assertEquals(false, $histories[0]->getParentAreaIdChanged());
		$this->assertEquals(false, $histories[0]->getIsDeletedChanged());		
				
		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(true, $histories[1]->getCountryIdChanged());
		$this->assertEquals(false, $histories[1]->getParentAreaIdChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());

				
		
	}

}

