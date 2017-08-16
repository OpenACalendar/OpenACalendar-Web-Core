<?php

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class  BaseFormWithEditComment  extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		if ($options['app']['currentSiteFeatures']->has('org.openacalendar', 'EditComments')) {
			$builder->add('edit_comment', TextType::class, array(
				'label'=>'Your Comment (public)',
				'required'=>false,
				'mapped'=>false,
			));
		}


	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'app' => null,
		));
	}

}
