<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\ImportURLModel;
use repositories\builders\ImportURLRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLListController {

	
	function index(Application $app) {
		

		$grb = new ImportURLRepositoryBuilder();
		$grb->setSite($app['currentSite']);
		
		$importurls = $grb->fetchAll();
		
		return $app['twig']->render('site/importurllist/index.html.twig', array(
				'importurls'=>$importurls,
			));
				
	}

}

