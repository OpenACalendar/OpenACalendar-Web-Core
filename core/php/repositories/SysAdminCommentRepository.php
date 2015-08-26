<?php


namespace repositories;

use Exception;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SysAdminCommentRepository {

	public function createAboutUser(UserAccountModel $aboutUser, $comment, UserAccountModel $author = null) {

		global $DB;
		$createdat = \TimeSource::getFormattedForDataBase();


		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("INSERT INTO sysadmin_comment_information (user_account_id, comment, created_at ) ".
				"VALUES (:user_account_id, :comment, :created_at ) RETURNING id");
			$stat->execute(array(
				'user_account_id'=>($author ? $author->getId() : null),
				'comment'=> $comment,
				'created_at'=>  $createdat,
			));
			$data = $stat->fetch();
			$id = $data['id'];


			$stat = $DB->prepare("INSERT INTO sysadmin_comment_about_user (user_account_id, sysadmin_comment_id ) ".
				"VALUES (:user_account_id, :sysadmin_comment_id)");
			$stat->execute(array(
				'user_account_id'=>$aboutUser->getId(),
				'sysadmin_comment_id'=> $id,
			));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}

	}

}

