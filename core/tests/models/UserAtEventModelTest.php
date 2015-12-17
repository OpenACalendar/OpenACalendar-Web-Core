<?php

namespace tests\models;

use models\EventModel;
use models\EventRecurSetModel;
use models\SiteModel;
use models\UserAccountModel;
use models\UserAtEventModel;
use repositories\EventRecurSetRepository;
use repositories\EventRepository;
use repositories\SiteRepository;
use repositories\UserAccountRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAtEventModelTest extends \BaseAppTest
{

    function testUnknownThenYes() {
        $userAtModel = new UserAtEventModel();

        // CHECK

        $this->assertEquals(true, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanAttending());

        // CHANGE

        $userAtModel->setIsPlanAttending(true);

        // CHECK

        $this->assertEquals(false, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(true, $userAtModel->getIsPlanAttending());
    }

    function testUnknownThenMaybe() {
        $userAtModel = new UserAtEventModel();

        // CHECK

        $this->assertEquals(true, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanAttending());

        // CHANGE

        $userAtModel->setIsPlanMaybeAttending(true);

        // CHECK

        $this->assertEquals(false, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(true, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanAttending());
    }

    function testUnknownThenNo() {
        $userAtModel = new UserAtEventModel();

        // CHECK

        $this->assertEquals(true, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanAttending());

        // CHANGE

        $userAtModel->setIsPlanNotAttending(true);

        // CHECK

        $this->assertEquals(false, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(true, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanAttending());
    }

    function testYesThenUnknown() {
        $userAtModel = new UserAtEventModel();
        $userAtModel->setIsPlanAttending(true);

        // CHECK

        $this->assertEquals(false, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(true, $userAtModel->getIsPlanAttending());

        // CHANGE

        $userAtModel->setIsPlanUnknownAttending(true);

        // CHECK

        $this->assertEquals(true, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanAttending());
    }

    function testYesThenMaybe() {
        $userAtModel = new UserAtEventModel();
        $userAtModel->setIsPlanAttending(true);

        // CHECK

        $this->assertEquals(false, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(true, $userAtModel->getIsPlanAttending());

        // CHANGE

        $userAtModel->setIsPlanMaybeAttending(true);

        // CHECK

        $this->assertEquals(false, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(true, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanAttending());
    }

    function testYesThenNo() {
        $userAtModel = new UserAtEventModel();
        $userAtModel->setIsPlanAttending(true);

        // CHECK

        $this->assertEquals(false, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(true, $userAtModel->getIsPlanAttending());

        // CHANGE

        $userAtModel->setIsPlanNotAttending(true);

        // CHECK

        $this->assertEquals(false, $userAtModel->getIsPlanUnknownAttending());
        $this->assertEquals(true, $userAtModel->getIsPlanNotAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanMaybeAttending());
        $this->assertEquals(false, $userAtModel->getIsPlanAttending());
    }

}