<?php

namespace site\controllers;

use models\GroupEditMetaDataModel;
use Silex\Application;
use site\forms\GroupNewForm;
use site\forms\GroupEditForm;
use site\forms\EventNewForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\GroupRepository;


/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class GroupNewController {
	
	
	function newGroup(Request $request, Application $app) {

		/////////////////////////////////////////////////////// Check Permissions and Prompt IF NEEDED

		if (!$app['currentUser'] && !$app['currentUserActions']->has("org.openacalendar","groupNew") &&  $app['anyVerifiedUserActions']->has("org.openacalendar","groupNew")) {
			return $app['twig']->render('site/groupnew/new.useraccountneeded.html.twig', array());
		}

		/////////////////////////////////////////////////////// Carry On



		$group = new GroupModel();
		
		$form = $app['form.factory']->create(new GroupNewForm($request->query->get('title')), $group);



		if ('POST' == $request->getMethod()) {
			$form->handleRequest($request);

			if ($form->isValid()) {

                $groupEditMetaDataModel = new GroupEditMetaDataModel();
                $groupEditMetaDataModel->setUserAccount($app['currentUser']);
                $groupEditMetaDataModel->setFromRequest($request);

                $groupRepository = new GroupRepository($app);
                $groupRepository->createWithMetaData($group, $app['currentSite'], $groupEditMetaDataModel);
				
				return $app->redirect("/group/".$group->getSlugForUrl());
				
			}
		}
		
		
		return $app['twig']->render('site/groupnew/new.html.twig', array(
				'form'=>$form->createView(),
			));
		
	}
	
}


