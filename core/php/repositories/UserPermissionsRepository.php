<?php

namespace repositories;

use models\SiteModel;
use models\UserAccountModel;
use models\UserGroupModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UserPermissionsRepository {


	/** @var  \ExtensionManager */
	protected $extensionsManager;

	function __construct($extensionsManager)
	{
		$this->extensionsManager = $extensionsManager;
	}


	public function getPermissionsForUserGroup(UserGroupModel $userGroupModel, $includeChildrenPermissions = false) {
		global $DB, $app;

		$stat = $DB->prepare("SELECT permission_in_user_group.* FROM permission_in_user_group ".
			"WHERE permission_in_user_group.user_group_id = :user_group_id AND permission_in_user_group.removed_at IS NULL");
		$stat->execute(array(
			'user_group_id'=>$userGroupModel->getId(),
		));
		$permissions = array();
		// base permissions
		while($data = $stat->fetch()) {
			$ext = $app['extensions']->getExtensionById($data['extension_id']);
			if ($ext) {
				$permissions[] = $ext->getUserPermission($data['permission_key']);
			}
		}
		// child permissions
		if ($includeChildrenPermissions) {
			// TODO
		}
		return $permissions;
	}


	public function getPermissionsForUserInIndex(UserAccountModel $userAccountModel = null,  $removeEditorPermissions = false, $includeChildrenPermissions = false) {
		global $DB, $CONFIG;

		if ($userAccountModel) {

			$stat = $DB->prepare("SELECT permission_in_user_group.* FROM permission_in_user_group ".
				" JOIN user_group_information ON user_group_information.id = permission_in_user_group.user_group_id AND user_group_information.is_deleted = '0' AND user_group_information.is_in_index = '1' ".
				" LEFT JOIN user_in_user_group ON user_in_user_group.user_group_id = user_group_information.id AND user_in_user_group.removed_at IS NULL ".
				" WHERE permission_in_user_group.removed_at IS NULL AND ".
				" ( user_in_user_group.user_account_id = :user_account_id OR  user_group_information.is_includes_users = '1' ".($userAccountModel->getIsEmailVerified() ? " OR user_group_information.is_includes_verified_users = '1'  " : "")." ) ");
			$stat->execute(array(
				'user_account_id'=>$userAccountModel->getId(),
			));

		} else {


			$stat = $DB->prepare("SELECT permission_in_user_group.* FROM permission_in_user_group ".
				" JOIN user_group_information ON user_group_information.id = permission_in_user_group.user_group_id AND user_group_information.is_deleted = '0' AND user_group_information.is_in_index = '1' ".
				" WHERE permission_in_user_group.removed_at IS NULL AND user_group_information.is_includes_anonymous = '1' ");
			$stat->execute(array());

		}

		$permissions = array();
		while($data = $stat->fetch()) {
			$ext = $this->extensionsManager->getExtensionById($data['extension_id']);
			if ($ext) {
				$permissions[] = $ext->getUserPermission($data['permission_key']);
			}
		}
		return new \UserPermissionsList($this->extensionsManager, $permissions, $userAccountModel, $CONFIG->siteReadOnly || $removeEditorPermissions, $includeChildrenPermissions);
	}

	public function getPermissionsForUserInSite(UserAccountModel $userAccountModel = null, SiteModel $siteModel,  $removeEditorPermissions = false, $includeChildrenPermissions = false) {
		global $DB, $CONFIG;

		if ($userAccountModel) {

			$stat = $DB->prepare("SELECT permission_in_user_group.* FROM permission_in_user_group ".
				" JOIN user_group_information ON user_group_information.id = permission_in_user_group.user_group_id AND user_group_information.is_deleted = '0' AND user_group_information.is_in_index = '0' ".
				" JOIN user_group_in_site ON user_group_in_site.user_group_id = user_group_information.id AND user_group_in_site.site_id = :site_id AND user_group_in_site.removed_at IS NULL ".
				" LEFT JOIN user_in_user_group ON user_in_user_group.user_group_id = user_group_information.id AND user_in_user_group.removed_at IS NULL ".
				" WHERE permission_in_user_group.removed_at IS NULL AND ".
				" ( user_in_user_group.user_account_id = :user_account_id OR user_group_information.is_includes_anonymous = '1' OR user_group_information.is_includes_users = '1' ".($userAccountModel->getIsEmailVerified() ? " OR user_group_information.is_includes_verified_users = '1'  " : "")." ) ");
			$stat->execute(array(
				'user_account_id'=>$userAccountModel->getId(),
				'site_id'=>$siteModel->getId(),
			));

		} else {


			$stat = $DB->prepare("SELECT permission_in_user_group.* FROM permission_in_user_group ".
				" JOIN user_group_information ON user_group_information.id = permission_in_user_group.user_group_id AND user_group_information.is_deleted = '0' AND user_group_information.is_in_index = '0' ".
				" JOIN user_group_in_site ON user_group_in_site.user_group_id = user_group_information.id AND user_group_in_site.site_id = :site_id AND user_group_in_site.removed_at IS NULL ".
				" WHERE permission_in_user_group.removed_at IS NULL AND user_group_information.is_includes_anonymous = '1' ");
			$stat->execute(array(
				'site_id'=>$siteModel->getId(),
			));

		}

		$permissions = array();
		while($data = $stat->fetch()) {
			$ext = $this->extensionsManager->getExtensionById($data['extension_id']);
			if ($ext) {
				$permissions[] = $ext->getUserPermission($data['permission_key']);
			}
		}
		return new \UserPermissionsList($this->extensionsManager, $permissions, $userAccountModel, $CONFIG->siteReadOnly || $removeEditorPermissions, $includeChildrenPermissions);
	}

	public function getPermissionsForAnonymousInSite(SiteModel $siteModel,  $removeEditorPermissions = false, $includeChildrenPermissions = false) {
		global $DB, $CONFIG;

		$stat = $DB->prepare("SELECT permission_in_user_group.* FROM permission_in_user_group ".
			" JOIN user_group_information ON user_group_information.id = permission_in_user_group.user_group_id AND user_group_information.is_deleted = '0' AND user_group_information.is_in_index = '0' ".
			" JOIN user_group_in_site ON user_group_in_site.user_group_id = user_group_information.id AND user_group_in_site.site_id = :site_id AND user_group_in_site.removed_at IS NULL ".
			" WHERE permission_in_user_group.removed_at IS NULL AND user_group_information.is_includes_anonymous = '1' ");
		$stat->execute(array(
			'site_id'=>$siteModel->getId(),
		));

		$permissions = array();
		while($data = $stat->fetch()) {
			$ext = $this->extensionsManager->getExtensionById($data['extension_id']);
			if ($ext) {
				$permissions[] = $ext->getUserPermission($data['permission_key']);
			}
		}
		return new \UserPermissionsList($this->extensionsManager, $permissions, null, $CONFIG->siteReadOnly || $removeEditorPermissions, $includeChildrenPermissions);
	}

	public function getPermissionsForAnyUserInSite(SiteModel $siteModel,  $removeEditorPermissions = false, $includeChildrenPermissions = false) {
		global $DB, $CONFIG;

		$stat = $DB->prepare("SELECT permission_in_user_group.* FROM permission_in_user_group ".
			" JOIN user_group_information ON user_group_information.id = permission_in_user_group.user_group_id AND user_group_information.is_deleted = '0' AND user_group_information.is_in_index = '0' ".
			" JOIN user_group_in_site ON user_group_in_site.user_group_id = user_group_information.id AND user_group_in_site.site_id = :site_id AND user_group_in_site.removed_at IS NULL ".
			" WHERE permission_in_user_group.removed_at IS NULL AND (user_group_information.is_includes_users = '1' OR user_group_information.is_includes_anonymous = '1' )");
		$stat->execute(array(
			'site_id'=>$siteModel->getId(),
		));

		$permissions = array();
		while($data = $stat->fetch()) {
			$ext = $this->extensionsManager->getExtensionById($data['extension_id']);
			if ($ext) {
				$permissions[] = $ext->getUserPermission($data['permission_key']);
			}
		}
		$user = new UserAccountModel();
		$user->setIsEditor(true);
		return new \UserPermissionsList($this->extensionsManager, $permissions, $user, $CONFIG->siteReadOnly || $removeEditorPermissions, $includeChildrenPermissions);
	}

	public function getPermissionsForAnyVerifiedUserInSite(SiteModel $siteModel,  $removeEditorPermissions = false, $includeChildrenPermissions = false) {
		global $DB, $CONFIG;

		$stat = $DB->prepare("SELECT permission_in_user_group.* FROM permission_in_user_group ".
			" JOIN user_group_information ON user_group_information.id = permission_in_user_group.user_group_id AND user_group_information.is_deleted = '0' AND user_group_information.is_in_index = '0' ".
			" JOIN user_group_in_site ON user_group_in_site.user_group_id = user_group_information.id AND user_group_in_site.site_id = :site_id AND user_group_in_site.removed_at IS NULL ".
			" WHERE permission_in_user_group.removed_at IS NULL AND (user_group_information.is_includes_verified_users = '1' OR user_group_information.is_includes_users = '1' OR user_group_information.is_includes_anonymous = '1' )");
		$stat->execute(array(
			'site_id'=>$siteModel->getId(),
		));

		$permissions = array();
		while($data = $stat->fetch()) {
			$ext = $this->extensionsManager->getExtensionById($data['extension_id']);
			if ($ext) {
				$permissions[] = $ext->getUserPermission($data['permission_key']);
			}
		}
		$user = new UserAccountModel();
		$user->setIsEditor(true);
		return new \UserPermissionsList($this->extensionsManager, $permissions, $user, $CONFIG->siteReadOnly || $removeEditorPermissions, $includeChildrenPermissions);
	}


}

