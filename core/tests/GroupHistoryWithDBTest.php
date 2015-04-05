<?php

use models\GroupHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\GroupHistoryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupHistoryTest extends \BaseAppWithDBTest {

	
	function testIntegration1() {
		\TimeSource::mock(2014, 1, 1, 12, 0, 0);
		
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
		
		## Create group
		\TimeSource::mock(2014, 1, 1, 13, 0, 0);
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		
		## Edit group
		\TimeSource::mock(2014, 1, 1, 14, 0, 0);
		
		$group = $groupRepo->loadById($group->getId());
		$group->setTwitterUsername("testy");
		$groupRepo->edit($group, $user);
		
		## Now save changed flags on these .....
		$groupHistoryRepo = new GroupHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM group_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$groupHistory = new GroupHistoryModel();
			$groupHistory->setFromDataBaseRow($data);
			$groupHistoryRepo->ensureChangedFlagsAreSet($groupHistory);
		}
		
		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder();
		$historyRepo->setGroup($group);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeVenueHistory(false);
		$historyRepo->setIncludeGroupHistory(true);
		$histories = $historyRepo->fetchAll();
		
		$this->assertEquals(2, count($histories));
		
		#the edit
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(false, $histories[0]->getDescriptionChanged());
		$this->assertEquals(false, $histories[0]->getUrlChanged());
		$this->assertEquals(true, $histories[0]->getTwitterUsernameChanged());
		$this->assertEquals(false, $histories[0]->getIsDeletedChanged());		
				
		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(true, $histories[1]->getUrlChanged());
		$this->assertEquals(false, $histories[1]->getTwitterUsernameChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());

				
		
	}

}

