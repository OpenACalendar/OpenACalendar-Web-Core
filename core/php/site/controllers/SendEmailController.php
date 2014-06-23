<?php

namespace site\controllers;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use repositories\SendEmailRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendEmailController {
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();
		
		$sec = new SendEmailRepository();
		$this->parameters['sendemail'] = $sec->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['sendemail']) {
			return false;
		}
		
		
		return true;
	}
	
	function show($slug, Request $request, Application $app) {
		global $WEBSESSION;
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Email does not exist.");
		}
		
		if ($request->request->get('actionSend')  && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {	
			$this->parameters['sendemail']->send($app, userGetCurrent());
			$sec = new SendEmailRepository();
			$sec->markSent($this->parameters['sendemail'], userGetCurrent());
			
			return $app->redirect("/admin/sendemail/".$this->parameters['sendemail']->getSlug());
		}
		
		if ($request->request->get('actionDiscard')  && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {	
			// TODO
		}
		
		return $app['twig']->render('site/sendemail/show.html.twig', $this->parameters);
	}
	
	
}

