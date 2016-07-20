<?php

namespace siteapi1\controllers;

use repositories\builders\CountryRepositoryBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryListController {


    function json(Request $request, Application $app) {

        $countryRepoBuilder = new CountryRepositoryBuilder($app);
        $countryRepoBuilder->setSiteIn($app['currentSite']);

        if (isset($_GET['titleSearch']) && trim($_GET['titleSearch'])) {
            $countryRepoBuilder->setTitleSearch($_GET['titleSearch']);
        }

        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $countryRepoBuilder->setLimit(intval($_GET['limit']));
        } else {
            $countryRepoBuilder->setLimit($app['config']->api1CountryListLimit);
        }

        $out = array();

        foreach($countryRepoBuilder->fetchAll() as $country) {
            $out[] = array(
                'twoCharCode'=>$country->getTwoCharCode(),
                'title'=>$country->getTitle(),
            );
        }

        $response = new Response(json_encode(array('data'=>$out)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }



}

