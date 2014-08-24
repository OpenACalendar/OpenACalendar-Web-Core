<?php


namespace dbaccess;

use models\UserAccountModel;
use models\AreaModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class AreaDBAccess {

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


	public function update(AreaModel $area, $fields, UserAccountModel $user = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			$stat = $this->db->prepare("UPDATE area_information  SET ".
					"title=:title,description=:description,parent_area_id=:parent_area_id, is_deleted=:is_deleted, is_duplicate_of_id=:is_duplicate_of_id ".
					"WHERE id=:id");
			$stat->execute(array(
					'id'=>$area->getId(),
					'title'=>$area->getTitle(),
					'description'=>$area->getDescription(),
					'parent_area_id'=>$area->getParentAreaId(),
					'is_duplicate_of_id'=>$area->getIsDuplicateOfId(),
					'is_deleted'=>$area->getIsDeleted()?1:0,
				));

			$stat = $this->db->prepare("INSERT INTO area_history (area_id,  title,description,country_id,parent_area_id,user_account_id  , created_at, approved_at, api2_application_id,is_duplicate_of_id,is_deleted) VALUES ".
					"(:area_id,  :title,:description,:country_id,:parent_area_id,:user_account_id, :created_at, :approved_at, :api2_application_id, :is_duplicate_of_id,:is_deleted)");
			$stat->execute(array(
					'area_id'=>$area->getId(),
					'title'=>substr($area->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$area->getDescription(),
					'country_id'=>$area->getCountryId(),
					'parent_area_id'=>$area->getParentAreaId(),
					'user_account_id'=>$user->getId(),
					'api2_application_id'=>($this->useragent && $this->useragent->hasApi2ApplicationId() ? $this->useragent->getApi2ApplicationId() : null),
					'created_at'=>$this->timesource->getFormattedForDataBase(),
					'approved_at'=>$this->timesource->getFormattedForDataBase(),
					'is_duplicate_of_id'=>$area->getIsDuplicateOfId(),
					'is_deleted'=>$area->getIsDeleted()?1:0,
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
