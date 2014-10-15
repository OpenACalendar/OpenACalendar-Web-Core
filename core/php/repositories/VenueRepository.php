<?php


namespace repositories;

use dbaccess\VenueDBAccess;
use dbaccess\EventDBAccess;
use models\VenueModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueRepository {

	/** @var  \dbaccess\VenueDBAccess */
	protected $venueDBAccess;


	function __construct()
	{
		global $DB;
		$this->venueDBAccess = new VenueDBAccess($DB, new \TimeSource());
	}

	public function create(VenueModel $venue, SiteModel $site, UserAccountModel $creator) {
		global $DB, $EXTENSIONHOOKRUNNER;
		
		$EXTENSIONHOOKRUNNER->beforeVenueSave($venue,$creator);
		
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM venue_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$venue->setSlug($data['c'] + 1);
			
			$stat = $DB->prepare("INSERT INTO venue_information (site_id, slug, title,".
					"description,lat,lng,country_id,area_id,created_at,approved_at,address,address_code, is_deleted) ".
					"VALUES (:site_id, :slug, :title, ".
					":description, :lat, :lng,:country_id, :area_id,:created_at,:approved_at,:address,:address_code, '0') RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$venue->getSlug(),
					'title'=>substr($venue->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
					'description'=>$venue->getDescription(),
					'address'=>$venue->getAddress(),
					'address_code'=>$venue->getAddressCode(),
					'country_id'=>$venue->getCountryId(),
					'area_id'=>$venue->getAreaId(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$venue->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO venue_history (venue_id, title,description,lat,lng, country_id,area_id,user_account_id  , created_at,approved_at,address,address_code, is_new, is_deleted) VALUES ".
					"(:venue_id,:title, :description, :lat, :lng,:country_id,:area_id,:user_account_id  , :created_at,:approved_at,:address,:address_code, '1', '0')");
			$stat->execute(array(
					'venue_id'=>$venue->getId(),
					'title'=>substr($venue->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
					'description'=>$venue->getDescription(),
					'address'=>$venue->getAddress(),
					'address_code'=>$venue->getAddressCode(),
					'user_account_id'=>$creator->getId(),				
					'country_id'=>$venue->getCountryId(),
					'area_id'=>$venue->getAreaId(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT venue_information.* FROM venue_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$venue = new VenueModel();
			$venue->setFromDataBaseRow($stat->fetch());
			return $venue;
		}
	}
	
	
	public function loadById($id) {
		global $DB;
		$stat = $DB->prepare("SELECT venue_information.* FROM venue_information WHERE id = :id");
		$stat->execute(array( 'id'=>$id, ));
		if ($stat->rowCount() > 0) {
			$venue = new VenueModel();
			$venue->setFromDataBaseRow($stat->fetch());
			return $venue;
		}
	}
	
	public function edit(VenueModel $venue, UserAccountModel $user) {
		global $DB, $EXTENSIONHOOKRUNNER;
		
		if ($venue->getIsDeleted()) {
			throw new \Exception("Can't edit deleted venue!");
		}
		
		$EXTENSIONHOOKRUNNER->beforeVenueSave($venue,$user);


		$fields = array('title','lat','lng','description','address','address_code','country_id','area_id','is_deleted');

		$this->venueDBAccess->update($venue,$fields,$user);
	}
	
	public function delete(VenueModel $venue, UserAccountModel $user) {
		$venue->setIsDeleted(true);
		$this->venueDBAccess->update($venue,array('is_deleted'),$user);
	}

	public function markDuplicate(VenueModel $duplicateVenue, VenueModel $originalVenue, UserAccountModel $user=null) {
		global $DB;

		if ($duplicateVenue->getId() == $originalVenue->getId()) return;

		try {
			$DB->beginTransaction();

			$duplicateVenue->setIsDeleted(true);
			$duplicateVenue->setIsDuplicateOfId($originalVenue->getId());
			$this->venueDBAccess->update($duplicateVenue,array('is_deleted','is_duplicate_of_id'),$user);

			// Move any Events
			$eventRepoBuilder = new EventRepositoryBuilder();
			$eventRepoBuilder->setVenue($duplicateVenue);
			$eventDBAccess = new EventDBAccess($DB, new \TimeSource());
			foreach($eventRepoBuilder->fetchAll() as $event) {
				$event->setVenueId($originalVenue->getId());
				$eventDBAccess->update($event, array('venue_id'), $user, null);
			}

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
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
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE event_history SET venue_id = NULL, venue_id_changed=0 WHERE venue_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $DB->prepare("UPDATE event_information SET venue_id = NULL WHERE venue_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $DB->prepare("UPDATE venue_history SET is_duplicate_of_id = NULL, is_duplicate_of_id_changed = 0 WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $DB->prepare("UPDATE venue_information SET is_duplicate_of_id = NULL WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $DB->prepare("DELETE FROM venue_history WHERE venue_id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$stat = $DB->prepare("DELETE FROM venue_information WHERE id=:id");
			$stat->execute(array('id'=>$venue->getId()));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
			throw $e;
		}

	}

}

