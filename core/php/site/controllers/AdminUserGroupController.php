<?php

namespace site\controllers;

use repositories\builders\UserAccountRepositoryBuilder;
use repositories\UserAccountRepository;
use repositories\UserGroupRepository;
use repositories\UserPermissionsRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminUserGroupController {


	protected $parameters = array();

	protected function build($id, Request $request, Application $app) {
		$this->parameters = array('currentUserWatchesGroup'=>false);



		$ugr = new UserGroupRepository();
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

		$urb = new UserAccountRepositoryBuilder();
		$urb->setInUserGroup($this->parameters['usergroup']);
		$this->parameters['users'] = $urb->fetchAll();

		$r = new UserPermissionsRepository($app['extensions']);
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
					$ugr = new UserGroupRepository();
					$ugr->addPermissionToGroup($permission, $this->parameters['usergroup'], userGetCurrent());
					return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/permissions');
				}
			}
		} else if ($request->request->get('action') == "removepermission" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$extension = $app['extensions']->getExtensionById($request->request->get("extension"));
			if ($extension) {
				$permission = $extension->getUserPermission($request->request->get("permission"));
				if ($permission) {
					$ugr = new UserGroupRepository();
					$ugr->removePermissionFromGroup($permission, $this->parameters['usergroup'], userGetCurrent());
					return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/permissions');
				}
			}
		}

		$r = new UserPermissionsRepository($app['extensions']);
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
			$ur = new UserAccountRepository();
			$user = $ur->loadById($request->request->get('id'));
			if ($user) {
				$ugr = new UserGroupRepository();
				$ugr->removeUserFromGroup($user, $this->parameters['usergroup'], userGetCurrent());
				return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
			}
		} else if ($request->request->get('action') == "adduser" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$ur = new UserAccountRepository();
			$user = $ur->loadByUserName($request->request->get('username'));
			if ($user) {
				$ugr = new UserGroupRepository();
				$ugr->addUserToGroup($user, $this->parameters['usergroup'], userGetCurrent());
				return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
			}
		} else if ($request->request->get('action') == "removeanonymous" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesAnonymous(false);
			$ugr = new UserGroupRepository();
			$ugr->editIsIncludesAnonymous($this->parameters['usergroup'], userGetCurrent());
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
		} else if ($request->request->get('action') == "addanonymous" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesAnonymous(true);
			$ugr = new UserGroupRepository();
			$ugr->editIsIncludesAnonymous($this->parameters['usergroup'], userGetCurrent());
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');

		} else if ($request->request->get('action') == "removeusers" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesUsers(false);
			$ugr = new UserGroupRepository();
			$ugr->editIsIncludesUser($this->parameters['usergroup'], userGetCurrent());
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
		} else if ($request->request->get('action') == "addusers" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesUsers(true);
			$ugr = new UserGroupRepository();
			$ugr->editIsIncludesUser($this->parameters['usergroup'], userGetCurrent());
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');

		} else if ($request->request->get('action') == "removeverifiedusers" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesVerifiedUsers(false);
			$ugr = new UserGroupRepository();
			$ugr->editIsIncludesVerifiedUser($this->parameters['usergroup'], userGetCurrent());
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');
		} else if ($request->request->get('action') == "addverifiedusers" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$this->parameters['usergroup']->setIsIncludesVerifiedUsers(true);
			$ugr = new UserGroupRepository();
			$ugr->editIsIncludesVerifiedUser($this->parameters['usergroup'], userGetCurrent());
			return $app->redirect('/admin/usergroup/'.$this->parameters['usergroup']->getId().'/users');

		}


		$urb = new UserAccountRepositoryBuilder();
		$urb->setInUserGroup($this->parameters['usergroup']);
		$this->parameters['users'] = $urb->fetchAll();

		$r = new UserPermissionsRepository($app['extensions']);
		$this->parameters['userpermissions'] = $r->getPermissionsForUserGroup($this->parameters['usergroup'], false);


		return $app['twig']->render('site/adminusergroup/users.html.twig', $this->parameters);
	}



}



