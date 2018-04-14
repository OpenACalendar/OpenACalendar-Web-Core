<?php

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class OAC_Swift_Mailer extends Swift_Mailer
{

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {

        // https://github.com/symfony/swiftmailer-bundle/issues/39
        // https://github.com/OpenACalendar/OpenACalendar-Web-Core/issues/767
        try {
            parent::send($message, $failedRecipients);
        } catch (Exception $e) {
            parent::getTransport()->stop();
            parent::getTransport()->start();
            parent::send($message, $failedRecipients);
        }

    }

}

