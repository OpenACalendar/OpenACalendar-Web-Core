<?php


namespace repositories;

use models\TagEditMetaDataModel;
use models\TagModel;
use models\SiteModel;
use models\EventModel;
use models\UserAccountModel;
use dbaccess\TagDBAccess;
use Silex\Application;
use Slugify;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagRepository {

    /** @var Application */
    private  $app;

	/** @var  \dbaccess\TagDBAccess */
	protected $tagDBAccess;

	function __construct(Application $app)
	{
        $this->app = $app;
		$this->tagDBAccess = new TagDBAccess($app);
	}


    /*
    * @deprecated
    */
	public function create(TagModel $tag, SiteModel $site, UserAccountModel $creator) {
        $tagEditMetaDataModel = new TagEditMetaDataModel();
        $tagEditMetaDataModel->setUserAccount($creator);
        $this->createWithMetaData($tag, $site, $tagEditMetaDataModel);

    }

	public function createWithMetaData(TagModel $tag, SiteModel $site, TagEditMetaDataModel $tagEditMetaDataModel) {
        $slugify = new Slugify($this->app);
		try {
			$this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("SELECT max(slug) AS c FROM tag_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$tag->setSlug($data['c'] + 1);
			
			$stat = $this->app['db']->prepare("INSERT INTO tag_information (site_id, slug, slug_human,  title,description,created_at,approved_at, is_deleted) ".
					"VALUES (:site_id, :slug, :slug_human,  :title, :description, :created_at,:approved_at, '0') RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$tag->getSlug(),
                    'slug_human'=>$slugify->process($tag->getTitle()),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$tag->setId($data['id']);
			
			$stat = $this->app['db']->prepare("INSERT INTO tag_history (tag_id, title, description, user_account_id  , created_at, approved_at, is_new, is_deleted, from_ip) VALUES ".
					"(:tag_id, :title, :description, :user_account_id  , :created_at, :approved_at, '1', '0', :from_ip)");
			$stat->execute(array(
					'tag_id'=>$tag->getId(),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
					'user_account_id'=> ($tagEditMetaDataModel->getUserAccount() ? $tagEditMetaDataModel->getUserAccount()->getId() : null),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
                    'from_ip' => $tagEditMetaDataModel->getIp(),
				));
						
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'TagSaved', array('tag_id'=>$tag->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		$stat = $this->app['db']->prepare("SELECT tag_information.* FROM tag_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$tag = new TagModel();
			$tag->setFromDataBaseRow($stat->fetch());
            //  data migration .... if no human_slug, let's add one
            if ($tag->getTitle() && !$tag->getSlugHuman()) {
                $slugify = new Slugify($this->app);
                $tag->setSlugHuman($slugify->process($tag->getTitle()));
                $stat = $this->app['db']->prepare("UPDATE tag_information SET slug_human=:slug_human WHERE id=:id");
                $stat->execute(array(
                    'id'=>$tag->getId(),
                    'slug_human'=>$tag->getSlugHuman(),
                ));
            }
			return $tag;
		}
	}
	
	
	
	public function loadById($id) {
		$stat = $this->app['db']->prepare("SELECT tag_information.* FROM tag_information WHERE id = :id");
		$stat->execute(array( 'id'=>$id ));
		if ($stat->rowCount() > 0) {
			$tag = new TagModel();
			$tag->setFromDataBaseRow($stat->fetch());
            //  data migration .... if no human_slug, let's add one
            if ($tag->getTitle() && !$tag->getSlugHuman()) {
                $slugify = new Slugify($this->app);
                $tag->setSlugHuman($slugify->process($tag->getTitle()));
                $stat = $this->app['db']->prepare("UPDATE tag_information SET slug_human=:slug_human WHERE id=:id");
                $stat->execute(array(
                    'id'=>$tag->getId(),
                    'slug_human'=>$tag->getSlugHuman(),
                ));
            }
			return $tag;
		}
	}



	/*
	* @deprecated
	*/
	public function edit(TagModel $tag, UserAccountModel $user) {
		$tagEditMetaDataModel = new TagEditMetaDataModel();
		$tagEditMetaDataModel->setUserAccount($user);
		$this->editWithMetaData($tag, $tagEditMetaDataModel);
	}

	public function editWithMetaData(TagModel $tag, TagEditMetaDataModel $tagEditMetaDataModel) {

		if ($tag->getIsDeleted()) {
			throw new \Exception("Can't edit deleted tag!");
		}
		try {
			$this->app['db']->beginTransaction();

			$fields = array('title','description','is_deleted');
			$tag->setIsDeleted(false);
			$this->tagDBAccess->update($tag, $fields, $tagEditMetaDataModel);
			
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'TagSaved', array('tag_id'=>$tag->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}

	/*
	* @deprecated
	*/
	public function delete(TagModel $tag, UserAccountModel $user)
	{
		$tagEditMetaDataModel = new TagEditMetaDataModel();
		$tagEditMetaDataModel->setUserAccount($user);
		$this->deleteWithMetaData($tag, $tagEditMetaDataModel);
	}

	public function deleteWithMetaData(TagModel $tag, TagEditMetaDataModel $tagEditMetaDataModel) {

		try {
			$this->app['db']->beginTransaction();

			$tag->setIsDeleted(true);
			$this->tagDBAccess->update($tag, array('is_deleted'), $tagEditMetaDataModel);
			
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'TagSaved', array('tag_id'=>$tag->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}

	/*
	* @deprecated
	*/
	public function undelete(TagModel $tag, UserAccountModel $user) {
		$tagEditMetaDataModel = new TagEditMetaDataModel();
		$tagEditMetaDataModel->setUserAccount($user);
		$this->undeleteWithMetaData($tag, $tagEditMetaDataModel);
	}

	public function undeleteWithMetaData(TagModel $tag, TagEditMetaDataModel $tagEditMetaDataModel) {

		try {
			$this->app['db']->beginTransaction();

			$tag->setIsDeleted(false);
			$this->tagDBAccess->update($tag, array('is_deleted'), $tagEditMetaDataModel);

			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'TagSaved', array('tag_id'=>$tag->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}

	
	public function addTagToEvent(TagModel $tag, EventModel $event, UserAccountModel $user=null) {

		
		// check event not already in list
		$stat = $this->app['db']->prepare("SELECT * FROM event_has_tag WHERE tag_id=:tag_id AND ".
				" event_id=:event_id AND removed_at IS NULL ");
		$stat->execute(array(
			'tag_id'=>$tag->getId(),
			'event_id'=>$event->getId(),
		));
		if ($stat->rowCount() > 0) {
			return;
		}
			
		// Add!
		$stat = $this->app['db']->prepare("INSERT INTO event_has_tag (tag_id,event_id,added_by_user_account_id,added_at,addition_approved_at) ".
				"VALUES (:tag_id,:event_id,:added_by_user_account_id,:added_at,:addition_approved_at)");
		$stat->execute(array(
			'tag_id'=>$tag->getId(),
			'event_id'=>$event->getId(),
			'added_by_user_account_id'=>($user?$user->getId():null),
			'added_at'=>  $this->app['timesource']->getFormattedForDataBase(),
			'addition_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
		));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventHasTagSaved', array('tag_id'=>$tag->getId(),'event_id'=>$event->getId()));
		
	}

	
	
	
	public function removeTagFromEvent(TagModel $tag, EventModel $event, UserAccountModel $user=null) {


		
		$stat = $this->app['db']->prepare("UPDATE event_has_tag SET removed_by_user_account_id=:removed_by_user_account_id,".
				" removed_at=:removed_at, removal_approved_at=:removal_approved_at WHERE ".
				" event_id=:event_id AND tag_id=:tag_id AND removed_at IS NULL ");
		$stat->execute(array(
				'event_id'=>$event->getId(),
				'tag_id'=>$tag->getId(),
				'removed_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'removal_approved_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'removed_by_user_account_id'=>($user?$user->getId():null),
		));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'EventHasTagSaved', array('tag_id'=>$tag->getId(),'event_id'=>$event->getId()));
	}

	public function purge(TagModel $tag) {

		try {
			$this->app['db']->beginTransaction();
			
			$stat = $this->app['db']->prepare("DELETE FROM event_has_tag WHERE tag_id=:id");
			$stat->execute(array('id'=>$tag->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM tag_history WHERE tag_id=:id");
			$stat->execute(array('id'=>$tag->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM tag_information WHERE id=:id");
			$stat->execute(array('id'=>$tag->getId()));
		
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'TagPurged', array());
		} catch (Exception $e) {
			$this->app['db']->rollBack();
			throw $e;
		}
		
	}
				
}

