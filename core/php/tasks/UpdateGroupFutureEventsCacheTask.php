<?php

namespace tasks;

use repositories\builders\GroupRepositoryBuilder;
use repositories\GroupRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateGroupFutureEventsCacheTask extends \BaseTask {


    public function getExtensionId()
    {
        return 'org.openacalendar';
    }

    public function getTaskId()
    {
        return 'UpdateGroupFutureEventsCache';
    }

    public function getShouldRunAutomaticallyNow() {
        return $this->app['config']->taskUpdateGroupFutureEventsCacheAutomaticUpdateInterval > 0 &&
            $this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateGroupFutureEventsCacheAutomaticUpdateInterval;
    }

    protected function run()
    {
        $groupRepository = new GroupRepository($this->app);

        $grb = new GroupRepositoryBuilder($this->app);
        $count = 0;
        foreach($grb->fetchAll() as $venue) {

            $groupRepository->updateFutureEventsCache($venue);
            ++$count;
        }

        return array('result'=>'ok','count'=>$count);
    }

    public function getResultDataAsString(\models\TaskLogModel $taskLogModel) {
        if ($taskLogModel->getIsResultDataHaveKey("result") && $taskLogModel->getResultDataValue("result") == "ok") {
            return "Ok";
        } else {
            return "Fail";
        }

    }



}

