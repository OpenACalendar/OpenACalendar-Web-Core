<?php


namespace dbaccess;

use models\TagEditMetaDataModel;
use models\UserAccountModel;
use models\TagModel;
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

class TagDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;


    function __construct(Application $application)
    {
        $this->db = $application['db'];
        $this->timesource = $application['timesource'];
    }

	protected $possibleFields = array('title','description','is_deleted');


	public function update(TagModel $tag, $fields, TagEditMetaDataModel $tagEditMetaDataModel ) {
		$alreadyInTransaction = $this->db->inTransaction();

		// Make Information Data
		$fieldsSQL1 = array( 'cached_updated_at = :cached_updated_at ');
		$fieldsParams1 = array( 'id'=>$tag->getId() , 'cached_updated_at'=> $this->timesource->getFormattedForDataBase() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'title') {
				$fieldsParams1['title'] = substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'description') {
				$fieldsParams1['description'] = $tag->getDescription();
			} else if ($field == 'is_deleted') {
				$fieldsParams1['is_deleted'] = ($tag->getIsDeleted()?1:0);
			}
		}

		// Make History Data
		$fieldsSQL2 = array('tag_id','user_account_id','created_at','approved_at','from_ip');
		$fieldsSQLParams2 = array(':tag_id',':user_account_id',':created_at',':approved_at',':from_ip');
		$fieldsParams2 = array(
			'tag_id'=>$tag->getId(),
			'user_account_id'=>($tagEditMetaDataModel->getUserAccount() ? $tagEditMetaDataModel->getUserAccount()->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
			'approved_at'=>$this->timesource->getFormattedForDataBase(),
			'from_ip'=>$tagEditMetaDataModel->getIp(),
		);
		if ($tagEditMetaDataModel->getEditComment()) {
			$fieldsSQL2[] = ' edit_comment ';
			$fieldsSQLParams2[] = ' :edit_comment ';
			$fieldsParams2['edit_comment'] = $tagEditMetaDataModel->getEditComment();
		}
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields) || $field == 'title') {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'title') {
					$fieldsParams2['title'] = substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'description') {
					$fieldsParams2['description'] = $tag->getDescription();
				} else if ($field == 'is_deleted') {
					$fieldsParams2['is_deleted'] = ($tag->getIsDeleted()?1:0);
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
			$stat = $this->db->prepare("UPDATE tag_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO tag_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
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
