<?php

namespace org\openacalendar\curatedlists\site\controllers;


use org\openacalendar\curatedlists\repositories\builders\filterparams\CuratedListFilterParams;
use Silex\Application;
use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListListController {


    function index(Application $app) {


        $params = new CuratedListFilterParams();
        $params->set($_GET);
        $params->getCuratedListRepositoryBuilder()->setSite($app['currentSite']);
        $params->getCuratedListRepositoryBuilder()->setIncludeDeleted(false);

        $lists = $params->getCuratedListRepositoryBuilder()->fetchAll();

        return $app['twig']->render('site/curatedlistlist/index.html.twig', array(
            'curatedlists'=>$lists,
            'curatedListListFilterParams'=>$params,
        ));

    }

}

