<?php


namespace repositories;

use models\TagModel;
use models\SiteModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagRepository {
	
	
	public function create(TagModel $tag, SiteModel $site, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM tag_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$tag->setSlug($data['c'] + 1);
			
			$stat = $DB->prepare("INSERT INTO tag_information (site_id, slug, title,description,created_at,approved_at) ".
					"VALUES (:site_id, :slug, :title, :description, :created_at,:approved_at) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$tag->getSlug(),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
					'approved_at'=>\TimeSource::getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$tag->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO tag_history (tag_id, title, description, user_account_id  , created_at, approved_at, is_new) VALUES ".
					"(:tag_id, :title, :description, :user_account_id  , :created_at, :approved_at, '1')");
			$stat->execute(array(
					'tag_id'=>$tag->getId(),
					'title'=>substr($tag->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED),
					'description'=>$tag->getDescription(),
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
		$stat = $DB->prepare("SELECT tag_information.* FROM tag_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$tag = new TagModel();
			$tag->setFromDataBaseRow($stat->fetch());
			return $tag;
		}
	}
	
	
	
}

