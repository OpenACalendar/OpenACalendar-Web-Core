<?php

namespace tests\repositories;


use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\builders\GroupRepositoryBuilder;
use TimeSource;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountRepositoryTest extends \BaseAppWithDBTest
{


    function testPurge() {

        ## CREATE
        $this->app['timesource']->mock(2013,7,1,7,0,0);

        $user = new UserAccountModel();
        $user->setEmail("test@jarofgreen.co.uk");
        $user->setUsername("test");
        $user->setPassword("password");

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->create($user);

        ## Can Purge
        $this->assertTrue($this->app['extensions']->getExtensionById('org.openacalendar')->canPurgeUser($user));

        ## Purge

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->purge($user);

    }


}
