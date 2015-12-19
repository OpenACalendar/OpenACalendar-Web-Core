<?php

namespace org\openacalendar\contact\index\controllers;

use org\openacalendar\contact\index\forms\ContactForm;
use org\openacalendar\contact\models\ContactSupportModel;
use org\openacalendar\contact\repositories\ContactSupportRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @package org.openacalendar.contact
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IndexController {


	function contact(Application $app, Request $request) {		
		$form = $app['form.factory']->create(new ContactForm($app['currentUser']));
		
		if (!$app['config']->siteReadOnly && 'POST' == $request->getMethod()) {
			$form->bind($request);
			if ($form->isValid()) {
				$data = $form->getData();

				$contact = new ContactSupportModel();
				$contact->setSubject($data['subject']);
				$contact->setMessage($data['message']);
				$contact->setEmail($data['email']);
				if ($app['currentUser']) {
					$contact->setUserAccountId($app['currentUser']->getId());
				}
				$contact->setIp($_SERVER['REMOTE_ADDR']);
				$contact->setBrowser($_SERVER['HTTP_USER_AGENT']);			
				if ($request->request->get('url')) {
					$contact->setIsSpamHoneypotFieldDetected(true);
				}

				$contactSupportRepository = new ContactSupportRepository();
				$contactSupportRepository->create($contact);

				if (!$contact->getIsSpam()) {
					$contact->sendEmailToSupport($app, $app['currentUser']);
				}

				$app['flashmessages']->addMessage('Your message has been sent');
				return $app->redirect('/contact');		
			}
		}
		
		return $app['twig']->render('index/index/contact.html.twig', array(
				'form'=>$form->createView(),
			));
		
	}



	
}


