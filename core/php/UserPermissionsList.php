<?php


/**
 *
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserPermissionsList {


	protected $permissions;

	protected $removeEditorPermissions;

	protected $has_user = false;
	protected $has_user_verified = false;
	protected $has_user_editor = false;
	protected $has_user_system_administrator = false;

	function __construct(ExtensionManager $extensionManager, $permissions, \models\UserAccountModel $userAccountModel = null, $removeEditorPermissions = false, $includeChildrenPermissions = false)
	{
		if ($userAccountModel) {
			$this->has_user = true;
			$this->has_user_editor = $userAccountModel->getIsEditor();
			$this->has_user_verified = $userAccountModel->getIsEmailVerified();
			$this->has_user_system_administrator = $userAccountModel->getIsSystemAdmin();
		}
		$this->removeEditorPermissions = $removeEditorPermissions;
		$this->permissions = array();
		// Add direct permissions, checking user stats as we do so.
		foreach($permissions as $permission) {
			$this->addPermission($permission);
		}
		// now add children
		if ($includeChildrenPermissions) {
			do {
				$addedAny = false;
				foreach($extensionManager->getExtensionsIncludingCore() as $extension) {
					foreach($extension->getUserPermissions() as $possibleChildID) {
						$possibleChildPermission = $extension->getUserPermission($possibleChildID);
						$addThisOne = false;
						foreach($possibleChildPermission->getParentPermissionsIDs() as $parentData) {
							if (!$addThisOne && $this->hasPermission($parentData[0],$parentData[1])) {
								$addThisOne = true;
							}
						}
						if ($addThisOne) {
							$this->addPermission($possibleChildPermission);
						}
					}
				}

			} while ($addedAny);
		}
	}

	protected function addPermission(BaseUserPermission $permission = null) {
		// The permission could be from a extension that has now been removed
		if (!$permission) return;

		foreach($this->permissions as $existingPermission) {
			if ($existingPermission->getUserPermissionExtensionID() == $permission->getUserPermissionExtensionID() &&
				$existingPermission->getUserPermissionKey() == $permission->getUserPermissionKey()) {
				return true;
			}
		}
		$add = true;
		if ($permission->requiresUser() && !$this->has_user) {
			$add = false;
		} else if ($permission->requiresVerifiedUser() && !$this->has_user_verified) {
			$add = false;
		} else if ($permission->requiresEditorUser() && (!$this->has_user_editor || $this->removeEditorPermissions)) {
			$add = false;
		}
		if ($add) {
			$this->permissions[] = $permission;
		}
	}

	function hasPermission($extId, $key) {
		foreach($this->permissions as $permission) {
			if ($permission->getUserPermissionExtensionID() == $extId && $permission->getUserPermissionKey() == $key) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}

}

