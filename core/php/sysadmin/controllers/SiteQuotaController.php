<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\SiteQuotaModel;
use repositories\SiteQuotaRepository;
use sysadmin\forms\ActionForm;
use sysadmin\ActionParser;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteQuotaController {
	
		
	protected $parameters = array();
	
	protected function build($code, Request $request, Application $app) {
		$this->parameters = array();

		$sqr = new SiteQuotaRepository($app);
		$this->parameters['sitequota'] = $sqr->loadByCode($code);
		
		if (!$this->parameters['sitequota']) {
			$app->abort(404);
		}
	
	}
	
	function show($code, Request $request, Application $app) {

		$this->build($code, $request, $app);
		
		return $app['twig']->render('sysadmin/sitequota/show.html.twig', $this->parameters);		
	
	}
	
	
}


