<?php


namespace dbaccess;

use models\MediaEditMetaDataModel;
use models\UserAccountModel;
use models\MediaModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class MediaDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;

	function __construct(Application $application)
	{
		$this->db = $application['db'];
		$this->timesource = $application['timesource'];
	}

	protected $possibleFields = array('title','source_url','source_text');


	public function update(MediaModel $media, array $fields, MediaEditMetaDataModel $mediaEditMetaDataModel) {
		$alreadyInTransaction = $this->db->inTransaction();


		// Make Information Data
		$fieldsSQL1 = array( 'cached_updated_at = :cached_updated_at ');
		$fieldsParams1 = array( 'id'=>$media->getId(), 'cached_updated_at'=> $this->timesource->getFormattedForDataBase() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'title') {
				$fieldsParams1['title'] = substr($media->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'source_url') {
				$fieldsParams1['source_url'] = substr($media->getSourceUrl(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'source_text') {
				$fieldsParams1['source_text'] = substr($media->getSourceText(),0,VARCHAR_COLUMN_LENGTH_USED);
			}
		}

		// Make History Data
		$fieldsSQL2 = array('media_id','user_account_id','created_at','from_ip');
		$fieldsSQLParams2 = array(':media_id',':user_account_id',':created_at',':from_ip');
		$fieldsParams2 = array(
			'media_id'=>$media->getId(),
			'user_account_id'=>($mediaEditMetaDataModel->getUserAccount() ? $mediaEditMetaDataModel->getUserAccount()->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
			'from_ip'=>$mediaEditMetaDataModel->getIp(),
		);
		// No edit comment here
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields) || $field == 'title') {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'title') {
					$fieldsParams2['title'] = substr($media->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'source_url') {
					$fieldsParams2['source_url'] = substr($media->getSourceUrl(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'source_text') {
					$fieldsParams2['source_text'] = substr($media->getSourceText(),0,VARCHAR_COLUMN_LENGTH_USED);
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
			$stat = $this->db->prepare("UPDATE media_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO media_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
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
