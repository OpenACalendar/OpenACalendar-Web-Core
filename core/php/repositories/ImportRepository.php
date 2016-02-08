<?php


namespace repositories;

use dbaccess\ImportDBAccess;
use models\ImportEditMetaDataModel;
use models\ImportModel;
use models\SiteModel;
use models\UserAccountModel;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportRepository {


    /** @var Application */
    private  $app;

	/** @var  \dbaccess\ImportDBAccess */
	protected $importDBAccess;

	function __construct(Application $app)
	{
        $this->app = $app;
		$this->importDBAccess = new ImportDBAccess($app);
	}

	public function create(ImportModel $importURL, SiteModel $site, UserAccountModel $creator) {

		try {
			$this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("SELECT max(slug) AS c FROM import_url_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$importURL->setSlug($data['c'] + 1);
			
			$stat = $this->app['db']->prepare("INSERT INTO import_url_information (site_id, slug, title,url,url_canonical,created_at,group_id,is_enabled,country_id,area_id, approved_at, is_manual_events_creation) ".
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
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'is_enabled'=>$importURL->getIsEnabled()?1:0,
					'is_manual_events_creation'=>$importURL->getIsManualEventsCreation()?1:0,
				));
			$data = $stat->fetch();
			$importURL->setId($data['id']);
			
			$stat = $this->app['db']->prepare("INSERT INTO import_url_history (import_url_id, title, user_account_id  , created_at,group_id,is_enabled,country_id,area_id, approved_at, is_new, is_manual_events_creation) VALUES ".
					"(:curated_list_id, :title, :user_account_id  , :created_at, :group_id,:is_enabled,:country_id,:area_id, :approved_at, '1', :is_manual_events_creation )");
			$stat->execute(array(
					'curated_list_id'=>$importURL->getId(),
					'title'=>substr($importURL->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'group_id'=>$importURL->getGroupId(),
					'country_id'=>$importURL->getCountryId(),
					'area_id'=>$importURL->getAreaId(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'is_enabled'=>$importURL->getIsEnabled()?1:0,
					'is_manual_events_creation'=>$importURL->getIsManualEventsCreation()?1:0,
				));
			
			
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'ImportSaved', array('import_id'=>$importURL->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}
	
	public function loadBySlug(SiteModel $site, $slug) {

		$stat = $this->app['db']->prepare("SELECT import_url_information.* FROM import_url_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$iurl = new ImportModel();
			$iurl->setFromDataBaseRow($stat->fetch());
			return $iurl;
		}
	}
	
	public function loadById($id) {

		$stat = $this->app['db']->prepare("SELECT import_url_information.* FROM import_url_information WHERE id =:id");
		$stat->execute(array('id'=>$id ));
		if ($stat->rowCount() > 0) {
			$iurl = new ImportModel();
			$iurl->setFromDataBaseRow($stat->fetch());
			return $iurl;
		}
	}

	/*
	* @deprecated
	*/
	public function edit(ImportModel $importURL, UserAccountModel $user) {
		$importEditMetaDataModel = new ImportEditMetaDataModel();
		$importEditMetaDataModel->setUserAccount($user);
		$this->editWithMetaData($importURL, $importEditMetaDataModel);
	}

	public function editWithMetaData(ImportModel $importURL, ImportEditMetaDataModel $importEditMetaDataModel) {

		try {
			$this->app['db']->beginTransaction();

			$fields = array('title','country_id','area_id','is_manual_events_creation');
			$this->importDBAccess->update($importURL, $fields, $importEditMetaDataModel);
			
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'ImportSaved', array('import_id'=>$importURL->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}


	/*
	* @deprecated
	*/
	public function enable(ImportModel $importURL, UserAccountModel $user) {
		$importEditMetaDataModel = new ImportEditMetaDataModel();
		$importEditMetaDataModel->setUserAccount($user);
		$this->enableWithMetaData($importURL, $importEditMetaDataModel);
	}

	public function enableWithMetaData(ImportModel $importURL, ImportEditMetaDataModel $importEditMetaDataModel) {

		try {
			$this->app['db']->beginTransaction();

			$importURL->setIsEnabled(true);
			$importURL->setExpiredAt(null);

			$this->importDBAccess->update($importURL, array('is_enabled','expired_at'), $importEditMetaDataModel);


			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'ImportSaved', array('import_id'=>$importURL->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}

	/*
	* @deprecated
	*/
	public function disable(ImportModel $importURL, UserAccountModel $user) {
		$importEditMetaDataModel = new ImportEditMetaDataModel();
		$importEditMetaDataModel->setUserAccount($user);
		$this->disableWithMetaData($importURL, $importEditMetaDataModel);
	}

	public function disableWithMetaData(ImportModel $importURL, ImportEditMetaDataModel $importEditMetaDataModel) {

		try {
			$this->app['db']->beginTransaction();


			$importURL->setIsEnabled(false);
			$importURL->setExpiredAt(null);

			$this->importDBAccess->update($importURL, array('is_enabled','expired_at'), $importEditMetaDataModel);
			
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'ImportSaved', array('import_id'=>$importURL->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}
	
	public function getLastEditDateForImportURL(ImportModel $importURL) {

		$stat = $this->app['db']->prepare("SELECT max(created_at) AS c FROM import_url_history WHERE import_url_id = :import_url_id");
		$stat->execute(array('import_url_id'=>$importURL->getId()));
		$data = $stat->fetch();
		return new \DateTime($data['c'], new \DateTimeZone('UTC'));
	}
	
	
	public function getLastRunDateForImportURL(ImportModel $importURL) {

		$stat = $this->app['db']->prepare("SELECT max(created_at) AS c FROM import_url_result WHERE import_url_id = :import_url_id");
		$stat->execute(array('import_url_id'=>$importURL->getId()));
		$data = $stat->fetch();
		return $data['c'] ? new \DateTime($data['c'], new \DateTimeZone('UTC')) : null;
	}
	
	public function expire(ImportModel $importURL) {

		try {
			$this->app['db']->beginTransaction();

			$importURL->setExpiredAt($this->app['timesource']->getDateTime());


			$importURLEditMetaData = new ImportEditMetaDataModel();
			$importURLEditMetaData->setUserAccount(null);

			$this->importDBAccess->update($importURL, array('expired_at'), $importURLEditMetaData);

			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'ImportSaved', array('import_id'=>$importURL->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}
	
	public function loadClashForImportUrl(ImportModel $importURL) {

		
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
				
		$stat = $this->app['db']->prepare($sql);
		$stat->execute($params);
		if ($stat->rowCount() > 0) {
			$iurl = new ImportModel();
			$iurl->setFromDataBaseRow($stat->fetch());
			return $iurl;
		}
	}	
	
}

