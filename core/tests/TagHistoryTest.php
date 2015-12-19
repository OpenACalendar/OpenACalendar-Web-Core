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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagHistoryTest extends \BaseAppTest {



	
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

