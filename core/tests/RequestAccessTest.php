<?php

use models\UserAccountModel;
use models\SiteModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\SiteAccessRequestRepository;
use repositories\UserInSiteRepository;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\builders\SiteAccessRequestRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RequestAccessTest extends \PHPUnit_Framework_TestCase {
	
	function testSearch1() {
		$DB = getNewTestDB();

		$userOwner = new UserAccountModel();
		$userOwner->setEmail("test@jarofgreen.co.uk");
		$userOwner->setUsername("test");
		$userOwner->setPassword("password");
		
		$user = new UserAccountModel();
		$user->setEmail("test1@jarofgreen.co.uk");
		$user->setUsername("test1");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($userOwner);
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $userOwner, array(), getSiteQuotaUsedForTesting());
		
		$siteAccessRequestRepo = new SiteAccessRequestRepository();
		$userInSiteRepositoryRepo = new UserInSiteRepository();
		
		# No requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(0, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(0, count($requests));
		
		$this->assertFalse($siteAccessRequestRepo->isCurrentRequestExistsForSiteAndUser($site, $user));
		
		# make request
		$siteAccessRequestRepo->create($site, $user, "1");
		
		# 1 user, 1 requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(1, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(1, count($requests));
		
		$this->assertTrue($siteAccessRequestRepo->isCurrentRequestExistsForSiteAndUser($site, $user));
		
		# make 2nd request
		$siteAccessRequestRepo->create($site, $user, "Hurry up!");
		
		# still 1 user, 2 requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(1, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(2, count($requests));		
		
		# request granted!
		$siteAccessRequestRepo->grantForSiteAndUser($site, $user, $userOwner);
		
		# now 0 user, 0 requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(0, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(0, count($requests));	
		
		$this->assertFalse($siteAccessRequestRepo->isCurrentRequestExistsForSiteAndUser($site, $user));
		
		# now user removed
		$userInSiteRepositoryRepo->removeUserEditsSite($user, $site);
		
		# still 0 user, 0 requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(0, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(0, count($requests));	
		
		$this->assertFalse($siteAccessRequestRepo->isCurrentRequestExistsForSiteAndUser($site, $user));
		
		# user requests again
		$siteAccessRequestRepo->create($site, $user, "Oi! Give me access back!");

		# 1 user, 1 requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(1, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(1, count($requests));	
		
		$this->assertTrue($siteAccessRequestRepo->isCurrentRequestExistsForSiteAndUser($site, $user));
		
		
		
		
	}
	
	
	function testSearch2() {
		$DB = getNewTestDB();

		$userOwner = new UserAccountModel();
		$userOwner->setEmail("test@jarofgreen.co.uk");
		$userOwner->setUsername("test");
		$userOwner->setPassword("password");
		
		$user = new UserAccountModel();
		$user->setEmail("test1@jarofgreen.co.uk");
		$user->setUsername("test1");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($userOwner);
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $userOwner, array(), getSiteQuotaUsedForTesting());
		
		$siteAccessRequestRepo = new SiteAccessRequestRepository();
		
		# No requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(0, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(0, count($requests));
		
		# make request
		$siteAccessRequestRepo->create($site, $user, "1");
		
		# 1 user, 1 requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(1, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(1, count($requests));
		
		$this->assertTrue($siteAccessRequestRepo->isCurrentRequestExistsForSiteAndUser($site, $user));
		
		# DENIED!
		$siteAccessRequestRepo->rejectForSiteAndUser($site, $user, $userOwner);
		
		# now 0 user, 0 requests
		$r = new UserAccountRepositoryBuilder;
		$r->setRequestAccessSite($site);
		$users = $r->fetchAll();
		$this->assertEquals(0, count($users));
		
		$s = new SiteAccessRequestRepositoryBuilder();
		$s->setSite($site);
		$s->setUser($user);
		$requests = $s->fetchAll();
		$this->assertEquals(0, count($requests));	
		
		$this->assertFalse($siteAccessRequestRepo->isCurrentRequestExistsForSiteAndUser($site, $user));
		
		
	}
	
	
}


