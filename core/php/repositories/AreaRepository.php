<?php


namespace repositories;

use models\AreaModel;
use models\SiteModel;
use models\UserAccountModel;
use models\CountryModel;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaRepository {

	
	public function create(AreaModel $area, AreaModel $parentArea = null, SiteModel $site, CountryModel $country, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM area_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$area->setSlug($data['c'] + 1);
			
			if ($parentArea) $area->setParentAreaId($parentArea->getId());
			
			$stat = $DB->prepare("INSERT INTO area_information (site_id, slug, title,description,country_id,parent_area_id,created_at,approved_at,cache_area_has_parent_generated) ".
					"VALUES (:site_id, :slug, :title,:description,:country_id,:parent_area_id,:created_at,:approved_at,:cache_area_has_parent_generated) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$area->getSlug(),
					'title'=>substr($area->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$area->getDescription(),
					'country_id'=>$country->getId(),
					'parent_area_id'=>($parentArea ? $parentArea->getId() : null),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
					'cache_area_has_parent_generated'=>  ( $parentArea ? '0' : '1' ),
				));
			$data = $stat->fetch();
			$area->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO area_history (area_id,  title,description,country_id,parent_area_id,user_account_id  , created_at, approved_at, is_new) VALUES ".
					"(:area_id,  :title,:description,:country_id,:parent_area_id,:user_account_id, :created_at,:approved_at,'1')");
			$stat->execute(array(
					'area_id'=>$area->getId(),
					'title'=>substr($area->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$area->getDescription(),
					'country_id'=>$country->getId(),
					'parent_area_id'=>($parentArea ? $parentArea->getId() : null),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
			
	}
	
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT area_information.* FROM area_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$area = new AreaModel();
			$area->setFromDataBaseRow($stat->fetch());
			return $area;
		}
	}
	
	
	public function loadBySlugAndCountry(SiteModel $site, $slug, CountryModel $country) {
		global $DB;
		$stat = $DB->prepare("SELECT area_information.* FROM area_information WHERE slug =:slug AND site_id =:sid AND country_id=:cid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug, 'cid'=>$country->getId() ));
		if ($stat->rowCount() > 0) {
			$area = new AreaModel();
			$area->setFromDataBaseRow($stat->fetch());
			return $area;
		}
	}
	
	
	public function loadById($id) {
		global $DB;
		$stat = $DB->prepare("SELECT area_information.* FROM area_information WHERE id = :id");
		$stat->execute(array( 'id'=>$id, ));
		if ($stat->rowCount() > 0) {
			$area = new AreaModel();
			$area->setFromDataBaseRow($stat->fetch());
			return $area;
		}
	}

	/**
	 * 
	 * 
	 * 
	 * @global type $DB
	 * @param \models\AreaModel $area
	 */
	public function buildCacheAreaHasParent(AreaModel $area) {
		global $DB;
		try {
			$DB->beginTransaction();

			$statInsertCache = $DB->prepare("INSERT INTO cached_area_has_parent(area_id,has_parent_area_id) VALUES (:area_id,:has_parent_area_id)");
			$statFirstArea = $DB->prepare("SELECT area_information.parent_area_id, area_information.cache_area_has_parent_generated FROM area_information WHERE area_information.id=:id");

			// get first parent
			$areaParentID = null;
			$statFirstArea->execute(array('id'=>$area->getId()));
			if ($statFirstArea->rowCount() > 0) {
				$d = $statFirstArea->fetch();
				// Wait, have we already done this one?
				if ($d['cache_area_has_parent_generated']) {
					$DB->commit();
					return;
				}
				$areaParentID = $d['parent_area_id'];
			}
			
			$statNextArea = $DB->prepare("SELECT area_information.parent_area_id FROM area_information WHERE area_information.id=:id");
			while($areaParentID) {
				// insert this parent into the cache
				$statInsertCache->execute(array('area_id'=>$area->getId(), 'has_parent_area_id'=>$areaParentID));
				
				// move up to next parent
				$statNextArea->execute(array('id'=>$areaParentID));
				if ($statNextArea->rowCount() > 0) {
					$d = $statNextArea->fetch();
					$areaParentID = $d['parent_area_id'];
				} else {
					$areaParentID = null;
				}
			}
			
			// finally mark this area as cached.
			$DB->prepare("UPDATE area_information SET cache_area_has_parent_generated='1' WHERE id=:id")
					->execute(array('id'=>$area->getId()));
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	/** 
	 * This will undelete the area to.
	 */
	public function edit(AreaModel $area, UserAccountModel $creator) {
		global $DB, $USERAGENT;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE area_information  SET ".
					"title=:title,description=:description, is_deleted='0'".
					"WHERE id=:id");
			$stat->execute(array(
					'id'=>$area->getId(),
					'title'=>$area->getTitle(),
					'description'=>$area->getDescription(),
				));
			
			$stat = $DB->prepare("INSERT INTO area_history (area_id,  title,description,country_id,parent_area_id,user_account_id  , created_at, approved_at, api2_application_id,is_duplicate_of_id) VALUES ".
					"(:area_id,  :title,:description,:country_id,:parent_area_id,:user_account_id, :created_at, :approved_at, :api2_application_id, :is_duplicate_of_id)");
			$stat->execute(array(
					'area_id'=>$area->getId(),
					'title'=>substr($area->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$area->getDescription(),
					'country_id'=>$area->getCountryId(),
					'parent_area_id'=>$area->getParentAreaId(),
					'user_account_id'=>$creator->getId(),				
					'api2_application_id'=>($USERAGENT->hasApi2ApplicationId()?$USERAGENT->getApi2ApplicationId():null),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
					'is_duplicate_of_id'=>$area->getIsDuplicateOfId(),
				));
			
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function editParentArea(AreaModel $area, UserAccountModel $creator) {
		global $DB;
		if ($area->getIsDeleted()) {
			throw new \Exception("Can't edit deleted area!");
		}
		try {
			$DB->beginTransaction();
			
			$stat = $DB->prepare("UPDATE area_information  SET ".
					"parent_area_id=:parent_area_id ".
					"WHERE id=:id");
			$stat->execute(array(
					'id'=>$area->getId(),
					'parent_area_id'=>$area->getParentAreaId(),
				));
			
			$stat = $DB->prepare("INSERT INTO area_history (area_id,  title,description,country_id,parent_area_id,user_account_id  , created_at, approved_at) VALUES ".
					"(:area_id,  :title,:description,:country_id,:parent_area_id,:user_account_id, :created_at, :approved_at)");
			$stat->execute(array(
					'area_id'=>$area->getId(),
					'title'=>substr($area->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$area->getDescription(),
					'country_id'=>$area->getCountryId(),
					'parent_area_id'=>$area->getParentAreaId(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			// new must clear caches
			// TODO clear for this area only - for now clear all.
			$DB->prepare("DELETE FROM cached_area_has_parent")->execute();
			$DB->prepare("UPDATE area_information SET cache_area_has_parent_generated='f'")->execute();
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	public function delete(AreaModel $area, UserAccountModel $creator) {
		global $DB;
		if ($area->getIsDeleted()) {
			throw new \Exception("Can't delete deleted area!");
		}
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE area_information  SET ".
					" is_deleted='1'".
					"WHERE id=:id");
			$stat->execute(array(
					'id'=>$area->getId(),
					
				));
			
			$stat = $DB->prepare("INSERT INTO area_history (area_id,  title,description,country_id,parent_area_id,user_account_id  , created_at, approved_at, is_deleted) VALUES ".
					"(:area_id,  :title,:description,:country_id,:parent_area_id,:user_account_id, :created_at,:approved_at, '1')");
			$stat->execute(array(
					'area_id'=>$area->getId(),
					'title'=>substr($area->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$area->getDescription(),
					'country_id'=>$area->getCountryId(),
					'parent_area_id'=>$area->getParentAreaId(),
					'user_account_id'=>$creator->getId(),				
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	public function updateFutureEventsCache(AreaModel $area) {
		global $DB;
		$statUpdate = $DB->prepare("UPDATE area_information SET cached_future_events=:count WHERE id=:id");

		$erb = new EventRepositoryBuilder();
		$erb->setArea($area);
		$erb->setIncludeDeleted(false);
		$erb->setAfterNow();
		$count = count($erb->fetchAll());

		$statUpdate->execute(array('count'=>$count,'id'=>$area->getId()));
		
	}

	public function updateBoundsCache(AreaModel $area) {
		global $DB;
		$statUpdate = $DB->prepare("UPDATE area_information SET cached_max_lat=:cached_max_lat, ".
				"cached_max_lng=:cached_max_lng, cached_min_lat=:cached_min_lat, cached_min_lng=:cached_min_lng ".
				" WHERE id=:id");

		$vrb = new VenueRepositoryBuilder();
		$vrb->setArea($area);
		$vrb->setIncludeDeleted(false);
		$cachedMinLat = null;
		$cachedMaxLat = null;
		$cachedMinLng = null;
		$cachedMaxLng = null;
		foreach($vrb->fetchAll() as $venue) {
			if ($venue->getLat() && $venue->getLng()) {
				if (is_null($cachedMaxLat)) {
					$cachedMaxLat = $cachedMinLat = $venue->getLat();
					$cachedMaxLng = $cachedMinLng = $venue->getLng();
				} else {
					$cachedMaxLat = max($cachedMaxLat, $venue->getLat());
					$cachedMaxLng = max($cachedMaxLng, $venue->getLng());
					$cachedMinLat = min($cachedMinLat, $venue->getLat());
					$cachedMinLng = min($cachedMinLng, $venue->getLng());
				}
			}
		}
		
		$statUpdate->execute(array(
			'id'=>$area->getId(),
					'cached_max_lat'=>$cachedMaxLat,
					'cached_min_lat'=>$cachedMinLat,
					'cached_max_lng'=>$cachedMaxLng,
					'cached_min_lng'=>$cachedMinLng,
				));
		
	}

	public function doesCountryHaveAnyNotDeletedAreas(SiteModel $site, CountryModel $country) {
		global $DB;
		$stat = $DB->prepare("SELECT id FROM area_information WHERE site_id=:site_id AND country_id=:country_id AND is_deleted='0'");
		$stat->execute(array(
			'site_id'=>$site->getId(),
			'country_id'=>$country->getId(),
		));
		return ($stat->rowCount() > 0);
	}


	public function markDuplicate(AreaModel $duplicateArea, AreaModel $originalArea, UserAccountModel $user=null) {
		global $DB;

		if ($duplicateArea->getId() == $originalArea->getId()) return;

		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("UPDATE area_information  SET ".
				" is_deleted='1', is_duplicate_of_id= :is_duplicate_of_id ".
				" WHERE id=:id");
			$stat->execute(array(
				'id'=>$duplicateArea->getId(),
				'is_duplicate_of_id'=>$originalArea->getId(),
			));

			$stat = $DB->prepare("INSERT INTO area_history (area_id,  title,description,country_id,parent_area_id,user_account_id  , created_at, approved_at, is_deleted, is_duplicate_of_id) VALUES ".
				"(:area_id,  :title,:description,:country_id,:parent_area_id,:user_account_id, :created_at,:approved_at, '1', :is_duplicate_of_id)");
			$stat->execute(array(
				'area_id'=>$duplicateArea->getId(),
				'title'=>substr($duplicateArea->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
				'description'=>$duplicateArea->getDescription(),
				'country_id'=>$duplicateArea->getCountryId(),
				'parent_area_id'=>$duplicateArea->getParentAreaId(),
				'user_account_id'=>($user ? $user->getId() : null),
				'created_at'=>\TimeSource::getFormattedForDataBase(),
				'approved_at'=>\TimeSource::getFormattedForDataBase(),
				'is_duplicate_of_id'=>$originalArea->getId(),
			));

			// Move Venues
			$vrb = new VenueRepositoryBuilder();
			$vrb->setArea($duplicateArea);
			$statUpdate = $DB->prepare("UPDATE venue_information SET area_id=:area_id WHERE id=:id");
			$statInsert = $DB->prepare("INSERT INTO venue_history (venue_id, title, lat,lng,country_id, ".
					"area_id, description, user_account_id  , created_at,approved_at,address,address_code,is_duplicate_of_id,is_deleted) VALUES ".
					"(:venue_id, :title, :lat, :lng, :country_id,".
					":area_id,:description,  :user_account_id  , :created_at,:approved_at,:address,:address_code,:is_duplicate_of_id,:is_deleted)");
			foreach($vrb->fetchAll() as $venue) {
				$statUpdate->execute(array('id'=>$venue->getId(),'area_id'=>$originalArea->getId()));
				$statInsert->execute(array(
					'venue_id'=>$venue->getId(),
					'title'=>$venue->getTitle(),
					'lat'=>$venue->getLat(),
					'lng'=>$venue->getLng(),
					'description'=>$venue->getDescription(),
					'address'=>$venue->getAddress(),
					'address_code'=>$venue->getAddressCode(),
					'user_account_id'=>($user?$user->getId():null),
					'country_id'=>$venue->getCountryId(),
					'is_duplicate_of_id'=>$venue->getIsDuplicateOfId(),
					'area_id'=>$originalArea->getId(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
					'is_deleted'=>($venue->getIsdeleted()?1:0),
				));
			}

			// Move Events
			// TODO




			// Move Child Areas
			// TODO





			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

}
