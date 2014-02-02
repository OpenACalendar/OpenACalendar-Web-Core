<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\UserWatchesGroupModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\UserWatchesGroupRepository;
use repositories\UserWatchesSiteRepository;
use repositories\builders\UserWatchesGroupRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesGroupTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		$DB = getNewTestDB();
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userOwner = new UserAccountModel();
		$userOwner->setEmail("test2@jarofgreen.co.uk");
		$userOwner->setUsername("test2");
		$userOwner->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($userOwner);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $userOwner, array(), getSiteQuotaUsedForTesting());
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $userOwner);
		
		$userWatchesgroupRepo = new UserWatchesGroupRepository();
		
		# Part 1: User does not watch group
		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertNull($t);
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(0, count($t));
		
		# Part 2: Watches!
		$userWatchesgroupRepo->startUserWatchingGroup($user, $group);
			
		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertEquals($user->getId(), $t->getUserAccountId());
		$this->assertEquals(true, $t->getIsWatching());
		$this->assertEquals(true, $t->getIsWasOnceWatching());
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(1, count($t));	
		
		
		# Part 3: Stops Watching!
		$userWatchesgroupRepo->stopUserWatchingGroup($user, $group);

		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertEquals(false, $t->getIsWatching());
		$this->assertEquals(true, $t->getIsWasOnceWatching());
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(0, count($t));
		
	}
	
	function test2() {
		$DB = getNewTestDB();
		
		$userOwner = new UserAccountModel();
		$userOwner->setEmail("test2@jarofgreen.co.uk");
		$userOwner->setUsername("test2");
		$userOwner->setPassword("password");
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($userOwner);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $userOwner, array(), getSiteQuotaUsedForTesting());
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $userOwner);
		
		$userWatchesgroupRepo = new UserWatchesGroupRepository();
		
		# Part 1: User does not watch group
		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertNull($t);
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(0, count($t));
		
		# Part 2: Watches!
		$userWatchesgroupRepo->startUserWatchingGroup($user, $group);
			
		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertEquals($user->getId(), $t->getUserAccountId());
		$this->assertEquals(true, $t->getIsWatching());
		$this->assertEquals(true, $t->getIsWasOnceWatching());
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(1, count($t));	
		
		
		# Part 3: Starts watching site, automatically stops Watching group as reported by UserWatchesGroupRepositoryBuilder!
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		$userWatchesSiteRepo->startUserWatchingSite($user, $site);

		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(0, count($t));
		
	}

	
	function test3() {
		$DB = getNewTestDB();
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userOwner = new UserAccountModel();
		$userOwner->setEmail("test2@jarofgreen.co.uk");
		$userOwner->setUsername("test2");
		$userOwner->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($userOwner);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $userOwner, array(), getSiteQuotaUsedForTesting());
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $userOwner);
		
		$userWatchesgroupRepo = new UserWatchesGroupRepository();
		
		# Part 1: User does not watch group
		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertNull($t);
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(0, count($t));
		
		# Part 2: Watches if not watched before!
		$userWatchesgroupRepo->startUserWatchingGroupIfNotWatchedBefore($user, $group);
			
		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertEquals($user->getId(), $t->getUserAccountId());
		$this->assertEquals(true, $t->getIsWatching());
		$this->assertEquals(true, $t->getIsWasOnceWatching());
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(1, count($t));	
		
		
		# Part 3: Stops Watching!
		$userWatchesgroupRepo->stopUserWatchingGroup($user, $group);

		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertEquals(false, $t->getIsWatching());
		$this->assertEquals(true, $t->getIsWasOnceWatching());
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(0, count($t));
		
		# Part 4: Watches if not watched before! As they have watched before, nothing happens
		$userWatchesgroupRepo->startUserWatchingGroupIfNotWatchedBefore($user, $group);
		
		$t = $userWatchesgroupRepo->loadByUserAndGroup($user, $group);
		$this->assertEquals(false, $t->getIsWatching());
		$this->assertEquals(true, $t->getIsWasOnceWatching());
		
		$b = new UserWatchesGroupRepositoryBuilder();
		$b->setGroup($group);
		$b->setUser($user);
		$t = $b->fetchAll();
		$this->assertEquals(0, count($t));
		
		
	}
	
	
	
}

