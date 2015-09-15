<?php


namespace repositories;

use dbaccess\VenueDBAccess;
use dbaccess\AreaDBAccess;
use dbaccess\EventDBAccess;
use models\AreaEditMetaDataModel;
use models\AreaModel;
use models\EventEditMetaDataModel;
use models\SiteModel;
use models\UserAccountModel;
use models\CountryModel;
use models\VenueEditMetaDataModel;
use repositories\builders\AreaRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AreaRepository {

	/** @var  \dbaccess\AreaDBAccess */
	protected $areaDBAccess;

	function __construct()
	{
		global $DB, $USERAGENT;
		$this->areaDBAccess = new AreaDBAccess($DB, new \TimeSource(), $USERAGENT);
	}
	
	public function create(AreaModel $area, AreaModel $parentArea = null, SiteModel $site, CountryModel $country, UserAccountModel $creator = null) {
		global $DB, $EXTENSIONHOOKRUNNER;

		$EXTENSIONHOOKRUNNER->beforeAreaSave($area,$creator);

		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM area_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$area->setSlug($data['c'] + 1);
			
			if ($parentArea) $area->setParentAreaId($parentArea->getId());
			
			$stat = $DB->prepare("INSERT INTO area_information (site_id, slug, title,description,country_id,parent_area_id,created_at,approved_at,cache_area_has_parent_generated, is_deleted) ".
					"VALUES (:site_id, :slug, :title,:description,:country_id,:parent_area_id,:created_at,:approved_at,:cache_area_has_parent_generated, '0') RETURNING id");
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
			
			$stat = $DB->prepare("INSERT INTO area_history (area_id,  title,description,country_id,parent_area_id,user_account_id  , created_at, approved_at, is_new, is_deleted) VALUES ".
					"(:area_id,  :title,:description,:country_id,:parent_area_id,:user_account_id, :created_at,:approved_at,'1','0')");
			$stat->execute(array(
					'area_id'=>$area->getId(),
					'title'=>substr($area->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$area->getDescription(),
					'country_id'=>$country->getId(),
					'parent_area_id'=>($parentArea ? $parentArea->getId() : null),
					'user_account_id'=>($creator ? $creator->getId() : null),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
			
	}




	public function loadBySlug(SiteModel $site, $slug) {
		return $this->loadBySiteIDAndAreaSlug($site->getId(), $slug);
	}

	public function loadBySiteIDAndAreaSlug($siteID, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT area_information.* FROM area_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$siteID, 'slug'=>$slug ));
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
	 * @deprecated
	 */
	public function edit(AreaModel $area, UserAccountModel $user) {
		$areaEditMetaDataModel = new AreaEditMetaDataModel();
		$areaEditMetaDataModel->setUserAccount($user);
		$this->editWithMetaData($area, $areaEditMetaDataModel);
	}

	/**
	 * This will undelete the area to.
	 */
	public function editWithMetaData(AreaModel $area, AreaEditMetaDataModel $areaEditMetaDataModel) {
		global $DB, $EXTENSIONHOOKRUNNER;
		$EXTENSIONHOOKRUNNER->beforeAreaSave($area,$areaEditMetaDataModel->getUserAccount());
		try {
			$DB->beginTransaction();

			$area->setIsDeleted(false);

			$fields = array('title','description','is_deleted');

			$this->areaDBAccess->update($area, $fields, $areaEditMetaDataModel);

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	/**
	 * @deprecated
	 */
	public function editParentArea(AreaModel $area, UserAccountModel $user) {
		$areaEditMetaDataModel = new AreaEditMetaDataModel();
		$areaEditMetaDataModel->setUserAccount($user);
		$this->editParentAreaWithMetaData($area, $areaEditMetaDataModel);
	}

	public function editParentAreaWithMetaData(AreaModel $area, AreaEditMetaDataModel $areaEditMetaDataModel) {
		global $DB, $EXTENSIONHOOKRUNNER;
		$EXTENSIONHOOKRUNNER->beforeAreaSave($area,$areaEditMetaDataModel->getUserAccount());
		if ($area->getIsDeleted()) {
			throw new \Exception("Can't edit deleted area!");
		}
		try {
			$DB->beginTransaction();

			$this->areaDBAccess->update($area, array('parent_area_id'), $areaEditMetaDataModel);

			// new must clear caches
			$this->deleteParentCacheForArea($area);

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	/**
	 * @deprecated
	 */
	public function delete(AreaModel $area, UserAccountModel $user) {
		$areaEditMetaDataModel = new AreaEditMetaDataModel();
		$areaEditMetaDataModel->setUserAccount($user);
		$this->deleteWithMetaData($area, $areaEditMetaDataModel);
	}

	public function deleteWithMetaData(AreaModel $area, AreaEditMetaDataModel $areaEditMetaDataModel) {
		global $DB, $EXTENSIONHOOKRUNNER;
		$EXTENSIONHOOKRUNNER->beforeAreaSave($area,$areaEditMetaDataModel->getUserAccount());
		if ($area->getIsDeleted()) {
			throw new \Exception("Can't delete deleted area!");
		}
		try {
			$DB->beginTransaction();

			$area->setIsDeleted(true);
			$this->areaDBAccess->update($area, array('is_deleted'), $areaEditMetaDataModel);
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}

	/**
	 * @deprecated
	 */
	public function undelete(AreaModel $area, UserAccountModel $user) {
		$areaEditMetaDataModel = new AreaEditMetaDataModel();
		$areaEditMetaDataModel->setUserAccount($user);
		$this->undeleteWithMetaData($area, $user);
	}

	public function undeleteWithMetaData(AreaModel $area, AreaEditMetaDataModel $areaEditMetaDataModel) {
		global $DB, $EXTENSIONHOOKRUNNER;
		$EXTENSIONHOOKRUNNER->beforeAreaSave($area,$areaEditMetaDataModel->getUserAccount());
		try {
			$DB->beginTransaction();

			$area->setIsDeleted(false);
			$this->areaDBAccess->update($area, array('is_deleted'), $areaEditMetaDataModel);

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
		$erb->setIncludeCancelled(false);
		$erb->setAfterNow();
		$count = count($erb->fetchAll());

		$statUpdate->execute(array('count'=>$count,'id'=>$area->getId()));

		$area->setCachedFutureEvents($count);
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

	/**
	 * @deprecated
	 */
	public function markDuplicate(AreaModel $duplicateArea, AreaModel $originalArea, UserAccountModel $user=null) {
		$areaEditMetaDataModel = new AreaEditMetaDataModel();
		$areaEditMetaDataModel->setUserAccount($user);
		$this->markDuplicateWithMetaData($duplicateArea, $originalArea, $areaEditMetaDataModel);

	}

	public function markDuplicateWithMetaData(AreaModel $duplicateArea, AreaModel $originalArea, AreaEditMetaDataModel $areaEditMetaDataModel) {
		global $DB;

		if ($duplicateArea->getId() == $originalArea->getId()) return;

		try {
			$DB->beginTransaction();


			$duplicateArea->setIsDuplicateOfId($originalArea->getId());
			$duplicateArea->setIsDeleted(true);
			$this->areaDBAccess->update($duplicateArea, array('is_duplicate_of_id','is_deleted'), $areaEditMetaDataModel);


			// Move Venues
			$venueDBAccess = new VenueDBAccess($DB, new \TimeSource());
			$vrb = new VenueRepositoryBuilder();
			$vrb->setArea($duplicateArea);
			$venueEditMetaData = new VenueEditMetaDataModel();
			$venueEditMetaData->setForSecondaryEditFromPrimaryEditMeta($areaEditMetaDataModel);
			foreach($vrb->fetchAll() as $venue) {
				$venue->setAreaId($originalArea->getId());
				$venueDBAccess->update($venue, array('area_id'),$venueEditMetaData);
			}

			// Move Events
			$eventRepoBuilder = new EventRepositoryBuilder();
			$eventRepoBuilder->setArea($duplicateArea);
			$eventDBAccess = new EventDBAccess($DB, new \TimeSource());
			$eventEditMetaData = new EventEditMetaDataModel();
			$eventEditMetaData->setForSecondaryEditFromPrimaryEditMeta($areaEditMetaDataModel);
			foreach($eventRepoBuilder->fetchAll() as $event) {
				// Check Area actually matches here because we may get events at a venue.
				// Based on the order we do things in (ie Move Venue, Move Event) we shouldn't but let's be safe.
				if ($event->getAreaId() == $duplicateArea->getId() && $event->getVenueId() == null) {
					$event->setAreaId($originalArea->getId());
					$eventDBAccess->update($event, array('area_id'), $eventEditMetaData);
				}
			}

			// Move Child Areas
			$areaRepoBuilder = new AreaRepositoryBuilder();
			$areaRepoBuilder->setParentArea($duplicateArea);
			$areaRepoBuilder->setIncludeParentLevels(0);
			$flag = false;
			foreach($areaRepoBuilder->fetchAll() as $area) {
				// lets just double check we haven't got any child areas.
				if ($area->getParentAreaId() == $duplicateArea->getId()) {
					$area->setParentAreaId($originalArea->getId());
					$this->areaDBAccess->update($area, array('parent_area_id'), $areaEditMetaDataModel);
					$flag = true;
				}
			}
			if ($flag) {
				// now must clear caches
				$this->deleteParentCacheForArea($originalArea);
				$this->deleteParentCacheForArea($duplicateArea);
			}

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}


	/**
	 *
	 * Delete Parent Cache for Area and any children areas to.
	 *
	 * If there is an area Tree A -> B -> C and this function is called with B should delete C to.
	 * This is because the cache for C includes which parents it has and that might have just changed!
	 *
	 * @param AreaModel $area
	 */
	protected function deleteParentCacheForArea(AreaModel $area) {
		global $DB;
		// TODO clear for this area only - for now clear all.
		$DB->prepare("DELETE FROM cached_area_has_parent")->execute();
		$DB->prepare("UPDATE area_information SET cache_area_has_parent_generated='f'")->execute();
	}

	/**
	 *
	 * @TODO This could be improved, At the moment it sets any events with this area to no area but it could set them to area of parent (if any).
	 * (Same for setting parent_area to NULL)
	 * Have to be careful of rewriting history if we do that. Create Edinburgh, Create Stockbridge as child, Create Scotland and set as parent of Edinburgh. Now purge Edinburgh.
	 * If just did "update area set parent_area_id=X where parent_area_id=Y" it will look as if Stockbridge was set as a child of Scotland BEFORE Scotland was created.
	 *
	 * @param VenueModel $venue
	 * @throws \Exception
	 * @throws Exception
	 */
	public function purge(AreaModel $area) {
		global $DB;
		try {
			$DB->beginTransaction();

			$this->deleteParentCacheForArea($area);

			$stat = $DB->prepare("UPDATE event_history SET area_id = NULL, area_id_changed = 0 WHERE area_id=:id");
			$stat->execute(array('id'=>$area->getId()));

			$stat = $DB->prepare("UPDATE event_information SET area_id = NULL WHERE area_id=:id");
			$stat->execute(array('id'=>$area->getId()));

			$stat = $DB->prepare("UPDATE area_history SET parent_area_id = NULL, parent_area_id_changed=0 WHERE parent_area_id=:id");
			$stat->execute(array('id'=>$area->getId()));

			$stat = $DB->prepare("UPDATE area_information SET parent_area_id = NULL WHERE parent_area_id=:id");
			$stat->execute(array('id'=>$area->getId()));

			$stat = $DB->prepare("UPDATE area_history SET is_duplicate_of_id = NULL, is_duplicate_of_id_changed = 0 WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$area->getId()));

			$stat = $DB->prepare("UPDATE area_information SET is_duplicate_of_id = NULL WHERE is_duplicate_of_id=:id");
			$stat->execute(array('id'=>$area->getId()));

			$stat = $DB->prepare("DELETE FROM area_history WHERE area_id=:id");
			$stat->execute(array('id'=>$area->getId()));

			$stat = $DB->prepare("DELETE FROM area_information WHERE id=:id");
			$stat->execute(array('id'=>$area->getId()));

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
			throw $e;
		}

	}


}
