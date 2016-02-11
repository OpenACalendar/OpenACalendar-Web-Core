<?php

namespace tasks;


use Silex\Application;

/**
 *
 * Deletes Old Task Logs. Meta.
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class DeleteOldTaskLogsTask extends \BaseTask
{


    public function getExtensionId()
    {
        return 'org.openacalendar';
    }

    public function getTaskId()
    {
        return 'DeleteOldTaskLogs';
    }


    public function getShouldRunAutomaticallyNow()
    {
        return $this->app['config']->taskDeleteOldTaskLogsAutomaticRunInterval > 0 &&
        $this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskDeleteOldTaskLogsAutomaticRunInterval;
    }

    protected function run()
    {

        $before = $this->app['timesource']->getDateTime();
        $before->setTimestamp($before->getTimestamp() - $this->app['config']->taskDeleteOldTaskLogsDeleteOlderThan);

        $stat = $this->app['db']->prepare("DELETE FROM task_log ".
            "WHERE started_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));

        return array('result' => 'ok');

    }

}

