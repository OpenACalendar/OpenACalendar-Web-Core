<?php

namespace messagequeworkers;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseMessageQueWorker
{

    protected $app;

    function __construct($app)
    {
        $this->app = $app;
    }

    abstract function process($extension, $type, $data);

}
