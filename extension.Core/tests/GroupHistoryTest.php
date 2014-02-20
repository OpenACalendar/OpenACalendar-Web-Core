<?php

use models\GroupHistoryModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupHistoryTest extends \PHPUnit_Framework_TestCase {

	
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
	}
	
}

