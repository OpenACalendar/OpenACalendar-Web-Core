<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\ImportModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportURLClashTest extends \BaseAppWithDBTest {

    /**
     *
     * @group import
     */
    function test1() {


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
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository($this->app);
		$groupRepo->create($group, $site, $user);
		
		$importRepository = new ImportRepository($this->app);
		
		$newImportURL = new ImportModel();
		$newImportURL->setIsEnabled(true);
		$newImportURL->setSiteId($site->getId());
		$newImportURL->setGroupId($group->getId());
		$newImportURL->setTitle("Test");
		$newImportURL->setUrl("http://test.com");
		
		# no clash
		$clash = $importRepository->loadClashForImportUrl($newImportURL);
		$this->assertNull($clash);
		
		# save import, now clash!
		$importRepository->create($newImportURL, $site, $user);
		
		$newImportURL2 = new ImportModel();
		$newImportURL2->setIsEnabled(true);
		$newImportURL2->setSiteId($site->getId());
		$newImportURL2->setGroupId($group->getId());
		$newImportURL2->setTitle("Test.com site");
		$newImportURL2->setUrl("http://TEST.com");
		
		# no clash
		$clash = $importRepository->loadClashForImportUrl($newImportURL2);
		$this->assertTrue($clash != null);	
		
	}
	
}


