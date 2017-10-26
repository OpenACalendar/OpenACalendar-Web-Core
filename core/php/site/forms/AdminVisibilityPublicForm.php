<?php

namespace site\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminVisibilityPublicForm extends AbstractType{
    
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('is_web_robots_allowed', CheckboxType::class, array(
			'label'=>'Allow search engines to list',
			'required'=>false
		));
		
		if (!$options['config']->isSingleSiteMode) {
			$builder->add('is_listed_in_index', CheckboxType::class, array(
				'label'=>'List is directory for others to discover',
				'required'=>false
			));
		}
		
		
	}
	
	public function getName() {
		return 'AdminVisibilityPublicForm';
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'config' => null,
        ));
    }
	
}