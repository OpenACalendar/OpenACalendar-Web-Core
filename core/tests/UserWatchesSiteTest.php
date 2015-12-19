<?php

use models\UserAccountModel;
use models\SiteModel;
use models\UserWatchesSiteModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\UserWatchesSiteRepository;
use repositories\builders\UserWatchesSiteRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserWatchesSiteTest extends \BaseAppWithDBTest {
	
	function test1() {
		
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
		$siteRepo->create($site, $userOwner, array(), $this->getSiteQuotaUsedForTesting());
		
		$userWatchesSiteRepo = new UserWatchesSiteRepository();
		
		# Part 1: User does not watch site
		$t = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$this->assertNull($t);
		
		$b = new UserWatchesSiteRepositoryBuilder();
		$t = $b->fetchAll();
		$this->assertEquals(1, count($t));
		
		# Part 2: Watches!
		$userWatchesSiteRepo->startUserWatchingSite($user, $site);
			
		$t = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$this->assertEquals($user->getId(), $t->getUserAccountId());
		$this->assertEquals(true, $t->getIsWatching());
		$this->assertEquals(true, $t->getIsWasOnceWatching());
		
		$b = new UserWatchesSiteRepositoryBuilder();
		$t = $b->fetchAll();
		$this->assertEquals(2, count($t));	
		
		
		# Part 3: Stops Watching!
		$userWatchesSiteRepo->stopUserWatchingSite($user, $site);

		$t = $userWatchesSiteRepo->loadByUserAndSite($user, $site);
		$this->assertEquals(false, $t->getIsWatching());
		$this->assertEquals(true, $t->getIsWasOnceWatching());
		
		$b = new UserWatchesSiteRepositoryBuilder();
		$t = $b->fetchAll();
		$this->assertEquals(1, count($t));
		
	}

}

