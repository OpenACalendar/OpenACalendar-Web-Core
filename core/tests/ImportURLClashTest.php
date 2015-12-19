<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\ImportURLModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportURLRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportURLClashTest extends \BaseAppWithDBTest {
	
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
		
		$importURLRepository = new ImportURLRepository();
		
		$newImportURL = new ImportURLModel();
		$newImportURL->setIsEnabled(true);
		$newImportURL->setSiteId($site->getId());
		$newImportURL->setGroupId($group->getId());
		$newImportURL->setTitle("Test");
		$newImportURL->setUrl("http://test.com");
		
		# no clash
		$clash = $importURLRepository->loadClashForImportUrl($newImportURL);
		$this->assertNull($clash);
		
		# save import, now clash!
		$importURLRepository->create($newImportURL, $site, $user);
		
		$newImportURL2 = new ImportURLModel();
		$newImportURL2->setIsEnabled(true);
		$newImportURL2->setSiteId($site->getId());
		$newImportURL2->setGroupId($group->getId());
		$newImportURL2->setTitle("Test.com site");
		$newImportURL2->setUrl("http://TEST.com");
		
		# no clash
		$clash = $importURLRepository->loadClashForImportUrl($newImportURL2);
		$this->assertTrue($clash != null);	
		
	}
	
}


