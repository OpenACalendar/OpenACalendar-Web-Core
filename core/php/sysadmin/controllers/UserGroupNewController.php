<?php

namespace sysadmin\controllers;

use models\UserGroupEditMetaDataModel;
use models\UserGroupModel;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\UserPermissionsRepository;
use Silex\Application;
use site\forms\AdminUserGroupNewForm;
use Symfony\Component\HttpFoundation\Request;
use repositories\SiteRepository;
use repositories\UserGroupRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserGroupNewController {


	function index(Request $request, Application $app) {

		$userGroup = new UserGroupModel();

		$form = $app['form.factory']->create(AdminUserGroupNewForm::class, $userGroup);

		if ('POST' == $request->getMethod()) {
			$form->handleRequest($request);

			if ($form->isValid()) {

                $userGroupEditMetaDataModel = new UserGroupEditMetaDataModel();
                $userGroupEditMetaDataModel->setUserAccount($app['currentUser']);
                $userGroupEditMetaDataModel->setFromRequest($request);

				$ugRepository = new UserGroupRepository($app);
				$ugRepository->createForIndexWithMetaData($userGroup, $userGroupEditMetaDataModel);
				return $app->redirect("/sysadmin/usergroup/".$userGroup->getId());

			}
		}

		return $app['twig']->render('sysadmin/usergroupnew/index.html.twig', array(
			'form'=>$form->createView(),
		));

	}


}


