<?php


namespace repositories;

use dbaccess\VenueDBAccess;
use dbaccess\EventDBAccess;
use models\EventEditMetaDataModel;
use models\VenueEditMetaDataModel;
use models\VenueModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\builders\EventRepositoryBuilder;
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
class VenueRepository {

    /** @var Application */
    private  $app;

	/** @var  \dbaccess\VenueDBAccess */
	protected $venueDBAccess;


	function __construct(Application $app)
	{
        $this->app = $app;
		$this->venueDBAccess = new VenueDBAccess($app);
	}


	/*
	* @deprecated
	*/
	public function create(VenueModel $venue, SiteModel $site, UserAccountModel $creator)
	{
		$venueEditMetaDataModel = new VenueEditMetaDataModel();
		$venueEditMetaDataModel->setUserAccount($creator);
		$this->createWithMetaData($venue, $site, $venueEditMetaDataModel);
	}

	public function createWithMetaData(VenueModel $venue, SiteModel $site, VenueEditMetaDataModel $venueEditMetaDataModel) {
        $slugify = new Slugify($this->app);
		
		$this->app['extensionhookrunner']->beforeVenueSave($venue,$venueEditMetaDataModel->getUserAccount());
		
		try {
			$this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("SELECT max(slug) AS c FROM venue_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$venue->setSlug($data['c'] + 1);
			
			$stat = $this->app['db']->prepare("INSERT INTO venue_information (site_id, slug, slug_human,  title,".
					"description,lat,lng,country_id,area_id,created_at,approved_at,address,address_code, is_deleted) ".
					"VALUES (:site_id, :slug, :slug_human,  :title, ".
					":description, :lat, :lng,:country_id, :area_id,:created_at,:approved_at,:address,:address_code, '0') RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$venue->getSlug(),
                    'slug_human'=>$slugify->process($venue->getTitle()),
					'title'=>substr($venue->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
					'description'=>$venue->getDescription(),
					'address'=>$venue->getAddress(),
					'address_code'=>$venue->getAddressCode(),
					'country_id'=>$venue->getCountryId(),
					'area_id'=>$venue->getAreaId(),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$venue->setId($data['id']);
			
			$stat = $this->app['db']->prepare("INSERT INTO venue_history (venue_id, title,description,lat,lng, country_id,area_id,user_account_id  , created_at,approved_at,address,address_code, is_new, is_deleted, edit_comment, from_ip) VALUES ".
					"(:venue_id,:title, :description, :lat, :lng,:country_id,:area_id,:user_account_id  , :created_at,:approved_at,:address,:address_code, '1', '0', :edit_comment, :from_ip)");
			$stat->execute(array(
					'venue_id'=>$venue->getId(),
					'title'=>substr($venue->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
					'description'=>$venue->getDescription(),
					'address'=>$venue->getAddress(),
					'address_code'=>$venue->getAddressCode(),
					'user_account_id'=>($venueEditMetaDataModel->getUserAccount() ? $venueEditMetaDataModel->getUserAccount()->getId() : null),
					'country_id'=>$venue->getCountryId(),
					'area_id'=>$venue->getAreaId(),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'approved_at'=>$this->app['timesource']->getFormattedForDataBase(),
					'edit_comment'=>$venueEditMetaDataModel->getEditComment(),
                    'from_ip' => $venueEditMetaDataModel->getIp(),
				));
			$data = $stat->fetch();
			
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'VenueSaved', array('venue_id'=>$venue->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		if (strpos($slug, "-")) {
			$slug = array_shift(explode("-", $slug, 2));
		}
		$stat = $this->app['db']->prepare("SELECT venue_information.* FROM venue_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$venue = new VenueModel();
			$venue->setFromDataBaseRow($stat->fetch());
			//  data migration .... if no human_slug, let's add one
            if ($venue->getTitle() && !$venue->getSlugHuman()) {
                $slugify = new Slugify($this->app);
                $venue->setSlugHuman($slugify->process($venue->getTitle()));
                $stat = $this->app['db']->prepare("UPDATE venue_information SET slug_human=:slug_human WHERE id=:id");
                $stat->execute(array(
                    'id'=>$venue->getId(),
                    'slug_human'=>$venue->getSlugHuman(),
                ));
            }
			return $venue;
		}
	}
	
	
	public function loadById($id) {
		$stat = $this->app['db']->prepare("SELECT venue_information.* FROM venue_information WHERE id = :id");
		$stat->execute(array( 'id'=>$id, ));
		if ($stat->rowCount() > 0) {
			$venue = new VenueModel();
			$venue->setFromDataBaseRow($stat->fetch());
            //  data migration .... if no human_slug, let's add one
            if ($venue->getTitle() && !$venue->getSlugHuman()) {
                $slugify = new Slugify($this->app);
                $venue->setSlugHuman($slugify->process($venue->getTitle()));
                $stat = $this->app['db']->prepare("UPDATE venue_information SET slug_human=:slug_human WHERE id=:id");
                $stat->execute(array(
                    'id'=>$venue->getId(),
                    'slug_human'=>$venue->getSlugHuman(),
                ));
            }
			return $venue;
		}
	}

	/*
	* @deprecated
	*/
	public function edit(VenueModel $venue, UserAccountModel $user) {
		$venueEditMetaDataModel = new VenueEditMetaDataModel();
		$venueEditMetaDataModel->setUserAccount($user);
		$this->editWithMetaData($venue, $venueEditMetaDataModel);
	}

	public function editWithMetaData(VenueModel $venue, VenueEditMetaDataModel $venueEditMetaDataModel) {

		if ($venue->getIsDeleted()) {
			throw new \Exception("Can't edit deleted venue!");
		}
		
		$this->app['extensionhookrunner']->beforeVenueSave($venue,$venueEditMetaDataModel->getUserAccount());


		$fields = array('title','lat','lng','description','address','address_code','country_id','area_id','is_deleted');

		$this->venueDBAccess->update($venue,$fields,$venueEditMetaDataModel);

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'VenueSaved', array('venue_id'=>$venue->getId()));
	}

	/*
	* @deprecated
	*/
	public function delete(VenueModel $venue, UserAccountModel $user) {
		$venueEditMetaDataModel = new VenueEditMetaDataModel();
		$venueEditMetaDataModel->setUserAccount($user);
		$this->deleteWithMetaData($venue, $venueEditMetaDataModel);
	}

	public function deleteWithMetaData(VenueModel $venue, VenueEditMetaDataModel $venueEditMetaDataModel) {
		$venue->setIsDeleted(true);
		$this->venueDBAccess->update($venue,array('is_deleted'),$venueEditMetaDataModel);

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'VenueSaved', array('venue_id'=>$venue->getId()));
	}

	/*
	* @deprecated
	*/
	public function undelete(VenueModel $venue, UserAccountModel $user) {
		$venueEditMetaDataModel = new VenueEditMetaDataModel();
		$venueEditMetaDataModel->setUserAccount($user);
		$this->undeleteWithMetaData($venue, $venueEditMetaDataModel);
	}

	public function undeleteWithMetaData(VenueModel $venue, VenueEditMetaDataModel $venueEditMetaDataModel) {
		$venue->setIsDeleted(false);
		$this->venueDBAccess->update($venue,array('is_deleted'),$venueEditMetaDataModel);

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'VenueSaved', array('venue_id'=>$venue->getId()));
	}


	/*
	* @deprecated
	*/
	public function markDuplicate(VenueModel $duplicateVenue, VenueModel $originalVenue, UserAccountModel $user=null) {
		$venueEditMetaDataModel = new VenueEditMetaDataModel();
		$venueEditMetaDataModel->setUserAccount($user);
		$this->markDuplicateWithMetaData($duplicateVenue, $originalVenue, $venueEditMetaDataModel);
	}

	public function markDuplicateWithMetaData(VenueModel $duplicateVenue, VenueModel $originalVenue, VenueEditMetaDataModel $venueEditMetaDataModel) {


		if ($duplicateVenue->getId() == $originalVenue->getId()) return;

		try {
			$this->app['db']->beginTransaction();

			$duplicateVenue->setIsDeleted(true);
			$duplicateVenue->setIsDuplicateOfId($originalVenue->getId());
			$this->venueDBAccess->update($duplicateVenue,array('is_deleted','is_duplicate_of_id'),$venueEditMetaDataModel);

			// Move any Events
			$eventEditMetaData = new EventEditMetaDataModel();
			$eventEditMetaData->setForSecondaryEditFromPrimaryEditMeta($venueEditMetaDataModel);

			$eventRepoBuilder = new EventRepositoryBuilder($this->app);
			$eventRepoBuilder->setVenue($duplicateVenue);
			$eventDBAccess = new EventDBAccess($this->app);
			foreach($eventRepoBuilder->fetchAll() as $event) {
				$event->setVenueId($originalVenue->getId());
				$eventDBAccess->update($event, array('venue_id'), $eventEditMetaData);
			}

			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'VenueSaved', array('venue_id'=>$duplicateVenue->getId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}


	/**
	 *
	 * @TODO This could be improved, At the moment it sets any events with this venue to no venue & no area but it could set them to area of venue.
	 * Have to be careful of rewriting history if we do that. Create Event - Create Area - Create Venue  and set event to it - now purge venue.
	 * If we just did "update event set area=X, venue=null where venue=Y" on the history table it will look as if the venue was set on the event BEFORE the venue was created.
	 *
	 * @param VenueModel $venue
	 * @throws \Exception
	 * @throws Exception
	 */
	public function purge(VenueModel $venue) {

		try {
			$this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("UPDATE event_history SET venue_id = NULL, venue_id_changed=0 WHERE venue_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $this->app['db']->prepare("UPDATE event_information SET venue_id = NULL WHERE venue_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $this->app['db']->prepare("UPDATE venue_history SET is_duplicate_of_id = NULL, is_duplicate_of_id_changed = 0 WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $this->app['db']->prepare("UPDATE venue_information SET is_duplicate_of_id = NULL WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $this->app['db']->prepare("DELETE FROM venue_history WHERE venue_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$statDeleteComment = $this->app['db']->prepare("DELETE FROM sysadmin_comment_information WHERE id=:id");
			$statDeleteLink = $this->app['db']->prepare("DELETE FROM sysadmin_comment_about_venue WHERE sysadmin_comment_id=:id");
			$stat = $this->app['db']->prepare("SELECT sysadmin_comment_id FROM sysadmin_comment_about_venue WHERE venue_id=:id");
			$stat->execute(array('id'=>$venue->getId()));
			while($data = $stat->fetch()) {
				$statDeleteLink->execute(array($data['sysadmin_comment_id']));
				$statDeleteComment->execute(array($data['sysadmin_comment_id']));
			}

			$stat = $this->app['db']->prepare("DELETE FROM venue_information WHERE id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'VenuePurged', array());
		} catch (Exception $e) {
			$this->app['db']->rollBack();
			throw $e;
		}

	}


	public function updateFutureEventsCache(VenueModel $venue) {

		$statUpdate = $this->app['db']->prepare("UPDATE venue_information SET cached_future_events=:count WHERE id=:id");

		$erb = new EventRepositoryBuilder($this->app);
		$erb->setVenue($venue);
		$erb->setIncludeDeleted(false);
		$erb->setIncludeCancelled(false);
		$erb->setAfterNow();
		$count = count($erb->fetchAll());

		$statUpdate->execute(array('count'=>$count,'id'=>$venue->getId()));

		$venue->setCachedFutureEvents($count);
	}
	
}

