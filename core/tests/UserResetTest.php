<?php

use models\UserAccountModel;
use repositories\UserAccountRepository;
use repositories\UserAccountResetRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserResetTest extends \BaseAppWithDBTest {
	
	function test1() {

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
	
		$userAccountResetRepository = new UserAccountResetRepository();
		
		# Test1: recently unused is null
		TimeSource::mock(2013, 1, 1, 1, 0, 1);
		$x = $userAccountResetRepository->loadRecentlyUnusedSentForUserAccountId($user->getId(), 180);
		$this->assertNull($x);
		
		# Test2: Request one
		TimeSource::mock(2013, 1, 1, 1, 0, 2);
		$userAccountReset = $userAccountResetRepository->create($user);
		
		#Test 3: recently unused has one
		TimeSource::mock(2013, 1, 1, 1, 0, 3);
		$x = $userAccountResetRepository->loadRecentlyUnusedSentForUserAccountId($user->getId(), 180);
		$this->assertTrue($x != null);
		
		#Test 4: use it
		TimeSource::mock(2013, 1, 1, 1, 0, 4);
		$userRepo->resetAccount($user, $userAccountReset);
		
		# Test5: recently unused is null
		TimeSource::mock(2013, 1, 1, 1, 0, 5);
		$x = $userAccountResetRepository->loadRecentlyUnusedSentForUserAccountId($user->getId(), 180);
		$this->assertNull($x);
		
		
	}
	
	
	function test2() {

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
	
		$userAccountResetRepository = new UserAccountResetRepository();
		
		# Test1: recently unused is null
		TimeSource::mock(2013, 1, 1, 1, 0, 1);
		$x = $userAccountResetRepository->loadRecentlyUnusedSentForUserAccountId($user->getId(), 180);
		$this->assertNull($x);
		
		# Test2: Request one
		TimeSource::mock(2013, 1, 1, 1, 0, 2);
		$userAccountReset = $userAccountResetRepository->create($user);
		
		#Test 3: recently unused has one
		TimeSource::mock(2013, 1, 1, 1, 0, 3);
		$x = $userAccountResetRepository->loadRecentlyUnusedSentForUserAccountId($user->getId(), 180);
		$this->assertTrue($x != null);
		
		# Test4: days pass. recently unused is null again
		TimeSource::mock(2013, 1, 5, 1, 0, 5);
		$x = $userAccountResetRepository->loadRecentlyUnusedSentForUserAccountId($user->getId(), 180);
		$this->assertNull($x);
		
		
	}
	
	
}


