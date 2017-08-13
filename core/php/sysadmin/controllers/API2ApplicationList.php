<?php

namespace sysadmin\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\builders\API2ApplicationRepositoryBuilder;
use repositories\API2ApplicationRepository;
use repositories\UserAccountRepository;
use sysadmin\forms\NewAPI2ApplicationForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2ApplicationList {
	
	
	function index(Request $request, Application $app) {				
		$form = $app['form.factory']->create(NewAPI2ApplicationForm::class);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			if ($form->isValid()) {
				$data = $form->getData();
		
				$userRepo = new UserAccountRepository($app);
				$user = $userRepo->loadByEmail($data['email']);
				if ($user) {
					$appRepo = new API2ApplicationRepository($app);
					$apiapp = $appRepo->create($user, $data['title']);
					return $app->redirect("/sysadmin/api2app/".$apiapp->getId());
				} else {
					$app['flashmessages']->addError('Existing user not found!');
				}
				
			}
		}
		
		
		
		$rb = new API2ApplicationRepositoryBuilder($app);
		$apps = $rb->fetchAll();
		
		return $app['twig']->render('sysadmin/api2applist/index.html.twig', array(
				'api2apps'=>$apps,
				'form'=>$form->createView(),
			));
		
		
	}
	
}
