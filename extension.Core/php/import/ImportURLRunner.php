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
		global $CONFIG;
		
		$importURLRun = new ImportURLRun($importURL);
		
		$i = new ImportURLNotUsHandler($importURLRun);
		if ($i->canHandle()) return $i->handle();

		// URL Rewriting handlers
		$i = new ImportURLMeetupHandler($importURLRun);
		if ($i->canHandle()) $i->handle();
		
		$i = new ImportURLEventbriteHandler($importURLRun);
		if ($i->canHandle()) $i->handle();
		
		$i = new ImportURLLanyardHandler($importURLRun);
		if ($i->canHandle()) $i->handle();
		
		// actual importer handlers
		$i = new ImportURLICalHandler($importURLRun);
		if ($i->canHandle()) return $i->handle();
		
		// Log that couldn't handle feed	
		$iurlr = new ImportURLResultModel();
		$iurlr->setImportUrlId($importURL->getId());
		$iurlr->setIsSuccess(false);
		$iurlr->setMessage("Did not recognise data");
		$iurlrRepo = new ImportURLResultRepository();
		$iurlrRepo->create($iurlr);
	}
	
	
	

	
}


