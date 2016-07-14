<?php

namespace tests\models;

use models\ImportModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportModelTest extends \BaseAppTest
{

    function testGuessATitleIfMissingWhenNotMissing() {
        $importModel = new ImportModel();
        $importModel->setTitle("Title");
        $importModel->setUrl("http://google.com");
        $importModel->guessATitleIfMissing();

        $this->assertEquals("Title", $importModel->getTitle());
    }

    function testGuessATitleIfMissingWhenMissing() {
        $importModel = new ImportModel();
        $importModel->setUrl("http://google.com");
        $importModel->guessATitleIfMissing();

        $this->assertEquals("http://google.com", $importModel->getTitle());
    }


}

