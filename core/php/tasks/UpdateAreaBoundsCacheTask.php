<?php

namespace tasks;

/**
 *
 * This task is dead - the thing it did isn't needed now.
 * Left so that past results still show OK in logs for now.
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateAreaBoundsCacheTask extends \BaseTask {


    public function getExtensionId()
    {
        return 'org.openacalendar';
    }

    public function getTaskId()
    {
        return 'UpdateAreaBoundsCache';
    }

    public function getShouldRunAutomaticallyNow() {
        return false;
    }

    public function getCanRunManuallyNow() {
        return false;
    }

    protected  function run() {
        return array('result'=>'Fail');
    }

    public function getResultDataAsString(\models\TaskLogModel $taskLogModel) {
        if ($taskLogModel->getIsResultDataHaveKey("result") && $taskLogModel->getResultDataValue("result") == "ok") {
            return "Ok";
        } else {
            return "Fail";
        }
    }



}

