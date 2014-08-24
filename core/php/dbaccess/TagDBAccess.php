<?php


namespace dbaccess;

use models\UserAccountModel;
use models\TagModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class TagDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;


	function __construct($db, $timesource)
	{
		$this->db = $db;
		$this->timesource = $timesource;
	}


	public function update(TagModel $tag, $fields, UserAccountModel $user = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			$stat = $this->db->prepare("UPDATE tag_information  SET title=:title, description=:description, is_deleted=:is_deleted WHERE id=:id");
			$stat->execute(array(
				'id'=>$tag->getId(),
				'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'description'=>$tag->getDescription(),
				'is_deleted'=>$tag->getIsDeleted()?1:0,
			));

			$stat = $this->db->prepare("INSERT INTO tag_history (tag_id, title, description, user_account_id  , created_at, approved_at, is_deleted, is_new) VALUES ".
				"(:tag_id, :title, :description, :user_account_id  , :created_at, :approved_at, :is_deleted, '0')");
			$stat->execute(array(
				'tag_id'=>$tag->getId(),
				'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'description'=>$tag->getDescription(),
				'user_account_id'=>$user->getId(),
				'created_at'=>$this->timesource->getFormattedForDataBase(),
				'approved_at'=>$this->timesource->getFormattedForDataBase(),
				'is_deleted'=>$tag->getIsDeleted()?1:0,
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
