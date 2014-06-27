<?php
namespace import;


use models\ImportURLModel;
use models\ImportURLResultModel;
use repositories\ImportURLResultRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLRunner {
	
	public function go(ImportURLModel $importURL) {				
		global $app;
		
		$importURLRun = new ImportURLRun($importURL);
		$handlers = array();
		
		// Get
		foreach($app['extensions']->getExtensionsIncludingCore() as $extension) {
			foreach($extension->getImportURLHandlers() as $handler) {
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
			$handler->setImportURLRun($importURLRun);
			if ($handler->canHandle()) {
				if ($handler->isStopAfterHandling()) {
					return $handler->handle();
				} else {
					$handler->handle();
				}
			}
		}
		
		// Log that couldn't handle feed	
		$iurlr = new ImportURLResultModel();
		$iurlr->setImportUrlId($importURL->getId());
		$iurlr->setIsSuccess(false);
		$iurlr->setMessage("Did not recognise data");
		$iurlrRepo = new ImportURLResultRepository();
		$iurlrRepo->create($iurlr);
	}
	
	
	

	
}


