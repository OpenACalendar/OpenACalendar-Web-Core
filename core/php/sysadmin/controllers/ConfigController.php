<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ConfigController {
	
	
	function index(Request $request, Application $app) {		
		return $app['twig']->render('sysadmin/config/index.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

	function tasks(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.tasks.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

	function messageQue(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.messageque.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

	function database(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.database.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}


	function newSites(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.newsites.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}


	function media(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.media.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

	function urls(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.urls.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

	function sysadminUI(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.sysadminui.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}


	function email(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.email.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

	function logging(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.logging.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}


	function externalAnalytics(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.externalanalytics.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

	function import(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.import.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

	function themes(Request $request, Application $app) {
		return $app['twig']->render('sysadmin/config/index.themes.html.twig', array(
				'configCheck'=>new \ConfigCheck($app['config']),
			));
	}

}


