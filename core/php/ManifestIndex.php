<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ManifestIndex {


    protected $app;

    function __construct( $app ) {
        $this->app = $app;
    }

    function get() {
        return array(
            'name'=>$this->app['config']->siteTitle,
            'short_name'=>$this->app['config']->siteTitle,
            'start_url'=>'/',
        );
    }

}
