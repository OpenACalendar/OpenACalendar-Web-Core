<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\API2ApplicationRepository;
use sysadmin\forms\ActionForm;
use sysadmin\ActionParser;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2Application {
	
	
	protected $parameters = array('api2Application'=>null);
	
	protected function build($id, Request $request, Application $app) {
		$repo = new API2ApplicationRepository();
		$this->parameters['api2Application'] = $repo->loadById($id);
		if (!$this->parameters['api2Application']) {
			$app->abort(404);
		}
	}
	
	function show($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
				
		$form = $app['form.factory']->create(new ActionForm());
		
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('sysadmin/api2app/show.html.twig', $this->parameters);		
	
	}
}
