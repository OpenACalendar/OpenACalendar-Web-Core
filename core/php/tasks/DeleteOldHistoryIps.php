<?php

namespace tasks;


use Silex\Application;

/**
 *
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class DeleteOldHistoryIps extends \BaseTask
{

    public function getExtensionId()
    {
        return 'org.openacalendar';
    }

    public function getTaskId()
    {
        return 'DeleteOldHistoryIps';
    }


    public function getShouldRunAutomaticallyNow()
    {
        return  $this->app['config']->taskDeleteOldHistoryIpsDeleteOlderThan > 0 &&
            $this->app['config']->taskDeleteOldHistoryIpsRunInterval > 0 &&
            $this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskDeleteOldHistoryIpsRunInterval;
    }

    protected function run()
    {

        $before = $this->app['timesource']->getDateTime();
        $before->setTimestamp($before->getTimestamp() - $this->app['config']->taskDeleteOldHistoryIpsDeleteOlderThan);

        $stat = $this->app['db']->prepare("UPDATE event_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));

        $stat = $this->app['db']->prepare("UPDATE group_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));

        $stat = $this->app['db']->prepare("UPDATE area_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));

        $stat = $this->app['db']->prepare("UPDATE venue_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));

        $stat = $this->app['db']->prepare("UPDATE tag_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));

        $stat = $this->app['db']->prepare("UPDATE media_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));

        $stat = $this->app['db']->prepare("UPDATE site_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));


        $stat = $this->app['db']->prepare("UPDATE user_group_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));


        $stat = $this->app['db']->prepare("UPDATE import_url_history SET from_ip=null ".
            "WHERE created_at < :before");
        $stat->execute(array(
            'before'=>$before->format("Y-m-d H:i:s"),
        ));



        return array('result' => 'ok');

    }

}


