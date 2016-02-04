<?php

namespace site\controllers;

use models\UserGroupEditMetaDataModel;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\UserAccountRepository;
use repositories\UserGroupRepository;
use repositories\UserPermissionsRepository;
use Silex\Application;
use site\forms\UserGroupEditForm;
use Symfony\Component\HttpFoundation\Request;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminUserGroupController {


	protected $parameters = array();

	protected function build($id, Request $request, Application $app) {
		$this->parameters = array('currentUserWatchesGroup'=>false);



		$ugr = new UserGroupRepository($app);
		$this->parameters['usergroup'] = $ugr->loadByIdInSite($id, $app['currentSite']);
		if (!$this->parameters['usergroup']) {
			return false;
		}


		return true;
	}

	function show($id, Request $request, Application $app) {

		if (!$this->build($id, $request, $app)) {
			$app->abort(404, "User Group does not exist.");
		}

		$urb = new UserAccountRepositoryBuilder($app);
		$urb->setInUserGroup($this->parameters['usergroup']);
		$this->parameters['users'] = $urb->fetchAll();

		$r = new UserPermissionsRepository($app);
		$this->parameters['userpermissions'] = $r->getPermissionsForUserGroup($this->parameters['usergroup'], false);


		return $app['twig']->render('site/adminusergroup/show.html.twig', $this->parameters);
	}

	function permissions($id, Request $request, Application $app) {

		if (!$this->build($id, $request, $app)) {
			$app->abort(404, "User Group does not exist.");
		}

		if ($request->request->get('action') == "addpermission" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$extension = $app['extensions']->getExtensionById($request->request->get("extension"));
			if ($extension) {
				$permission = $extension->getUserPermission($request->request->get("permission"));
				if ($permission) {
					$ugr = new UserGroupRepository($app);
					$ugr->addPermissionToGroup($permission, $this->parameters['usergroup'], $app['currentUser']);
					return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/permissions');
				}
			}
		} else if ($request->request->get('action') == "removepermission" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$extension = $app['extensions']->getExtensionById($request->request->get("extension"));
			if ($extension) {
				$permission = $extension->getUserPermission($request->request->get("permission"));
				if ($permission) {
					$ugr = new UserGroupRepository($app);
					$ugr->removePermissionFromGroup($permission, $this->parameters['usergroup'], $app['currentUser']);
					return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/permissions');
				}
			}
		}

		$r = new UserPermissionsRepository($app);
		$this->parameters['userpermissions'] = $r->getPermissionsForUserGroup($this->parameters['usergroup'], false);

		$this->parameters['userpermissionstoadd'] = array();
		foreach($app['extensions']->getExtensionsIncludingCore() as $ext) {
			foreach($ext->getUserPermissions() as $key) {
				$per = $ext->getUserPermission($key);
				if ($per->isForSite() && !in_array($per, $this->parameters['userpermissions'])) {
					$this->parameters['userpermissionstoadd'][] = $per;
				}
			}
		}


		return $app['twig']->render('site/adminusergroup/permissions.html.twig', $this->parameters);
	}


	function users($id, Request $request, Application $app) {

		if (!$this->build($id, $request, $app)) {
			$app->abort(404, "User Group does not exist.");
		}


		if ($request->request->get('action') == "removeuser" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$ur = new UserAccountRepository($app);
			$user = $ur->loadById($request->request->get('id'));
			if ($user) {
				$ugr = new UserGroupRepository($app);
				$ugr->removeUserFromGroup($user, $this->parameters['usergroup'], $app['currentUser']);
				return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
			}
		} else if ($request->request->get('action') == "adduser" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$ur = new UserAccountRepository($app);
			$user = $ur->loadByUserName($request->request->get('username'));
			if ($user) {
				$ugr = new UserGroupRepository($app);
				$ugr->addUserToGroup($user, $this->parameters['usergroup'], $app['currentUser']);
				return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
			} else {
				$app['flashmessages']->addError("Could not find user");
			}
		} else if ($request->request->get('action') == "removeanonymous" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesAnonymous(false);
			$ugr = new UserGroupRepository($app);
			$ugr->editIsIncludesAnonymous($this->parameters['usergroup'], $app['currentUser']);
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
		} else if ($request->request->get('action') == "addanonymous" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesAnonymous(true);
			$ugr = new UserGroupRepository($app);
			$ugr->editIsIncludesAnonymous($this->parameters['usergroup'], $app['currentUser']);
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');

		} else if ($request->request->get('action') == "removeusers" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesUsers(false);
			$ugr = new UserGroupRepository($app);
			$ugr->editIsIncludesUser($this->parameters['usergroup'], $app['currentUser']);
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
		} else if ($request->request->get('action') == "addusers" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesUsers(true);
			$ugr = new UserGroupRepository($app);
			$ugr->editIsIncludesUser($this->parameters['usergroup'], $app['currentUser']);
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');

		} else if ($request->request->get('action') == "removeverifiedusers" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesVerifiedUsers(false);
			$ugr = new UserGroupRepository($app);
			$ugr->editIsIncludesVerifiedUser($this->parameters['usergroup'], $app['currentUser']);
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
		} else if ($request->request->get('action') == "addverifiedusers" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesVerifiedUsers(true);
			$ugr = new UserGroupRepository($app);
			$ugr->editIsIncludesVerifiedUser($this->parameters['usergroup'], $app['currentUser']);
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');

		}


		$urb = new UserAccountRepositoryBuilder($app);
		$urb->setInUserGroup($this->parameters['usergroup']);
		$this->parameters['users'] = $urb->fetchAll();

		$r = new UserPermissionsRepository($app);
		$this->parameters['userpermissions'] = $r->getPermissionsForUserGroup($this->parameters['usergroup'], false);


		return $app['twig']->render('site/adminusergroup/users.html.twig', $this->parameters);
	}


    function edit($id, Request $request, Application $app)
    {

        if (!$this->build($id, $request, $app)) {
            $app->abort(404, "User Group does not exist.");
        }

        $form = $app['form.factory']->create(new UserGroupEditForm($app), $this->parameters['usergroup']);

        if ('POST' == $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {

                $ugr = new UserGroupRepository($app);
                $meta = new UserGroupEditMetaDataModel();
                $meta->setUserAccount($app['currentUser']);
				$meta->setFromRequest($request);
                $ugr->editTitleWithMetaData($this->parameters['usergroup'], $meta);

                return $app->redirect("/admin/usergroup/".$this->parameters['usergroup']->getId());

            }
        }

        $this->parameters['form'] = $form->createView();
        return $app['twig']->render('site/adminusergroup/edit.html.twig', $this->parameters);


    }

}



