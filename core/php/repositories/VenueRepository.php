<?php


namespace repositories;

use models\VenueModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\builders\EventRepositoryBuilder;
use repositories\UserInSiteRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueRepository {
	
	
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
					"description,lat,lng,country_id,area_id,created_at,approved_at,address,address_code) ".
					"VALUES (:site_id, :slug, :title, ".
					":description, :lat, :lng,:country_id, :area_id,:created_at,:approved_at,:address,:address_code) RETURNING id");
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
			
			$stat = $DB->prepare("INSERT INTO venue_history (venue_id, title,description,lat,lng, country_id,area_id,user_account_id  , created_at,approved_at,address,address_code, is_new) VALUES ".
					"(:venue_id,:title, :description, :lat, :lng,:country_id,:area_id,:user_account_id  , :created_at,:approved_at,:address,:address_code, '1')");
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
	
	public function edit(VenueModel $venue, UserAccountModel $creator) {
		global $DB, $EXTENSIONHOOKRUNNER;
		
		if ($venue->getIsDeleted()) {
			throw new \Exception("Can't edit deleted venue!");
		}
		
		$EXTENSIONHOOKRUNNER->beforeVenueSave($venue,$creator);
		
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE venue_information  SET title=:title,description=:description,".
					"lat=:lat,lng=:lng , country_id=:country_id, area_id=:area_id, address=:address, ".
					"address_code=:address_code, is_deleted='0' WHERE id=:id");
			$stat->execute(array(
					'id'=>$venue->getId(),
					'title'=>$venue->getTitle(),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
					'description'=>$venue->getDescription(),
					'address'=>$venue->getAddress(),
					'address_code'=>$venue->getAddressCode(),
					'country_id'=>$venue->getCountryId(),
					'area_id'=>$venue->getAreaId(),
				));
			
			$stat = $DB->prepare("INSERT INTO venue_history (venue_id, title, lat,lng,country_id, ".
					"area_id, description, user_account_id  , created_at,approved_at,address,address_code,is_duplicate_of_id) VALUES ".
					"(:venue_id, :title, :lat, :lng, :country_id,".
					":area_id,:description,  :user_account_id  , :created_at,:approved_at,:address,:address_code,:is_duplicate_of_id)");
			$stat->execute(array(
					'venue_id'=>$venue->getId(),
					'title'=>$venue->getTitle(),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
					'description'=>$venue->getDescription(),
					'address'=>$venue->getAddress(),
					'address_code'=>$venue->getAddressCode(),
					'user_account_id'=>$creator->getId(),				
					'country_id'=>$venue->getCountryId(),
					'is_duplicate_of_id'=>$venue->getIsDuplicateOfId(),
					'area_id'=>$venue->getAreaId(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function delete(VenueModel $venue, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE venue_information  SET is_deleted='1' WHERE id=:id");
			$stat->execute(array(
					'id'=>$venue->getId(),
				));
			
			$stat = $DB->prepare("INSERT INTO venue_history (venue_id, title, lat,lng,country_id, ".
					"description, user_account_id  , created_at,approved_at, is_deleted) VALUES ".
					"(:venue_id, :title, :lat, :lng, :country_id,".
					":description,  :user_account_id  , :created_at, :approved_at, '1')");
			$stat->execute(array(
					'venue_id'=>$venue->getId(),
					'title'=>$venue->getTitle(),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
					'description'=>$venue->getDescription(),
					'user_account_id'=>$creator->getId(),				
					'country_id'=>$venue->getCountryId(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	public function markDuplicate(VenueModel $duplicateVenue, VenueModel $originalVenue, UserAccountModel $user=null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE venue_information  SET is_deleted='1', is_duplicate_of_id = :is_duplicate_of_id WHERE id=:id");
			$stat->execute(array(
					'id'=>$duplicateVenue->getId(),
					'is_duplicate_of_id'=>$originalVenue->getId(),
				));

			$stat = $DB->prepare("INSERT INTO venue_history (venue_id, title, lat,lng,country_id, ".
					"description, user_account_id  , created_at,approved_at, is_deleted, is_duplicate_of_id) VALUES ".
					"(:venue_id, :title, :lat, :lng, :country_id,".
					":description,  :user_account_id  , :created_at, :approved_at, '1', :is_duplicate_of_id)");
			$stat->execute(array(
					'venue_id'=>$duplicateVenue->getId(),
					'is_duplicate_of_id'=>$originalVenue->getId(),
					'title'=>$duplicateVenue->getTitle(),
					'lat'=>$duplicateVenue->getLat(),
					'lng'=>$duplicateVenue->getLng(),
					'description'=>$duplicateVenue->getDescription(),
					'user_account_id'=>($user ? $user->getId() : null),
					'country_id'=>$duplicateVenue->getCountryId(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));

			// Move any Events
			$statUpdateInfo = $DB->prepare("UPDATE event_information  SET venue_id=:venue_id WHERE id=:id");
			$statInsertHistory = $DB->prepare("INSERT INTO event_history (event_id, summary, description,start_at, end_at, user_account_id  , ".
					"created_at, reverted_from_created_at,venue_id,country_id,timezone,".
					"url, ticket_url, is_physical, is_virtual, area_id, approved_at) VALUES ".
					"(:event_id, :summary, :description, :start_at, :end_at, :user_account_id  , ".
					":created_at, :reverted_from_created_at,:venue_id,:country_id,:timezone,"."
						:url, :ticket_url, :is_physical, :is_virtual, :area_id, :approved_at)");
			$eventRepoBuilder = new EventRepositoryBuilder();
			$eventRepoBuilder->setVenue($duplicateVenue);
			foreach($eventRepoBuilder->fetchAll() as $event) {
				$statUpdateInfo->execute(array(
					'id'=>$event->getId(),
					'venue_id'=>$originalVenue->getId(),
				));
				$statInsertHistory->execute(array(
					'event_id'=>$event->getId(),
					'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$event->getDescription(),
					'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
					'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
					'venue_id'=>$originalVenue->getId(),
					'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
					'country_id'=>$event->getCountryId(),
					'timezone'=>$event->getTimezone(),
					'user_account_id'=>($user ? $user->getId(): null),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
					'reverted_from_created_at'=> null,
					'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'ticket_url'=>substr($event->getTicketUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
					'is_physical'=>$event->getIsPhysical()?1:0,
					'is_virtual'=>$event->getIsVirtual()?1:0,
				));
			}

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
}

