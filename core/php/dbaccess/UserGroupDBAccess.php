<?php


namespace dbaccess;

use models\UserAccountModel;
use models\UserGroupModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UserGroupDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;


	function __construct($db, $timesource)
	{
		$this->db = $db;
		$this->timesource = $timesource;
	}

	protected $possibleFields = array('title','description','is_deleted','is_in_index','is_includes_anonymous','is_includes_users','is_includes_verified_users');


	public function update(UserGroupModel $userGroup, $fields, UserAccountModel $user = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		// Make Information Data
		$fieldsSQL1 = array();
		$fieldsParams1 = array( 'id'=>$userGroup->getId() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'title') {
				$fieldsParams1['title'] = substr($userGroup->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'description') {
				$fieldsParams1['description'] = $userGroup->getDescription();
			} else if ($field == 'is_deleted') {
				$fieldsParams1['is_deleted'] = ($userGroup->getIsDeleted()?1:0);
			} else if ($field == 'is_in_index') {
				$fieldsParams1['is_in_index'] = ($userGroup->getIsDeleted()?1:0);
			} else if ($field == 'is_includes_anonymous') {
				$fieldsParams1['is_includes_anonymous'] = ($userGroup->getIsIncludesAnonymous()?1:0);
			} else if ($field == 'is_includes_users') {
				$fieldsParams1['is_includes_users'] = ($userGroup->getIsIncludesUsers()?1:0);
			} else if ($field == 'is_includes_verified_users') {
				$fieldsParams1['is_includes_verified_users'] = ($userGroup->getIsIncludesVerifiedUsers()?1:0);
			}
		}

		// Make History Data
		$fieldsSQL2 = array('user_group_id','user_account_id','created_at');
		$fieldsSQLParams2 = array(':user_group_id',':user_account_id',':created_at');
		$fieldsParams2 = array(
			'user_group_id'=>$userGroup->getId(),
			'user_account_id'=>($user ? $user->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
		);
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields)) {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'title') {
					$fieldsParams2['title'] = substr($userGroup->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'description') {
					$fieldsParams2['description'] = $userGroup->getDescription();
				} else if ($field == 'is_deleted') {
					$fieldsParams2['is_deleted'] = ($userGroup->getIsDeleted()?1:0);
				} else if ($field == 'is_in_index') {
					$fieldsParams2['is_in_index'] = ($userGroup->getIsDeleted()?1:0);
				} else if ($field == 'is_includes_anonymous') {
					$fieldsParams2['is_includes_anonymous'] = ($userGroup->getIsIncludesAnonymous()?1:0);
				} else if ($field == 'is_includes_users') {
					$fieldsParams2['is_includes_users'] = ($userGroup->getIsIncludesUsers()?1:0);
				} else if ($field == 'is_includes_verified_users') {
					$fieldsParams2['is_includes_verified_users'] = ($userGroup->getIsIncludesVerifiedUsers()?1:0);
				}
				$fieldsSQL2[] = " ".$field."_changed ";
				$fieldsSQLParams2[] = " 0 ";
			} else {
				$fieldsSQL2[] = " ".$field."_changed ";
				$fieldsSQLParams2[] = " -2 ";
			}
		}

		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			// Information SQL
			$stat = $this->db->prepare("UPDATE user_group_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO user_group_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
			$stat->execute($fieldsParams2);

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
