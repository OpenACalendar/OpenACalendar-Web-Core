<?php

namespace site\forms;


use BaseFormWithEditComment;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventDeleteForm extends BaseFormWithEditComment {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);
		
		$builder->add("agree",
                CheckboxType::class,
					array(
						'required'=>true,
						'label'=>'Delete this event'
					)
			    );
		
	}
	
	public function getName() {
		return 'EventDeleteForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}