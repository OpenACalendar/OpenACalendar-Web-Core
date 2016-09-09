<?php


namespace repositories;

use dbaccess\MediaDBAccess;
use models\MediaEditMetaDataModel;
use models\SiteModel;
use models\MediaModel;
use models\UserAccountModel;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaRepository {

    /** @var Application */
    private  $app;

	/** @var  \dbaccess\MediaDBAccess */
	protected $mediaDBAccess;

    function __construct(Application $app)
    {
        $this->app = $app;
	    $this->mediaDBAccess = new MediaDBAccess($app);
    }

    /*
    * @deprecated
    */
    public function createFromFile(UploadedFile $newMedia, SiteModel $site, UserAccountModel $user, $title = null, $sourceText = null, $sourceURL = null) {
        $mediaEditMetaDataModel = new MediaEditMetaDataModel();
        $mediaEditMetaDataModel->setUserAccount($user);
        return $this->createFromFileWithMetaData($newMedia, $site, $mediaEditMetaDataModel, $title, $sourceText, $sourceURL);
    }

	public function createFromFileWithMetaData(UploadedFile $newMedia, SiteModel $site, MediaEditMetaDataModel $mediaEditMetaDataModel, $title = null, $sourceText = null, $sourceURL = null) {

		if ($newMedia && in_array(strtolower($newMedia->guessExtension()), MediaModel::getAllowedImageExtensions())) {

			$media = new MediaModel();
			$media->setSiteId($site->getId());
			$media->setStorageSize($newMedia->getSize());
			$media->setTitle($title);
			$media->setSourceText($sourceText);
			$media->setSourceUrl($sourceURL);
			$media->setMd5(md5_file($newMedia->getRealPath()));

			$this->createWithMetaData($media, $mediaEditMetaDataModel);

			$storeDirectory = $this->app['config']->fileStoreLocation.DIRECTORY_SEPARATOR."media";
			$extension = strtolower($newMedia->guessExtension());

			$newMedia->move($storeDirectory,$media->getId().".".  $extension );

			return $media;
			
		}
	}

    /*
    * @deprecated
    */
    public function create(MediaModel $media, UserAccountModel $owner) {
        $mediaEditMetaDataModel = new MediaEditMetaDataModel();
        $mediaEditMetaDataModel->setUserAccount($owner);
        $this->createWithMetaData($media, $mediaEditMetaDataModel);
    }

	public function createWithMetaData(MediaModel $media, MediaEditMetaDataModel $mediaEditMetaDataModel) {

		$createdat = $this->app['timesource']->getFormattedForDataBase();
		
		try {
			$this->app['db']->beginTransaction();
		
			$stat = $this->app['db']->prepare("SELECT max(slug) AS c FROM media_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$media->getSiteId()));
			$data = $stat->fetch();
			$media->setSlug($data['c'] + 1);

			$stat = $this->app['db']->prepare("INSERT INTO media_information (site_id, slug, storage_size, created_by_user_account_id, created_at,title,source_text,source_url,md5) ".
					"VALUES (:site_id, :slug, :storage_size, :created_by_user_account_id, :created_at,:title,:source_text,:source_url,:md5) RETURNING id");
			$stat->execute(array(
					'site_id'=>$media->getSiteId(), 
					'slug'=>$media->getSlug(), 
					'storage_size'=>$media->getStorageSize(), 
					'created_by_user_account_id'=> $mediaEditMetaDataModel->getUserAccount()->getId(),
					'created_at'=>  $createdat,
					'title'=>substr($media->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'source_text'=>substr($media->getSourceText(),0,VARCHAR_COLUMN_LENGTH_USED),
					'source_url'=>substr($media->getSourceUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'md5'=>  $media->getMd5(),
				));
			$data = $stat->fetch();
			$media->setId($data['id']);

			$stat = $this->app['db']->prepare("INSERT INTO media_history (media_id,title,title_changed,source_text,source_text_changed,source_url,source_url_changed,user_account_id,created_at,from_ip) ".
				"VALUES (:media_id,:title,:title_changed,:source_text,:source_text_changed,:source_url,:source_url_changed,:user_account_id,:created_at,:from_ip)");
			$stat->execute(array(
				'media_id'=>$media->getId(),
				'title'=>substr($media->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'title_changed'=>0,
				'source_text'=>substr($media->getSourceText(),0,VARCHAR_COLUMN_LENGTH_USED),
				'source_text_changed'=>0,
				'source_url'=>substr($media->getSourceUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
				'source_url_changed'=>0,
				'user_account_id'=>$mediaEditMetaDataModel->getUserAccount()->getId(),
				'created_at'=>$createdat,
                'from_ip' => $mediaEditMetaDataModel->getIp(),
			));

			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'MediaSaved', array('media_id'=>$media->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {

		$stat = $this->app['db']->prepare("SELECT media_information.* FROM media_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$media = new MediaModel();
			$media->setFromDataBaseRow($stat->fetch());
			return $media;
		}
	}
		
	public function loadByID($id) {

		$stat = $this->app['db']->prepare("SELECT media_information.*  FROM media_information ".
				"WHERE media_information.id =:id");
		$stat->execute(array( 'id'=>$id ));
		if ($stat->rowCount() > 0) {
			$media = new MediaModel();
			$media->setFromDataBaseRow($stat->fetch());
			return $media;
		}
	}

	public function editWithMetaData(MediaModel $media,  MediaEditMetaDataModel $mediaEditMetaDataModel ) {
		if ($media->getIsDeleted()) {
			throw new \Exception("Can't edit deleted medias!");
		}

		try {
			$this->app['db']->beginTransaction();

			$fields = array('title','source_url','source_text');

			$this->mediaDBAccess->update($media, $fields, $mediaEditMetaDataModel);

			$this->app['db']->commit();

			$this->app['messagequeproducerhelper']->send('org.openacalendar', 'MediaSaved', array('media_id'=>$media->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}

	public function delete(MediaModel $media, UserAccountModel $user) {

		try {
			$this->app['db']->beginTransaction();
			
			$stat = $this->app['db']->prepare("UPDATE media_in_group SET removed_by_user_account_id=:removed_by_user_account_id,".
					" removed_at=:removed_at , removal_approved_at= :removal_approved_at WHERE ".
					" media_id=:media_id AND removed_at IS NULL ");
			$stat->execute(array(
					'media_id'=>$media->getId(),
					'removed_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'removal_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
					'removed_by_user_account_id'=>$user->getId(),
				));		
			
			$stat = $this->app['db']->prepare("UPDATE media_in_venue SET removed_by_user_account_id=:removed_by_user_account_id,".
				" removed_at=:removed_at , removal_approved_at= :removal_approved_at WHERE ".
				" media_id=:media_id AND removed_at IS NULL ");
			$stat->execute(array(
				'media_id'=>$media->getId(),
				'removed_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'removal_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'removed_by_user_account_id'=>$user->getId(),
			));

			$stat = $this->app['db']->prepare("UPDATE media_in_event SET removed_by_user_account_id=:removed_by_user_account_id,".
				" removed_at=:removed_at , removal_approved_at= :removal_approved_at WHERE ".
				" media_id=:media_id AND removed_at IS NULL ");
			$stat->execute(array(
				'media_id'=>$media->getId(),
				'removed_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'removal_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'removed_by_user_account_id'=>$user->getId(),
			));

			$stat = $this->app['db']->prepare("UPDATE media_information SET deleted_by_user_account_id=:deleted_by_user_account_id,".
				" deleted_at=:deleted_at WHERE ".
				" id=:id AND deleted_at IS NULL ");
			$stat->execute(array(
				'id'=>$media->getId(),
				'deleted_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'deleted_by_user_account_id'=>$user->getId(),
			));
			
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'MediaSaved', array('media_id'=>$media->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
		
		$media->deleteFiles();
	}
	
}
