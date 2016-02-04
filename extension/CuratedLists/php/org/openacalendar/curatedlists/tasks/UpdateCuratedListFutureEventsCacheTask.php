<?php

namespace org\openacalendar\curatedlists\tasks;

use org\openacalendar\curatedlists\models\CuratedListHistoryModel;
use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;
use org\openacalendar\curatedlists\repositories\CuratedListHistoryRepository;
use org\openacalendar\curatedlists\repositories\CuratedListRepository;


/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UpdateCuratedListFutureEventsCacheTask extends \BaseTask {

    public function getExtensionId()
    {
        return 'org.openacalendar.curatedlists';
    }

    public function getTaskId()
    {
        return 'UpdateHistoryFutureEventsCacheTask';
    }

    public function getShouldRunAutomaticallyNow() {
        return $this->getLastRunEndedAgoInSeconds() > 30*60; // TODO $config
    }


    protected function run()
    {
        $curatedListRepository = new CuratedListRepository();

        $clrb = new CuratedListRepositoryBuilder($this->app);
        $count = 0;
        foreach($clrb->fetchAll() as $curatedList) {

            $curatedListRepository->updateFutureEventsCache($curatedList);
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
