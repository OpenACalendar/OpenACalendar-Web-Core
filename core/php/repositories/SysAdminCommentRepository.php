<?php


namespace repositories;

use Exception;
use models\AreaModel;
use models\EventModel;
use models\GroupModel;
use models\MediaModel;
use models\SiteModel;
use models\UserAccountModel;
use models\VenueModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SysAdminCommentRepository {

	protected function createCommentBody($comment, UserAccountModel $author = null ) {
		global $DB;
		$stat = $DB->prepare("INSERT INTO sysadmin_comment_information (user_account_id, comment, created_at ) ".
			"VALUES (:user_account_id, :comment, :created_at ) RETURNING id");
		$stat->execute(array(
			'user_account_id'=>($author ? $author->getId() : null),
			'comment'=> $comment,
			'created_at'=>  \TimeSource::getFormattedForDataBase(),
		));
		$data = $stat->fetch();
		return $data['id'];
	}

	public function createAboutUser(UserAccountModel $aboutUser, $comment, UserAccountModel $author = null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$id = $this->createCommentBody($comment, $author);

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


	public function createAboutSite(SiteModel $aboutSite, $comment, UserAccountModel $author = null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$id = $this->createCommentBody($comment, $author);

			$stat = $DB->prepare("INSERT INTO sysadmin_comment_about_site (site_id, sysadmin_comment_id ) ".
				"VALUES (:site_id, :sysadmin_comment_id)");
			$stat->execute(array(
				'site_id'=>$aboutSite->getId(),
				'sysadmin_comment_id'=> $id,
			));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function createAboutEvent(EventModel $aboutEvent, $comment, UserAccountModel $author = null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$id = $this->createCommentBody($comment, $author);

			$stat = $DB->prepare("INSERT INTO sysadmin_comment_about_event (event_id, sysadmin_comment_id ) ".
				"VALUES (:event_id, :sysadmin_comment_id)");
			$stat->execute(array(
				'event_id'=>$aboutEvent->getId(),
				'sysadmin_comment_id'=> $id,
			));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	
	public function createAboutGroup(GroupModel $aboutGroup, $comment, UserAccountModel $author = null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$id = $this->createCommentBody($comment, $author);

			$stat = $DB->prepare("INSERT INTO sysadmin_comment_about_group (group_id, sysadmin_comment_id ) ".
				"VALUES (:group_id, :sysadmin_comment_id)");
			$stat->execute(array(
				'group_id'=>$aboutGroup->getId(),
				'sysadmin_comment_id'=> $id,
			));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	
	public function createAboutArea(AreaModel $aboutArea, $comment, UserAccountModel $author = null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$id = $this->createCommentBody($comment, $author);

			$stat = $DB->prepare("INSERT INTO sysadmin_comment_about_area (area_id, sysadmin_comment_id ) ".
				"VALUES (:area_id, :sysadmin_comment_id)");
			$stat->execute(array(
				'area_id'=>$aboutArea->getId(),
				'sysadmin_comment_id'=> $id,
			));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	
	public function createAboutVenue(VenueModel $aboutVenue, $comment, UserAccountModel $author = null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$id = $this->createCommentBody($comment, $author);

			$stat = $DB->prepare("INSERT INTO sysadmin_comment_about_venue (venue_id, sysadmin_comment_id ) ".
				"VALUES (:venue_id, :sysadmin_comment_id)");
			$stat->execute(array(
				'venue_id'=>$aboutVenue->getId(),
				'sysadmin_comment_id'=> $id,
			));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	
	public function createAboutMedia(MediaModel $aboutMedia, $comment, UserAccountModel $author = null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$id = $this->createCommentBody($comment, $author);

			$stat = $DB->prepare("INSERT INTO sysadmin_comment_about_media (media_id, sysadmin_comment_id ) ".
				"VALUES (:media_id, :sysadmin_comment_id)");
			$stat->execute(array(
				'media_id'=>$aboutMedia->getId(),
				'sysadmin_comment_id'=> $id,
			));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

}

