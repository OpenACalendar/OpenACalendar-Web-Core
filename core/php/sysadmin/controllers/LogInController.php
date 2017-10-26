<?php

namespace sysadmin\controllers;

use Silex\Application;
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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LogInController {
	
	
	function index(Request $request, Application $app) {				
		$form = $app['form.factory']->create( LogInUserForm::class);
		
		if ('POST' == $request->getMethod()) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				$data = $form->getData();
				
				if ($app['currentUser']->checkPassword($data['password']) && $data['extrapassword'] == $app['config']->sysAdminExtraPassword) {
					$app['websession']->set('sysAdminLastActive', \TimeSource::time());
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

