<?php

use models\UserAccountModel;
use models\SiteModel;
use models\TagModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\TagRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagCreateTest extends \BaseAppWithDBTest {
	
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
		
		$tag = new TagModel();
		$tag->setTitle("test");
		$tag->setDescription("test test");
		
		$tagRepo = new TagRepository();
		$tagRepo->create($tag, $site, $user);
		
		$this->checkTagInTest1($tagRepo->loadById($tag->getId()));
		$this->checkTagInTest1	($tagRepo->loadBySlug($site, $tag->getSlug()));
	}
	
	protected function checkTagInTest1(TagModel $tag) {
		$this->assertEquals("test test", $tag->getDescription());
		$this->assertEquals("test", $tag->getTitle());
	}
	
}




