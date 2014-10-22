<?php

namespace symfony\form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class MagicUrlType extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addEventSubscriber(new MagicUrlTypeFixer());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParent()
	{
		return 'text';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'url';
	}
}


