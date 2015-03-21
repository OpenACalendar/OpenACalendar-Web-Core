<?php

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class  BaseFormWithEditComment  extends AbstractType
{


	protected $formEditComments = false;


	function __construct(Application $application)
	{
		$this->formEditComments = $application['currentSiteFeatures']->has('org.openacalendar', 'EditComments');
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		if ($this->formEditComments) {
			$builder->add('edit_comment', 'text', array(
				'label'=>'Your Comment (public)',
				'required'=>false,
				'mapped'=>false,
			));
		}


	}
}
