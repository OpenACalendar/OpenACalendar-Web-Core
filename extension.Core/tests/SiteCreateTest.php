<?php

use models\UserAccountModel;
use models\SiteModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteCreateTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		$DB = getNewTestDB();

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
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());
		
		
		$this->checkSiteInTest1($siteRepo->loadBySlug("test"));
	}
	
	protected function checkSiteInTest1(SiteModel $site) {
		$this->assertEquals("test", $site->getSlug());
		$this->assertEquals("Test", $site->getTitle());
	}
	
}


