<?php

use models\UserAccountModel;
use models\SiteModel;
use models\CuratedListModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CuratedListRepository;
use repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListCreateTest extends \PHPUnit_Framework_TestCase {

	function test1() {
		$DB = getNewTestDB();

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userStranger = new UserAccountModel();
		$userStranger->setEmail("test2@jarofgreen.co.uk");
		$userStranger->setUsername("test2");
		$userStranger->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		$userRepo->create($userStranger);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());
		
		$curatedList = new CuratedListModel();
		$curatedList->setTitle("test");
		$curatedList->setDescription("test this!");
		
		$clRepo = new CuratedListRepository();
		$clRepo->create($curatedList, $site, $user);
		
		// check loading works
		$this->checkCuratedListInTest1($clRepo->loadBySlug($site, 1));
		
		// check user perms work
		$curatedList = $clRepo->loadBySlug($site, 1);
		$this->assertFalse($curatedList->canUserEdit(null));
		$this->assertTrue($curatedList->canUserEdit($user));
		$this->assertFalse($curatedList->canUserEdit($userStranger));
		
		$clb = new CuratedListRepositoryBuilder();
		$clb->setUserCanEdit($user);
		$this->assertEquals(1, count($clb->fetchAll()));
		
		$clb = new CuratedListRepositoryBuilder();
		$clb->setUserCanEdit($userStranger);
		$this->assertEquals(0, count($clb->fetchAll()));
	}
	
	protected function checkCuratedListInTest1(CuratedListModel $curatedList) {
		$this->assertEquals("test this!", $curatedList->getDescription());
		$this->assertEquals("test", $curatedList->getTitle());
	}
	
}


