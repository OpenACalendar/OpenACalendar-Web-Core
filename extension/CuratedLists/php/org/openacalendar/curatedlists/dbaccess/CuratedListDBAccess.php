<?php


namespace org\openacalendar\curatedlists\dbaccess;

use models\UserAccountModel;
use org\openacalendar\curatedlists\models\CuratedListModel;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CuratedListDBAccess {

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

	protected $possibleFields = array('title','description','is_deleted');

	public function update(CuratedListModel $curatedList, $fields, UserAccountModel $user = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		// Make Information Data
		$fieldsSQL1 = array();
		$fieldsParams1 = array( 'id'=>$curatedList->getId() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'title') {
				$fieldsParams1['title'] = substr($curatedList->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'description') {
				$fieldsParams1['description'] = $curatedList->getDescription();
			} else if ($field == 'parent_area_id') {
				$fieldsParams1['parent_area_id'] = $curatedList->getParentAreaId();
			} else if ($field == 'is_deleted') {
				$fieldsParams1['is_deleted'] = ($curatedList->getIsDeleted()?1:0);
			}
		}

		// Make History Data
		$fieldsSQL2 = array('curated_list_id','user_account_id','created_at');
		$fieldsSQLParams2 = array(':curated_list_id',':user_account_id',':created_at');
		$fieldsParams2 = array(
			'curated_list_id'=>$curatedList->getId(),
			'user_account_id'=>($user ? $user->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
		);
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields) || $field == 'title') {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'title') {
					$fieldsParams2['title'] = substr($curatedList->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'description') {
					$fieldsParams2['description'] = $curatedList->getDescription();
				} else if ($field == 'is_deleted') {
					$fieldsParams2['is_deleted'] = ($curatedList->getIsDeleted()?1:0);
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
			$stat = $this->db->prepare("UPDATE curated_list_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO curated_list_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
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
