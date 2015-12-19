<?php


namespace repositories;

use dbaccess\ImportURLDBAccess;
use models\ImportURLEditMetaDataModel;
use models\ImportURLModel;
use models\SiteModel;
use models\UserAccountModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLRepository {


	/** @var  \dbaccess\ImportURLDBAccess */
	protected $importURLDBAccess;

	function __construct()
	{
		global $DB, $USERAGENT;
		$this->importURLDBAccess = new ImportURLDBAccess($DB, new \TimeSource(), $USERAGENT);
	}

	public function create(ImportURLModel $importURL, SiteModel $site, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM import_url_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$importURL->setSlug($data['c'] + 1);
			
			$stat = $DB->prepare("INSERT INTO import_url_information (site_id, slug, title,url,url_canonical,created_at,group_id,is_enabled,country_id,area_id, approved_at, is_manual_events_creation) ".
					"VALUES (:site_id, :slug, :title,:url,:url_canonical, :created_at, :group_id,:is_enabled,:country_id,:area_id,:approved_at,:is_manual_events_creation) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$importURL->getSlug(),
					'title'=>substr($importURL->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url'=>substr($importURL->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'url_canonical'=>substr($importURL->getUrlCanonical(),0,VARCHAR_COLUMN_LENGTH_USED),
					'group_id'=>$importURL->getGroupId(),
					'country_id'=>$importURL->getCountryId(),
					'area_id'=>$importURL->getAreaId(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),		
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
					'is_enabled'=>$importURL->getIsEnabled()?1:0,
					'is_manual_events_creation'=>$importURL->getIsManualEventsCreation()?1:0,
				));
			$data = $stat->fetch();
			$importURL->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO import_url_history (import_url_id, title, user_account_id  , created_at,group_id,is_enabled,country_id,area_id, approved_at, is_new, is_manual_events_creation) VALUES ".
					"(:curated_list_id, :title, :user_account_id  , :created_at, :group_id,:is_enabled,:country_id,:area_id, :approved_at, '1', :is_manual_events_creation )");
			$stat->execute(array(
					'curated_list_id'=>$importURL->getId(),
					'title'=>substr($importURL->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'group_id'=>$importURL->getGroupId(),
					'country_id'=>$importURL->getCountryId(),
					'area_id'=>$importURL->getAreaId(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),		
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
					'is_enabled'=>$importURL->getIsEnabled()?1:0,
					'is_manual_events_creation'=>$importURL->getIsManualEventsCreation()?1:0,
				));
			
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function loadBySlug(SiteModel $site, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT import_url_information.* FROM import_url_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$iurl = new ImportURLModel();
			$iurl->setFromDataBaseRow($stat->fetch());
			return $iurl;
		}
	}
	
	public function loadById($id) {
		global $DB;
		$stat = $DB->prepare("SELECT import_url_information.* FROM import_url_information WHERE id =:id");
		$stat->execute(array('id'=>$id ));
		if ($stat->rowCount() > 0) {
			$iurl = new ImportURLModel();
			$iurl->setFromDataBaseRow($stat->fetch());
			return $iurl;
		}
	}

	/*
	* @deprecated
	*/
	public function edit(ImportURLModel $importURL, UserAccountModel $user) {
		$importURLEditMetaDataModel = new ImportURLEditMetaDataModel();
		$importURLEditMetaDataModel->setUserAccount($user);
		$this->editWithMetaData($importURL, $importURLEditMetaDataModel);
	}

	public function editWithMetaData(ImportURLModel $importURL, ImportURLEditMetaDataModel $importURLEditMetaDataModel) {
		global $DB;
		try {
			$DB->beginTransaction();

			$fields = array('title','country_id','area_id','is_manual_events_creation');
			$this->importURLDBAccess->update($importURL, $fields, $importURLEditMetaDataModel);
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}


	/*
	* @deprecated
	*/
	public function enable(ImportURLModel $importURL, UserAccountModel $user) {
		$importURLEditMetaDataModel = new ImportURLEditMetaDataModel();
		$importURLEditMetaDataModel->setUserAccount($user);
		$this->enableWithMetaData($importURL, $importURLEditMetaDataModel);
	}

	public function enableWithMetaData(ImportURLModel $importURL, ImportURLEditMetaDataModel $importURLEditMetaDataModel) {
		global $DB;
		try {
			$DB->beginTransaction();

			$importURL->setIsEnabled(true);
			$importURL->setExpiredAt(null);

			$this->importURLDBAccess->update($importURL, array('is_enabled','expired_at'), $importURLEditMetaDataModel);


			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	/*
	* @deprecated
	*/
	public function disable(ImportURLModel $importURL, UserAccountModel $user) {
		$importURLEditMetaDataModel = new ImportURLEditMetaDataModel();
		$importURLEditMetaDataModel->setUserAccount($user);
		$this->disableWithMetaData($importURL, $importURLEditMetaDataModel);
	}

	public function disableWithMetaData(ImportURLModel $importURL, ImportURLEditMetaDataModel $importURLEditMetaDataModel) {
		global $DB;
		try {
			$DB->beginTransaction();


			$importURL->setIsEnabled(false);
			$importURL->setExpiredAt(null);

			$this->importURLDBAccess->update($importURL, array('is_enabled','expired_at'), $importURLEditMetaDataModel);
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function getLastEditDateForImportURL(ImportURLModel $importURL) {
		global $DB;
		$stat = $DB->prepare("SELECT max(created_at) AS c FROM import_url_history WHERE import_url_id = :import_url_id");
		$stat->execute(array('import_url_id'=>$importURL->getId()));
		$data = $stat->fetch();
		return new \DateTime($data['c'], new \DateTimeZone('UTC'));
	}
	
	
	public function getLastRunDateForImportURL(ImportURLModel $importURL) {
		global $DB;
		$stat = $DB->prepare("SELECT max(created_at) AS c FROM import_url_result WHERE import_url_id = :import_url_id");
		$stat->execute(array('import_url_id'=>$importURL->getId()));
		$data = $stat->fetch();
		return $data['c'] ? new \DateTime($data['c'], new \DateTimeZone('UTC')) : null;
	}
	
	public function expire(ImportURLModel $importURL) {
		global $DB;
		try {
			$DB->beginTransaction();

			$importURL->setExpiredAt(\TimeSource::getDateTime());


			$importURLEditMetaData = new ImportURLEditMetaDataModel();
			$importURLEditMetaData->setUserAccount(null);

			$this->importURLDBAccess->update($importURL, array('expired_at'), $importURLEditMetaData);

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function loadClashForImportUrl(ImportURLModel $importURL) {
		global $DB;
		
		$sql = "SELECT import_url_information.* FROM import_url_information WHERE ".
				"is_enabled='1' AND expired_at IS NULL AND site_id=:site_id AND url_canonical=:url_canonical ";
		$params = array(
				'site_id'=>$importURL->getSiteId(),
				'url_canonical'=>$importURL->getUrlCanonical(),
			);
		if ($importURL->getId()) {
			$sql .= " AND id != :id";
			$params['id'] = $importURL->getId();
		}
				
		$stat = $DB->prepare($sql);
		$stat->execute($params);
		if ($stat->rowCount() > 0) {
			$iurl = new ImportURLModel();
			$iurl->setFromDataBaseRow($stat->fetch());
			return $iurl;
		}
	}	
	
}

