<?php


namespace dbaccess;

use models\UserAccountModel;
use models\GroupModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class GroupDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;

	/** @var \UserAgent */
	protected $useragent;

	function __construct($db, $timesource, $useragent)
	{
		$this->db = $db;
		$this->timesource = $timesource;
		$this->useragent = $useragent;
	}


	public function update(GroupModel $group, $fields, UserAccountModel $user = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			$stat = $this->db->prepare("UPDATE group_information  SET title=:title, url=:url, description=:description, twitter_username=:twitter_username, is_deleted=:is_deleted, is_duplicate_of_id=:is_duplicate_of_id WHERE id=:id");
			$stat->execute(array(
				'id'=>$group->getId(),
				'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
				'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
				'description'=>$group->getDescription(),
				'is_duplicate_of_id'=>$group->getIsDuplicateOfId(),
				'is_deleted'=>$group->getIsDeleted()?1:0,
			));

			$stat = $this->db->prepare("INSERT INTO group_history (group_id, title, url, description, user_account_id  , created_at,approved_at, twitter_username, is_duplicate_of_id, is_deleted) VALUES ".
				"(:group_id, :title, :url, :description,  :user_account_id  , :created_at, :approved_at, :twitter_username, :is_duplicate_of_id, :is_deleted)");
			$stat->execute(array(
				'group_id'=>$group->getId(),
				'title'=>substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'url'=>substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
				'description'=>$group->getDescription(),
				'user_account_id'=>($user ? $user->getId() : null),
				'twitter_username'=>substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
				'is_duplicate_of_id'=>$group->getIsDuplicateOfId(),
				'created_at'=>$this->timesource->getFormattedForDataBase(),
				'approved_at'=>$this->timesource->getFormattedForDataBase(),
				'is_deleted'=>$group->getIsDeleted()?1:0,
			));


			if (!$alreadyInTransaction) {
				$this->db->commit();
			}
		} catch (Exception $e) {
			if (!$alreadyInTransaction) {
				$this->db->rollBack();
			}
			throw $e;
		}

	}


} 
