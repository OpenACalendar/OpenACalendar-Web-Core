<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionController {


	protected $parameters = array();

	protected function build($id, Request $request, Application $app) {

		$this->parameters['extension'] = $app['extensions']->getExtensionById($id);
		if (!$this->parameters['extension']) {
			$app->abort(404);
		}

	}


	function index($id, Request $request, Application $app) {
		$this->build($id, $request, $app);

		$this->parameters['userpermissions'] = array();
		foreach($this->parameters['extension']->getUserPermissions() as $key) {
			$this->parameters['userpermissions'][] = $this->parameters['extension']->getUserPermission($key);
		}

		return $app['twig']->render('sysadmin/extension/index.html.twig', $this->parameters);
	}


}


