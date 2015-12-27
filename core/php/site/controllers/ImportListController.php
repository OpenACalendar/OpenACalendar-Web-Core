<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\ImportModel;
use repositories\builders\ImportRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportListController {

	
	function index(Application $app) {
		

		$grb = new ImportRepositoryBuilder();
		$grb->setSite($app['currentSite']);
		
		$imports = $grb->fetchAll();
		
		return $app['twig']->render('site/importlist/index.html.twig', array(
				'imports'=>$imports,
			));
				
	}

}

