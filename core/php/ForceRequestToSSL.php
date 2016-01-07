<?php
/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


use models\SiteModel;

/**
 *
 * If Request is not SSL, redirect user to SSL.
 *
 * This class does not check config to see if forceSSL is on - it is assumed you've already done that.
 * If forcesSL is not on you should not even instantiate this class as there is no need and it will cause a minor performance hit.
 */
class ForceRequestToSSL {

    /** @var Application */
    protected $app;

    function __construct($app)
    {
        $this->app = $app;
    }

    function processForIndex() {

        if (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: https://".$this->app['config']->webIndexDomainSSL.$_SERVER['REQUEST_URI']);
            die();
        }

    }

    function processForSite(SiteModel $site) {

        if (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: https://".$site->getSlug().".".$this->app['config']->webSiteDomainSSL.$_SERVER['REQUEST_URI']);
            die();
        }

    }

}
