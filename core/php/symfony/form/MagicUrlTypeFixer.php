<?php

namespace symfony\form;


use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MagicUrlTypeFixer implements EventSubscriberInterface
{


	public function onSubmit(FormEvent $event)
	{
		$data = trim($event->getData());

		// A user has just put a email address in
		if (filter_var($data, FILTER_VALIDATE_EMAIL)) {
			$event->setData("mailto:".$data);
			return;
		}

		// this is already a valid mailto: link ..... it's fine!
		if (strtolower(substr($data, 0, 7)) == "mailto:" &&  filter_var(substr($data,8), FILTER_VALIDATE_EMAIL)) {
			return;
		}

		if ($data && !preg_match('~^\w+://~', $data)) {
			$event->setData('http://'.$data);
			return;
		}


	}

	/**
	 * Alias of {@link onSubmit()}.
	 *
	 * @deprecated Deprecated since version 2.3, to be removed in 3.0. Use
	 *             {@link onSubmit()} instead.
	 */
	public function onBind(FormEvent $event)
	{
		$this->onSubmit($event);
	}

	public static function getSubscribedEvents()
	{
		return array(FormEvents::SUBMIT => 'onSubmit');
	}
}

