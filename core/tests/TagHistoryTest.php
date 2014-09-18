<?php

use models\TagHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\TagModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\TagRepository;
use repositories\TagHistoryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagHistoryTest extends \PHPUnit_Framework_TestCase {

	
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
		
		## Create tag
		\TimeSource::mock(2014, 1, 1, 13, 0, 0);
		$tag = new TagModel();
		$tag->setTitle("test");
		$tag->setDescription("test test");
		
		$tagRepo = new TagRepository();
		$tagRepo->create($tag, $site, $user);
		
		## Edit tag
		\TimeSource::mock(2014, 1, 1, 14, 0, 0);
		
		$tag = $tagRepo->loadById($tag->getId());
		$tag->setDescription("testy");
		$tagRepo->edit($tag, $user);
		
		## Now save changed flags on these .....
		$tagHistoryRepo = new TagHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM tag_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$tagHistory = new TagHistoryModel();
			$tagHistory->setFromDataBaseRow($data);
			$tagHistoryRepo->ensureChangedFlagsAreSet($tagHistory);
		}
		
		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder();
		$historyRepo->setTag($tag);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeVenueHistory(false);
		$historyRepo->setIncludeGroupHistory(true);
		$historyRepo->setIncludeTagHistory(true);
		$histories = $historyRepo->fetchAll();
		
		$this->assertEquals(2, count($histories));
		
		#the edit
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(true, $histories[0]->getDescriptionChanged());
		$this->assertEquals(false, $histories[0]->getIsDeletedChanged());		
				
		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());

				
		
	}

	function testIntegration2() {
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

		## Create tag
		\TimeSource::mock(2014, 1, 1, 13, 0, 0);
		$tag = new TagModel();
		$tag->setTitle("test");
		$tag->setDescription("test test");

		$tagRepo = new TagRepository();
		$tagRepo->create($tag, $site, $user);

		## Delete tag
		\TimeSource::mock(2014, 1, 1, 14, 0, 0);

		$tag = $tagRepo->loadById($tag->getId());
		$tagRepo->delete($tag, $user);

		## Now save changed flags on these .....
		$tagHistoryRepo = new TagHistoryRepository();
		$stat = $DB->prepare("SELECT * FROM tag_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$tagHistory = new TagHistoryModel();
			$tagHistory->setFromDataBaseRow($data);
			$tagHistoryRepo->ensureChangedFlagsAreSet($tagHistory);
		}

		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder();
		$historyRepo->setTag($tag);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeVenueHistory(false);
		$historyRepo->setIncludeGroupHistory(true);
		$historyRepo->setIncludeTagHistory(true);
		$histories = $historyRepo->fetchAll();

		$this->assertEquals(2, count($histories));

		#the Delete
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(false, $histories[0]->getDescriptionChanged());
		$this->assertEquals(true, $histories[0]->getIsDeletedChanged());

		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());



	}
	
	function testSetChangedFlagsFromNothing1() {
		$tagHistory = new TagHistoryModel();
		$tagHistory->setFromDataBaseRow(array(
			'tag_id'=>1,
			'title'=>'New Tag',
			'description'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$tagHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(true,  $tagHistory->getTitleChanged());
		$this->assertEquals(false,  $tagHistory->getDescriptionChanged());
		$this->assertEquals(false,  $tagHistory->getIsDeletedChanged());
		$this->assertEquals(true, $tagHistory->getIsNew());
	}
	
	function testSetChangedFlagsFromNothing2() {
		$tagHistory = new TagHistoryModel();
		$tagHistory->setFromDataBaseRow(array(
			'tag_id'=>1,
			'title'=>'',
			'description'=>'This tag has no name',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$tagHistory->setChangedFlagsFromNothing();
		
		$this->assertEquals(false,  $tagHistory->getTitleChanged());
		$this->assertEquals(true,  $tagHistory->getDescriptionChanged());
		$this->assertEquals(false,  $tagHistory->getIsDeletedChanged());
		$this->assertEquals(true, $tagHistory->getIsNew());
	}
	
	function testSetChangedFlagsFromLast1() {
		$lastHistory = new TagHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'tag_id'=>1,
			'title'=>'Cat',
			'description'=>'',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$tagHistory = new TagHistoryModel();
		$tagHistory->setFromDataBaseRow(array(
			'tag_id'=>1,
			'title'=>'Cat',
			'description'=>'This tag has no name',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 12:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$tagHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(false,  $tagHistory->getTitleChanged());
		$this->assertEquals(true,  $tagHistory->getDescriptionChanged());
		$this->assertEquals(false,  $tagHistory->getIsDeletedChanged());
		$this->assertEquals(false, $tagHistory->getIsNew());
	}
	
	function testSetChangedFlagsFromLast2() {
		$lastHistory = new TagHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'tag_id'=>1,
			'title'=>'Cat',
			'description'=>'no dogs allowed',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$tagHistory = new TagHistoryModel();
		$tagHistory->setFromDataBaseRow(array(
			'tag_id'=>1,
			'title'=>'Cat People Only',
			'description'=>'no dogs allowed',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$tagHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(true,  $tagHistory->getTitleChanged());
		$this->assertEquals(false,  $tagHistory->getDescriptionChanged());
		$this->assertEquals(false,  $tagHistory->getIsDeletedChanged());
		$this->assertEquals(false, $tagHistory->getIsNew());
	}
	
	function testSetChangedFlagsFromLast3() {
		$lastHistory = new TagHistoryModel();
		$lastHistory->setFromDataBaseRow(array(
			'tag_id'=>1,
			'title'=>'Cat',
			'description'=>'no dogs allowed',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>0,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$tagHistory = new TagHistoryModel();
		$tagHistory->setFromDataBaseRow(array(
			'tag_id'=>1,
			'title'=>'Cat',
			'description'=>'no dogs allowed',
			'user_account_id'=>1,
			'created_at'=>'2014-02-01 10:00:00',
			'is_deleted'=>1,
			'title_changed'=>0,
			'description_changed'=>0,
			'url_changed'=>0,
			'twitter_username_changed'=>0,
			'is_deleted_changed'=>0,
		));
		
		$tagHistory->setChangedFlagsFromLast($lastHistory);
		
		$this->assertEquals(false,  $tagHistory->getTitleChanged());
		$this->assertEquals(false,  $tagHistory->getDescriptionChanged());
		$this->assertEquals(true,  $tagHistory->getIsDeletedChanged());
		$this->assertEquals(false, $tagHistory->getIsNew());
	}
	
}

