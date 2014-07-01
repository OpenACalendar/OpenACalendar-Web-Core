<?php

namespace sysadmin\controllers;

use Silex\Application;
use index\forms\CreateForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use repositories\SiteRepository;
use Symfony\Component\Form\FormError;
use sysadmin\forms\LogInUserForm;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LogInController {
	
	
	function index(Request $request, Application $app) {
		
		global $WEBSESSION;
				
		$form = $app['form.factory']->create(new LogInUserForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();
				
				if (userGetCurrent()->checkPassword($data['password']) && $data['extrapassword'] == $app['config']->sysAdminExtraPassword) {
					$_SESSION['sysAdminLastActive'] = \TimeSource::time();
					return $app->redirect("/sysadmin");
				} else {
					$form->addError(new FormError('passwords wrong'));
				}
				
			}
		}
		
		
		return $app['twig']->render('sysadmin/login/index.html.twig', array(
			'form'=>$form->createView(),
		));			
		
		
	}
	
	
	
}

