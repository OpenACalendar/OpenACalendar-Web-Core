<?php


namespace dbaccess;

use models\ImportEditMetaDataModel;
use models\UserAccountModel;
use models\ImportModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ImportDBAccess {

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


	protected $possibleFields = array('country_id','area_id','title','is_enabled','expired_at','group_id','is_manual_events_creation');

	public function update(ImportModel $import, $fields, ImportEditMetaDataModel $importEditMetaDataModel ) {
		$alreadyInTransaction = $this->db->inTransaction();

		// Make Information Data
		$fieldsSQL1 = array();
		$fieldsParams1 = array( 'id'=>$import->getId() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'title') {
				$fieldsParams1['title'] = substr($import->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'area_id') {
				$fieldsParams1['area_id'] = $import->getAreaId();
			} else if ($field == 'is_enabled') {
				$fieldsParams1['is_enabled'] = $import->getIsEnabled() ? 1 : 0;
			} else if ($field == 'is_manual_events_creation') {
				$fieldsParams1['is_manual_events_creation'] = $import->getIsManualEventsCreation() ? 1 : 0;
			} else if ($field == 'country_id') {
				$fieldsParams1['country_id'] = $import->getCountryId();
			} else if ($field == 'expired_at') {
				$fieldsParams1['expired_at'] = $import->getExpiredAt() ? $import->getExpiredAt()->format("Y-m-d H:i:s") : null;
			} else if ($field == 'group_id') {
				$fieldsParams1['group_id'] = ($import->getGroupId());
			}
		}

		// Make History Data
		$fieldsSQL2 = array('import_url_id','user_account_id','created_at','approved_at','from_ip');
		$fieldsSQLParams2 = array(':import_url_id',':user_account_id',':created_at',':approved_at',':from_ip');
		$fieldsParams2 = array(
			'import_url_id'=>$import->getId(),
			'user_account_id'=>($importEditMetaDataModel->getUserAccount() ? $importEditMetaDataModel->getUserAccount()->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
			'approved_at'=>$this->timesource->getFormattedForDataBase(),
			'from_ip'=>$importEditMetaDataModel->getIp(),
		);
		if ($importEditMetaDataModel->getEditComment()) {
			$fieldsSQL2[] = ' edit_comment ';
			$fieldsSQLParams2[] = ' :edit_comment ';
			$fieldsParams2['edit_comment'] = $importEditMetaDataModel->getEditComment();
		}
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields) || $field == 'title') {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'title') {
					$fieldsParams2['title'] = substr($import->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'area_id') {
					$fieldsParams2['area_id'] = $import->getAreaId();
				} else if ($field == 'country_id') {
					$fieldsParams2['country_id'] = $import->getCountryId();
				} else if ($field == 'group_id') {
					$fieldsParams2['group_id'] = $import->getGroupId();
				} else if ($field == 'expired_at') {
					$fieldsParams2['expired_at'] = $import->getExpiredAt() ? $import->getExpiredAt()->format("Y-m-d H:i:s") : null;
				} else if ($field == 'is_enabled') {
					$fieldsParams2['is_enabled'] = ($import->getIsEnabled()?1:0);
				} else if ($field == 'is_manual_events_creation') {
					$fieldsParams2['is_manual_events_creation'] = ($import->getIsManualEventsCreation()?1:0);
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
			$stat = $this->db->prepare("UPDATE import_url_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO import_url_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
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
