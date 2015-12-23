<?php

namespace messagequeworkers;
use repositories\ImportURLRepository;
use tasks\RunImportURLsTask;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RunImportNowMessageQueWorker extends BaseMessageQueWorker {


    function process($extension, $type, $data)
    {
        if ($extension == 'org.openacalendar' && $type == 'ImportURLSaved') {

            $importrepo = new ImportURLRepository();
            $import = $importrepo->loadById($data['id']);

            if ($import) {

                $task = new RunImportURLsTask($this->app);
                $task->runImportURL($import);

                return true;
            }

        }
        return false;
    }

}

