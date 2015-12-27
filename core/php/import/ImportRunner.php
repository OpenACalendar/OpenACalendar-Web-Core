<?php
namespace import;


use models\ImportModel;
use models\ImportResultModel;
use repositories\ImportResultRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportRunner {
	
	public function go(ImportModel $importURL) {
		global $app;
		
		$iurlrRepo = new ImportResultRepository();
		$importURLRun = new ImportRun($importURL);
		$handlers = array();
		
		// Get
		foreach($app['extensions']->getExtensionsIncludingCore() as $extension) {
			foreach($extension->getImportHandlers() as $handler) {
				$handlers[] = $handler;
			}
		}
		
		// Sort
		usort($handlers, function($a, $b) {
			if ($a->getSortOrder() == $b->getSortOrder()) {
				return 0;
			} else if ($a->getSortOrder() > $b->getSortOrder()) {
				return 1;
			} else if ($a->getSortOrder() < $b->getSortOrder()) {
				return -1;
			}
		});

		// Run
		foreach($handlers as $handler) {
			$handler->setImportRun($importURLRun);
			if ($handler->canHandle()) {
				if ($handler->isStopAfterHandling()) {
					$iurlr = $handler->handle();
					$iurlr->setImportUrlId($importURL->getId());
					$iurlrRepo->create($iurlr);
					return;
				} else {
					$handler->handle();
				}
			}
		}

		// Log that couldn't handle feed	
		$iurlr = new ImportResultModel();
		$iurlr->setImportUrlId($importURL->getId());
		$iurlr->setIsSuccess(false);
		$iurlr->setMessage("Did not recognise data");
		$iurlrRepo->create($iurlr);			
	}
	
	
	

	
}


