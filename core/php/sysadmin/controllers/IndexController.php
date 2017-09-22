<?php

namespace sysadmin\controllers;

use repositories\UserAccountRepository;
use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use repositories\EventRepository;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IndexController {
	
	
	function index(Request $request, Application $app) {

        if ($request->query->get('findUserByEmail')) {
            $userRepo = new UserAccountRepository($app);
            $user = $userRepo->loadByEmail($request->query->get('findUserByEmail'));
            if ($user) {
                return $app->redirect('/sysadmin/user/'. $user->getId());
            } else {
                $app['flashmessages']->addError("Can't find any user for that email.");
            }
        }

        if ($request->query->get('findUserByUserName')) {
            $userRepo = new UserAccountRepository($app);
            $user = $userRepo->loadByUserName($request->query->get('findUserByUserName'));
            if ($user) {
                return $app->redirect('/sysadmin/user/'. $user->getId());
            } else {
                $app['flashmessages']->addError("Can't find any user for that user name.");
            }
        }

		return $app['twig']->render('sysadmin/index/index.html.twig', array(
				'extensions'=>$app['extensions']->getExtensions(),
			));
		
	}
	
}


