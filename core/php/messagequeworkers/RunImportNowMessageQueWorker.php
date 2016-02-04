<?php

namespace messagequeworkers;
use repositories\ImportRepository;
use tasks\RunImportsTask;

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
        if ($extension == 'org.openacalendar' && $type == 'ImportSaved') {

            $importrepo = new ImportRepository($this->app);
            $import = $importrepo->loadById($data['id']);

            if ($import) {

                $task = new RunImportsTask($this->app);
                $task->runImport($import);

                return true;
            }

        }
        return false;
    }

}

