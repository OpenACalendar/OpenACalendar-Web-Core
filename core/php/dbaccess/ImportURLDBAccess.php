<?php


namespace dbaccess;

use models\UserAccountModel;
use models\ImportURLModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ImportURLDBAccess {

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


	public function update(ImportURLModel $importURL, $fields, UserAccountModel $user = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			$stat = $this->db->prepare("UPDATE import_url_information  SET title=:title, country_id=:country_id, area_id=:area_id, is_enabled=:is_enabled, expired_at=:expired_at WHERE id=:id");
			$stat->execute(array(
				'id'=>$importURL->getId(),
				'country_id'=>$importURL->getCountryId(),
				'area_id'=>$importURL->getAreaId(),
				'title'=>$importURL->getTitle(),
				'is_enabled'=>$importURL->getIsEnabled()?1:0,
				'expired_at'=>$importURL->getExpiredAt() ? $importURL->getExpiredAt()->format('Y-m-d H:i:s') : null,
			));

			$stat = $this->db->prepare("INSERT INTO import_url_history (import_url_id, title, user_account_id  , created_at,group_id, is_enabled, expired_at, country_id, area_id) VALUES ".
				"(:import_url_id, :title, :user_account_id  , :created_at, :group_id, :is_enabled, :expired_at, :country_id, :area_id)");
			$stat->execute(array(
				'import_url_id'=>$importURL->getId(),
				'title'=>substr($importURL->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'group_id'=>$importURL->getGroupId(),
				'country_id'=>$importURL->getCountryId(),
				'area_id'=>$importURL->getAreaId(),
				'user_account_id'=>$user->getId(),
				'is_enabled'=>$importURL->getIsEnabled()?1:0,
				'created_at'=>$this->timesource->getFormattedForDataBase(),
				'expired_at'=>$importURL->getExpiredAt() ? $importURL->getExpiredAt()->format('Y-m-d H:i:s') : null,
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
