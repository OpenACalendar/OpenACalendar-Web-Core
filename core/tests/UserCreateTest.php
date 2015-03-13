<?php

use models\UserAccountModel;
use repositories\UserAccountRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserCreateTest extends \BaseAppWithDBTest {
	
	function test1() {

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		
		$this->checkUserInTest1($userRepo->loadByID($user->getId()) );
		$this->checkUserInTest1($userRepo->loadByUserName("test") );
		$this->checkUserInTest1($userRepo->loadByEmail("test@jarofgreen.co.uk") );
		$this->checkUserInTest1($userRepo->loadByUserNameOrEmail("test") );
		$this->checkUserInTest1($userRepo->loadByUserNameOrEmail("test@jarofgreen.co.uk") );
		
	}
	
	protected function checkUserInTest1(UserAccountModel $user) {
		$this->assertEquals("test", $user->getUsername());
		$this->assertEquals("test@jarofgreen.co.uk", $user->getEmail());
		$this->assertEquals(false, $user->checkPassword("1234"));
		$this->assertEquals(true, $user->checkPassword("password"));
		$this->assertEquals(false, $user->getIsEmailVerified());
		$this->assertEquals(false, $user->getIsSystemAdmin());
		$this->assertEquals(true, $user->getIsEditor());
	}
	
}


