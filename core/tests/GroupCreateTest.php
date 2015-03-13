<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\builders\GroupRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupCreateTest extends \BaseAppWithDBTest {
	
	function test1() {

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
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);
		
		$this->checkGroupInTest1($groupRepo->loadById($group->getId()));
		$this->checkGroupInTest1($groupRepo->loadBySlug($site, $group->getSlug()));
		
		$grb = new GroupRepositoryBuilder();
		$grb->setFreeTextsearch('test');
		$this->assertEquals(1, count($grb->fetchAll()));	
		
		$grb = new GroupRepositoryBuilder();
		$grb->setFreeTextsearch('cats');
		$this->assertEquals(0, count($grb->fetchAll()));	

	}
	
	protected function checkGroupInTest1(GroupModel $group) {
		$this->assertEquals("test test", $group->getDescription());
		$this->assertEquals("test", $group->getTitle());
		$this->assertEquals("http://www.group.com", $group->getUrl());
	}
	
}




