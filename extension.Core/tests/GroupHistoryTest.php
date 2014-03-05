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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupHistoryTest extends \PHPUnit_Framework_TestCase {

	
	function testIntegration1() {
		$DB = getNewTestDB();
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
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());
		
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
		$stat = $DB->prepare("SELECT * FROM group_history");
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
	
	function testSetChangedFlagsFromNothing1() {
		$groupHistory = new GroupHistoryModel();
		$groupHistory->setFromDataBaseRow(array(
			'group_id'=>1,
			'title'=>'New Group',
			'description'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'url'=>'',
			'twitter_username'=>'',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$groupHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(true,  $groupHistory->getTitleChanged());
		$this->assertEquals(false,  $groupHistory->getDescriptionChanged());
		$this->assertEquals(false,  $groupHistory->getUrlChanged());
		$this->assertEquals(false,  $groupHistory->getTwitterUsernameChanged());
		$this->assertEquals(false,  $groupHistory->getIsDeletedChanged());
		$this->assertEquals(true, $groupHistory->getIsNew());
	}
	
	function testSetChangedFlagsFromNothing2() {
		$groupHistory = new GroupHistoryModel();
		$groupHistory->setFromDataBaseRow(array(
			'group_id'=>1,
			'title'=>'',
			'description'=>'This group has no name',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'url'=>'http://www.groupwithnoname.com',
			'twitter_username'=>'noname',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$groupHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(false,  $groupHistory->getTitleChanged());
		$this->assertEquals(true,  $groupHistory->getDescriptionChanged());
		$this->assertEquals(true,  $groupHistory->getUrlChanged());
		$this->assertEquals(true,  $groupHistory->getTwitterUsernameChanged());
		$this->assertEquals(false,  $groupHistory->getIsDeletedChanged());
		$this->assertEquals(true, $groupHistory->getIsNew());
	}
	
	function testSetChangedFlagsFromLast1() {
		$lastHistory = new GroupHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'group_id'=>1,
			'title'=>'Cat',
			'description'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'url'=>'http://www.groupwithnoname.com',
			'twitter_username'=>'noname',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$groupHistory = new GroupHistoryModel();
		$groupHistory->setFromDataBaseRow(array(
			'group_id'=>1,
			'title'=>'Cat',
			'description'=>'This group has no name',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 12:00:00',
			'url'=>'',
			'twitter_username'=>'noname',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$groupHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(false,  $groupHistory->getTitleChanged());
		$this->assertEquals(true,  $groupHistory->getDescriptionChanged());
		$this->assertEquals(true,  $groupHistory->getUrlChanged());
		$this->assertEquals(false,  $groupHistory->getTwitterUsernameChanged());
		$this->assertEquals(false,  $groupHistory->getIsDeletedChanged());
		$this->assertEquals(false, $groupHistory->getIsNew());
	}
	
	function testSetChangedFlagsFromLast2() {
		$lastHistory = new GroupHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'group_id'=>1,
			'title'=>'Cat',
			'description'=>'no dogs allowed',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'url'=>'http://www.groupwithnoname.com',
			'twitter_username'=>'noname',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$groupHistory = new GroupHistoryModel();
		$groupHistory->setFromDataBaseRow(array(
			'group_id'=>1,
			'title'=>'Cat People Only',
			'description'=>'no dogs allowed',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'url'=>'http://www.groupwithnoname.com',
			'twitter_username'=>'catppl',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$groupHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(true,  $groupHistory->getTitleChanged());
		$this->assertEquals(false,  $groupHistory->getDescriptionChanged());
		$this->assertEquals(false,  $groupHistory->getUrlChanged());
		$this->assertEquals(true,  $groupHistory->getTwitterUsernameChanged());
		$this->assertEquals(false,  $groupHistory->getIsDeletedChanged());
		$this->assertEquals(false, $groupHistory->getIsNew());
	}
	
	function testSetChangedFlagsFromLast3() {
		$lastHistory = new GroupHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'group_id'=>1,
			'title'=>'Cat',
			'description'=>'no dogs allowed',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'url'=>'http://www.groupwithnoname.com',
			'twitter_username'=>'noname',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$groupHistory = new GroupHistoryModel();
		$groupHistory->setFromDataBaseRow(array(
			'group_id'=>1,
			'title'=>'Cat',
			'description'=>'no dogs allowed',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'url'=>'http://www.groupwithnoname.com',
			'twitter_username'=>'noname',
			'is_deleted'=>1,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$groupHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(false,  $groupHistory->getTitleChanged());
		$this->assertEquals(false,  $groupHistory->getDescriptionChanged());
		$this->assertEquals(false,  $groupHistory->getUrlChanged());
		$this->assertEquals(false,  $groupHistory->getTwitterUsernameChanged());
		$this->assertEquals(true,  $groupHistory->getIsDeletedChanged());
		$this->assertEquals(false, $groupHistory->getIsNew());
	}
	
}

