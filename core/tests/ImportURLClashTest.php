<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\ImportModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLClashTest extends \BaseAppWithDBTest
{

    function dataForTestClash()
    {
        return array(
            array('http://www.group.com', 'http://www.group.com'),
            array('http://www.group.com', 'http://www.GROUP.com'),
            array('https://www.group.com', 'http://www.GROUP.com'),
        );
    }

    /**
     * @dataProvider dataForTestClash
     * @group import
     */
    function testClash($originalURL, $clashURL)
    {
        
        $user = new UserAccountModel();
        $user->setEmail("test@jarofgreen.co.uk");
        $user->setUsername("test");
        $user->setPassword("password");

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->create($user);

        $site = new SiteModel();
        $site->setTitle("Test");
        $site->setSlug("test");

        $siteRepo = new SiteRepository($this->app);
        $siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

        $group = new GroupModel();
        $group->setTitle("test");
        $group->setDescription("test test");
        $group->setUrl($originalURL);

        $groupRepo = new GroupRepository($this->app);
        $groupRepo->create($group, $site, $user);

        $importRepository = new ImportRepository($this->app);

        $originalImport = new ImportModel();
        $originalImport->setIsEnabled(true);
        $originalImport->setSiteId($site->getId());
        $originalImport->setGroupId($group->getId());
        $originalImport->setTitle("Test");
        $originalImport->setUrl($originalURL);

        $clash = $importRepository->loadClashForImportUrl($originalImport);
        $this->assertNull($clash);

        $importRepository->create($originalImport, $site, $user);

        $clashURLImport = new ImportModel();
        $clashURLImport->setIsEnabled(true);
        $clashURLImport->setSiteId($site->getId());
        $clashURLImport->setGroupId($group->getId());
        $clashURLImport->setTitle("Test.com site");
        $clashURLImport->setUrl($clashURL);

        $clash = $importRepository->loadClashForImportUrl($clashURLImport);
        $this->assertTrue($clash != null);

    }

    function dataForTestNoClash()
    {
        return array(
            array('http://www.group.com', 'http://www.example.com'),
        );
    }

    /**
     * @dataProvider dataForTestNoClash
     * @group import
     */
    function testNoClash($originalURL, $noClashURL)
    {


        $user = new UserAccountModel();
        $user->setEmail("test@jarofgreen.co.uk");
        $user->setUsername("test");
        $user->setPassword("password");

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->create($user);

        $site = new SiteModel();
        $site->setTitle("Test");
        $site->setSlug("test");

        $siteRepo = new SiteRepository($this->app);
        $siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

        $group = new GroupModel();
        $group->setTitle("test");
        $group->setDescription("test test");
        $group->setUrl($originalURL);

        $groupRepo = new GroupRepository($this->app);
        $groupRepo->create($group, $site, $user);

        $importRepository = new ImportRepository($this->app);

        $originalImport = new ImportModel();
        $originalImport->setIsEnabled(true);
        $originalImport->setSiteId($site->getId());
        $originalImport->setGroupId($group->getId());
        $originalImport->setTitle("Test");
        $originalImport->setUrl($originalURL);

        $clash = $importRepository->loadClashForImportUrl($originalImport);
        $this->assertNull($clash);

        $importRepository->create($originalImport, $site, $user);

        $noClashURLImport = new ImportModel();
        $noClashURLImport->setIsEnabled(true);
        $noClashURLImport->setSiteId($site->getId());
        $noClashURLImport->setGroupId($group->getId());
        $noClashURLImport->setTitle("Test.com site");
        $noClashURLImport->setUrl($noClashURL);

        $clash = $importRepository->loadClashForImportUrl($noClashURLImport);
        $this->assertNull($clash);

    }

}


