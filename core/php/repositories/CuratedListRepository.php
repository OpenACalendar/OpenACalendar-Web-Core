<?php


namespace repositories;

use models\CuratedListModel;
use models\SiteModel;
use models\UserAccountModel;
use models\EventModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListRepository {
	
	
	
	public function create(CuratedListModel $curatedList, SiteModel $site, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM curated_list_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$curatedList->setSlug($data['c'] + 1);
			
			$stat = $DB->prepare("INSERT INTO curated_list_information (site_id, slug, title,description,created_at) ".
					"VALUES (:site_id, :slug, :title,:description, :created_at) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$curatedList->getSlug(),
					'title'=>substr($curatedList->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$curatedList->getDescription(),
					'created_at'=>\TimeSource::getFormattedForDataBase()
				));
			$data = $stat->fetch();
			$curatedList->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO curated_list_history (curated_list_id, title, description, user_account_id  , created_at) VALUES ".
					"(:curated_list_id, :title, :description, :user_account_id  , :created_at)");
			$stat->execute(array(
					'curated_list_id'=>$curatedList->getId(),
					'title'=>substr($curatedList->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$curatedList->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			$stat = $DB->prepare("INSERT INTO user_in_curated_list_information (user_account_id,curated_list_id,is_owner,created_at) ".
					" VALUES (:user_account_id,:curated_list_id,:is_owner,:created_at) ");
			$stat->execute(array(
					'user_account_id'=>$creator->getId(),
					'curated_list_id'=>$curatedList->getId(),
					'is_owner'=>'1',
					'created_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT curated_list_information.* FROM curated_list_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$clist = new CuratedListModel();
			$clist->setFromDataBaseRow($stat->fetch());
			return $clist;
		}
	}
	
	
	public function edit(CuratedListModel $curatedList, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE curated_list_information  SET title=:title, description=:description WHERE id=:id");
			$stat->execute(array(
					'id'=>$curatedList->getId(),
					'title'=>substr($curatedList->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$curatedList->getDescription(),
				));
			
			$stat = $DB->prepare("INSERT INTO curated_list_history (curated_list_id, title, description, user_account_id  , created_at) VALUES ".
					"(:curated_list_id, :title, :description, :user_account_id  , :created_at)");
			$stat->execute(array(
					'curated_list_id'=>$curatedList->getId(),
					'title'=>substr($curatedList->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$curatedList->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function addEditorToCuratedList(UserAccountModel $user, CuratedListModel $curatedList, UserAccountModel $addedBy) {
		global $DB;
		
		$stat = $DB->prepare("SELECT * FROM user_in_curated_list_information WHERE user_account_id=:uid AND curated_list_id=:clid ");;
		$stat->execute(array('uid'=>$user->getId(), 'clid'=>$curatedList->getId()));
		if ($stat->rowCount() == 0){
			$stat = $DB->prepare("INSERT INTO user_in_curated_list_information (user_account_id,curated_list_id,is_owner,is_editor,created_at) ".
					"VALUES (:user_account_id,:curated_list_id,'0','1',:created_at)");
			$stat->execute(array(
				'user_account_id'=>$user->getId(),
				'curated_list_id'=>$curatedList->getId(),
				'created_at'=>\TimeSource::getFormattedForDataBase(),
			));
		} else {
			$stat = $DB->prepare("UPDATE user_in_curated_list_information SET is_editor='1' WHERE ".
					" user_account_id=:user_account_id AND curated_list_id=:curated_list_id AND is_owner = '0' ");
			$stat->execute(array(
				'user_account_id'=>$user->getId(),
				'curated_list_id'=>$curatedList->getId(),
			));
		}
	}
	
	public function canUserEditCuratedList(UserAccountModel $user, CuratedListModel $curatedList) {
		global $DB;
		$stat = $DB->prepare("SELECT * FROM user_in_curated_list_information WHERE user_account_id=:uid AND curated_list_id=:clid".
				" AND (is_editor = '1' OR is_owner = '1')");;
		$stat->execute(array('uid'=>$user->getId(), 'clid'=>$curatedList->getId()));
		return ($stat->rowCount() > 0);
	}
	
	public function removeEditorFromCuratedList(UserAccountModel $user, CuratedListModel $curatedList, UserAccountModel $removedBy) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_in_curated_list_information SET is_editor='0' WHERE ".
				" user_account_id=:user_account_id AND curated_list_id=:curated_list_id AND is_owner = '0' ");
		$stat->execute(array(
				'user_account_id'=>$user->getId(),
				'curated_list_id'=>$curatedList->getId(),
			));
	}

	public function addEventtoCuratedList(EventModel $event, CuratedListModel $curatedList, UserAccountModel $user) {
		global $DB;
		
		// check event not already in list
		$stat = $DB->prepare("SELECT * FROM event_in_curated_list WHERE curated_list_id=:curated_list_id AND ".
				" event_id=:event_id AND removed_at IS NULL ");
		$stat->execute(array(
			'curated_list_id'=>$curatedList->getId(),
			'event_id'=>$event->getId(),
		));
		if ($stat->rowCount() > 0) {
			return;
		}
		
		// Add!
		$stat = $DB->prepare("INSERT INTO event_in_curated_list (curated_list_id,event_id,added_by_user_account_id,added_at) ".
				"VALUES (:curated_list_id,:event_id,:added_by_user_account_id,:added_at)");
		$stat->execute(array(
			'curated_list_id'=>$curatedList->getId(),
			'event_id'=>$event->getId(),
			'added_by_user_account_id'=>$user->getId(),
			'added_at'=>  \TimeSource::getFormattedForDataBase(),
		));
		
	}


	public function removeEventFromCuratedList(EventModel $event, CuratedListModel $curatedList, UserAccountModel $user) {
		global $DB;
		$stat = $DB->prepare("UPDATE event_in_curated_list SET removed_by_user_account_id=:removed_by_user_account_id,".
				" removed_at=:removed_at WHERE ".
				" event_id=:event_id AND curated_list_id=:curated_list_id AND removed_at IS NULL ");
		$stat->execute(array(
				'event_id'=>$event->getId(),
				'curated_list_id'=>$curatedList->getId(),
				'removed_at'=>  \TimeSource::getFormattedForDataBase(),
				'removed_by_user_account_id'=>$user->getId(),
			));
	}

}

