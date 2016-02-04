<?php



namespace repositories;

use dbaccess\UserGroupDBAccess;
use models\SiteModel;
use models\UserAccountModel;
use models\UserGroupModel;
use Silex\Application;
use Symfony\Component\Config\Definition\Exception\Exception;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */


class UserHasNoEditorPermissionsInSiteRepository {


    /** @var Application */
    private  $app;


    function __construct(Application $app)
    {
        $this->app = $app;
    }

	public function addUserToSite(UserAccountModel $userAccountModel, SiteModel $siteModel, UserAccountModel $currentUser = null) {


		$inTransaction = $this->app['db']->inTransaction();

		$statInsertUserInUserGroup = $this->app['db']->prepare("INSERT INTO user_has_no_editor_permissions_in_site (user_account_id, site_id, added_at, added_by_user_account_id) ".
			"VALUES (:user_account_id, :site_id, :added_at, :added_by_user_account_id)");

		try {
			if (!$inTransaction) $this->app['db']->beginTransaction();

			// TODO check already in

			$statInsertUserInUserGroup->execute(array(
						"site_id"=>$siteModel->getId(),
						"user_account_id"=>$userAccountModel->getId(),
						"added_at"=>$this->app['timesource']->getFormattedForDataBase(),
						"added_by_user_account_id"=>($currentUser ? $currentUser->getId() : null),
				));


			if (!$inTransaction) $this->app['db']->commit();
		} catch (Exception $e) {
			if (!$inTransaction) $this->app['db']->rollBack();
		}

	}

	public function removeUserFromSite(UserAccountModel $userAccountModel, SiteModel $siteModel, UserAccountModel $currentUser = null) {


		$stat = $this->app['db']->prepare("UPDATE user_has_no_editor_permissions_in_site SET removed_at=:removed_at, removed_by_user_account_id=:removed_by_user_account_id WHERE ".
			"site_id=:site_id AND user_account_id=:user_account_id AND removed_at IS NULL");

		$stat->execute(array(
			"site_id"=>$siteModel->getId(),
			"user_account_id"=>$userAccountModel->getId(),
			"removed_at"=>$this->app['timesource']->getFormattedForDataBase(),
			"removed_by_user_account_id"=>($currentUser ? $currentUser->getId() : null),
		));

	}

	public function isUserInSite(UserAccountModel $userAccountModel, SiteModel $siteModel) {

		$stat = $this->app['db']->prepare("SELECT * FROM user_has_no_editor_permissions_in_site WHERE site_id=:site_id AND user_account_id=:user_account_id AND removed_at IS NULL");
		$stat->execute(array(
			"site_id"=>$siteModel->getId(),
			"user_account_id"=>$userAccountModel->getId(),
		));
		return $stat->rowCount() > 0;
	}



}
