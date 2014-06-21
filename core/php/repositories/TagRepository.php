<?php


namespace repositories;

use models\TagModel;
use models\SiteModel;
use models\EventModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagRepository {
	
	
	public function create(TagModel $tag, SiteModel $site, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM tag_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$tag->setSlug($data['c'] + 1);
			
			$stat = $DB->prepare("INSERT INTO tag_information (site_id, slug, title,description,created_at,approved_at) ".
					"VALUES (:site_id, :slug, :title, :description, :created_at,:approved_at) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$tag->getSlug(),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$tag->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO tag_history (tag_id, title, description, user_account_id  , created_at, approved_at, is_new) VALUES ".
					"(:tag_id, :title, :description, :user_account_id  , :created_at, :approved_at, '1')");
			$stat->execute(array(
					'tag_id'=>$tag->getId(),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
						
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT tag_information.* FROM tag_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$tag = new TagModel();
			$tag->setFromDataBaseRow($stat->fetch());
			return $tag;
		}
	}
	
	
	
	public function loadById($id) {
		global $DB;
		$stat = $DB->prepare("SELECT tag_information.* FROM tag_information WHERE id = :id");
		$stat->execute(array( 'id'=>$id ));
		if ($stat->rowCount() > 0) {
			$tag = new TagModel();
			$tag->setFromDataBaseRow($stat->fetch());
			return $tag;
		}
	}
	
	
	
	public function edit(TagModel $tag, UserAccountModel $creator) {
		global $DB;
		if ($tag->getIsDeleted()) {
			throw new \Exception("Can't edit deleted tag!");
		}
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE tag_information  SET title=:title, description=:description, is_deleted='0' WHERE id=:id");
			$stat->execute(array(
					'id'=>$tag->getId(),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
				));
			
			$stat = $DB->prepare("INSERT INTO tag_history (tag_id, title, description, user_account_id  , created_at, approved_at, is_deleted, is_new) VALUES ".
					"(:tag_id, :title, :description, :user_account_id  , :created_at, :approved_at, '0', '0')");
			$stat->execute(array(
					'tag_id'=>$tag->getId(),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function delete(TagModel $tag, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE tag_information  SET is_deleted='1' WHERE id=:id");
			$stat->execute(array(
					'id'=>$tag->getId(),
				));
			
			$stat = $DB->prepare("INSERT INTO tag_history (tag_id, title, description, user_account_id  , created_at, approved_at,is_deleted, is_new) VALUES ".
					"(:tag_id, :title, :description, :user_account_id  , :created_at, :approved_at, '1','0')");
			$stat->execute(array(
					'tag_id'=>$tag->getId(),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function addTagToEvent(TagModel $tag, EventModel $event, UserAccountModel $user=null) {
		global $DB;
		
		// check event not already in list
		$stat = $DB->prepare("SELECT * FROM event_has_tag WHERE tag_id=:tag_id AND ".
				" event_id=:event_id AND removed_at IS NULL ");
		$stat->execute(array(
			'tag_id'=>$tag->getId(),
			'event_id'=>$event->getId(),
		));
		if ($stat->rowCount() > 0) {
			return;
		}
			
		// Add!
		$stat = $DB->prepare("INSERT INTO event_has_tag (tag_id,event_id,added_by_user_account_id,added_at,addition_approved_at) ".
				"VALUES (:tag_id,:event_id,:added_by_user_account_id,:added_at,:addition_approved_at)");
		$stat->execute(array(
			'tag_id'=>$tag->getId(),
			'event_id'=>$event->getId(),
			'added_by_user_account_id'=>($user?$user->getId():null),
			'added_at'=>  \TimeSource::getFormattedForDataBase(),
			'addition_approved_at'=>  \TimeSource::getFormattedForDataBase(),
		));
		
	}

	
	
	
	public function removeTagFromEvent(TagModel $tag, EventModel $event, UserAccountModel $user=null) {
		global $DB;

		
		$stat = $DB->prepare("UPDATE event_has_tag SET removed_by_user_account_id=:removed_by_user_account_id,".
				" removed_at=:removed_at, removal_approved_at=:removal_approved_at WHERE ".
				" event_id=:event_id AND tag_id=:tag_id AND removed_at IS NULL ");
		$stat->execute(array(
				'event_id'=>$event->getId(),
				'tag_id'=>$tag->getId(),
				'removed_at'=>  \TimeSource::getFormattedForDataBase(),
				'removal_approved_at'=>  \TimeSource::getFormattedForDataBase(),
				'removed_by_user_account_id'=>($user?$user->getId():null),
		));
	}

	
}

