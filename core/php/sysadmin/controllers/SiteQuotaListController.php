<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\builders\SiteQuotaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class SiteQuotaListController {
	
	
	function index(Request $request, Application $app) {
		
		
		$sqrb = new SiteQuotaRepositoryBuilder($app);
		$sitequotas = $sqrb->fetchAll();
		
		return $app['twig']->render('sysadmin/sitequotalist/index.html.twig', array(
				'sitequotas'=>$sitequotas,
			));
		
	}
	
	
}

