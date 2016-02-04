<?php


namespace dbaccess;

use models\GroupEditMetaDataModel;
use models\UserAccountModel;
use models\GroupModel;
use Silex\Application;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class GroupDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;

    function __construct(Application $application)
    {
        $this->db = $application['db'];
        $this->timesource = $application['timesource'];
    }

	protected $possibleFields = array('title','description','url','twitter_username','is_deleted','is_duplicate_of_id');


	public function update(GroupModel $group, $fields, GroupEditMetaDataModel $groupEditMetaDataModel) {
		$alreadyInTransaction = $this->db->inTransaction();


		// Make Information Data
		$fieldsSQL1 = array();
		$fieldsParams1 = array( 'id'=>$group->getId() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'title') {
				$fieldsParams1['title'] = substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'description') {
				$fieldsParams1['description'] = $group->getDescription();
			} else if ($field == 'url') {
				$fieldsParams1['url'] = substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'twitter_username') {
				$fieldsParams1['twitter_username'] = substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'is_duplicate_of_id') {
				$fieldsParams1['is_duplicate_of_id'] = $group->getIsDuplicateOfId();
			} else if ($field == 'is_deleted') {
				$fieldsParams1['is_deleted'] = ($group->getIsDeleted()?1:0);
			}
		}

		// Make History Data
		$fieldsSQL2 = array('group_id','user_account_id','created_at','approved_at','from_ip');
		$fieldsSQLParams2 = array(':group_id',':user_account_id',':created_at',':approved_at',':from_ip');
		$fieldsParams2 = array(
			'group_id'=>$group->getId(),
			'user_account_id'=>($groupEditMetaDataModel->getUserAccount() ? $groupEditMetaDataModel->getUserAccount()->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
			'approved_at'=>$this->timesource->getFormattedForDataBase(),
			'from_ip'=>$groupEditMetaDataModel->getIp(),
		);
		if ($groupEditMetaDataModel->getEditComment()) {
			$fieldsSQL2[] = ' edit_comment ';
			$fieldsSQLParams2[] = ' :edit_comment ';
			$fieldsParams2['edit_comment'] = $groupEditMetaDataModel->getEditComment();
		}
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields) || $field == 'title') {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'title') {
					$fieldsParams2['title'] = substr($group->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'description') {
					$fieldsParams2['description'] = $group->getDescription();
				} else if ($field == 'url') {
					$fieldsParams2['url'] = substr($group->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'twitter_username') {
					$fieldsParams2['twitter_username'] = substr($group->getTwitterUsername(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'is_duplicate_of_id') {
					$fieldsParams2['is_duplicate_of_id'] = $group->getIsDuplicateOfId();
				} else if ($field == 'is_deleted') {
					$fieldsParams2['is_deleted'] = ($group->getIsDeleted()?1:0);
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
			$stat = $this->db->prepare("UPDATE group_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO group_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
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
