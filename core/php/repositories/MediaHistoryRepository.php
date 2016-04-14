<?php

namespace repositories;

use models\MediaModel;
use models\MediaHistoryModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaHistoryRepository {

    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

	
	public function ensureChangedFlagsAreSet(MediaHistoryModel $mediaHistory) {

		// do we already have them?
		if (!$mediaHistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $this->app['db']->prepare("SELECT * FROM media_history WHERE media_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC");
		$stat->execute(array('id'=>$mediaHistory->getId(),'at'=>$mediaHistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$mediaHistory->setChangedFlagsFromNothing();
		} else {
			while($mediaHistory->isAnyChangeFlagsUnknown() && $lastHistoryData = $stat->fetch()) {
				$lastHistory = new MediaHistoryModel();
				$lastHistory->setFromDataBaseRow($lastHistoryData);
				$mediaHistory->setChangedFlagsFromLast($lastHistory);
			}
		}


		// Save back to DB
		$sqlFields = array();
		$sqlParams = array(
			'id'=>$mediaHistory->getId(),
			'created_at'=>$mediaHistory->getCreatedAt()->format("Y-m-d H:i:s"),
			'is_new'=>$mediaHistory->getIsNew()?1:0,
		);

		if ($mediaHistory->getTitleChangedKnown()) {
			$sqlFields[] = " title_changed = :title_changed ";
			$sqlParams['title_changed'] = $mediaHistory->getTitleChanged() ? 1 : -1;
		}
		if ($mediaHistory->getSourceTextChangedKnown()) {
			$sqlFields[] = " source_text_changed = :source_text_changed ";
			$sqlParams['source_text_changed'] = $mediaHistory->getSourceTextChanged() ? 1 : -1;
		}

		if ($mediaHistory->getSourceURLChangedKnown()) {
			$sqlFields[] = " source_url_changed = :source_url_changed ";
			$sqlParams['source_url_changed'] = $mediaHistory->getSourceURLChanged() ? 1 : -1;
		}

		$statUpdate = $this->app['db']->prepare("UPDATE media_history SET ".
			" is_new = :is_new, ".
			implode(" , ",$sqlFields).
			" WHERE media_id = :id AND created_at = :created_at");
		$statUpdate->execute($sqlParams);
	}
	
}


