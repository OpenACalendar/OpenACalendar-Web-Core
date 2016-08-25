<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ManifestSite {

    protected $app;

    function __construct( $app ) {
        $this->app = $app;
    }

    function get(\models\SiteModel $site) {
        return array(
            'name'=>$site->getTitle() . ($this->app['config']->isSingleSiteMode ? '' : ' : '.$this->app['config']->installTitle),
            'short_name'=>$site->getTitle(),
            'start_url'=>'/',
        );
    }

}
