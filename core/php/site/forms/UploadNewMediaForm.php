<?php

namespace site\forms;

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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UploadNewMediaForm extends AbstractType{

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('media', 'file', array(
			"mapped" => false, 
			'label'=>'Picture',
			'required'=>false
		));
		
		$builder->add('title', 'text', array(
			'label'=>'Title',
			'required'=>false, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));
		
		$builder->add('source_text', 'text', array(
			'label'=>'Source',
			'required'=>false, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));
		$builder->add('sorce_url', 'url', array(
			'label'=>'Source URL',
			'required'=>false, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));


		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			global $CONFIG;
			$form = $event->getForm();
			// URL validation. We really can't do much except verify ppl haven't put a space in, which they might do if they just type in Google search terms (seen it done)
			if (strpos($form->get("sorce_url")->getData(), " ") !== false) {
				$form['sorce_url']->addError(new FormError("Please enter a URL"));
			}
		};

		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);
	}
	
	public function getName() {
		return 'UploadNewMediaForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

