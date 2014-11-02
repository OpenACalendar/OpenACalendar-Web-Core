<?php



namespace repositories;

use dbaccess\UserGroupDBAccess;
use models\SiteModel;
use models\UserAccountModel;
use models\UserGroupModel;
use Symfony\Component\Config\Definition\Exception\Exception;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


class UserHasNoEditorPermissionsInSiteRepository {


	public function addUserToSite(UserAccountModel $userAccountModel, SiteModel $siteModel, UserAccountModel $currentUser = null) {
		global $DB;

		$inTransaction = $DB->inTransaction();

		$statInsertUserInUserGroup = $DB->prepare("INSERT INTO user_has_no_editor_permissions_in_site (user_account_id, site_id, added_at, added_by_user_account_id) ".
			"VALUES (:user_account_id, :site_id, :added_at, :added_by_user_account_id)");

		try {
			if (!$inTransaction) $DB->beginTransaction();

			// TODO check already in

			$statInsertUserInUserGroup->execute(array(
						"site_id"=>$siteModel->getId(),
						"user_account_id"=>$userAccountModel->getId(),
						"added_at"=>\TimeSource::getFormattedForDataBase(),
						"added_by_user_account_id"=>($currentUser ? $currentUser->getId() : null),
				));


			if (!$inTransaction) $DB->commit();
		} catch (Exception $e) {
			if (!$inTransaction) $DB->rollBack();
		}

	}

	public function removeUserFromSite(UserAccountModel $userAccountModel, SiteModel $siteModel, UserAccountModel $currentUser = null) {
		global $DB;

		$stat = $DB->prepare("UPDATE user_has_no_editor_permissions_in_site SET removed_at=:removed_at, removed_by_user_account_id=:removed_by_user_account_id WHERE ".
			"site_id=:site_id AND user_account_id=:user_account_id AND removed_at IS NULL");

		$stat->execute(array(
			"site_id"=>$siteModel->getId(),
			"user_account_id"=>$userAccountModel->getId(),
			"removed_at"=>\TimeSource::getFormattedForDataBase(),
			"removed_by_user_account_id"=>($currentUser ? $currentUser->getId() : null),
		));

	}

	public function isUserInSite(UserAccountModel $userAccountModel, SiteModel $siteModel) {
		global $DB;
		$stat = $DB->prepare("SELECT * FROM user_has_no_editor_permissions_in_site WHERE site_id=:site_id AND user_account_id=:user_account_id AND removed_at IS NULL");
		$stat->execute(array(
			"site_id"=>$siteModel->getId(),
			"user_account_id"=>$userAccountModel->getId(),
		));
		return $stat->rowCount() > 0;
	}



}
