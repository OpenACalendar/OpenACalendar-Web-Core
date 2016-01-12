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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class GroupNewForm extends AbstractType{

	protected $defaultTitle;

	function __construct($defaultTitle=null)
	{
		$this->defaultTitle = $defaultTitle;
	}


	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('title', 'text', array(
			'label'=>'Title',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
			'attr' => array('autofocus' => 'autofocus'),
			'data'=>$this->defaultTitle,

		));
		
		$builder->add('description', 'textarea', array(
			'label'=>'Description',
			'required'=>false
		));
		$builder->add('url', 'url', array(
			'label'=>'URL',
			'required'=>false, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));
		
		$builder->add('twitterUsername', 'text', array(
			'label'=>'Twitter',
			'required'=>false
		));

		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			$form = $event->getForm();
			// Title
			if (!trim($form->get('title')->getData())) {
				$form->get('title')->addError( new FormError("Please enter a title"));
			}
			// URL validation. We really can't do much except verify ppl haven't put a space in, which they might do if they just type in Google search terms (seen it done)
			if (strpos($form->get("url")->getData(), " ") !== false) {
				$form['url']->addError(new FormError("Please enter a URL"));
			}
		};

		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);
	}
	
	public function getName() {
		return 'GroupNewForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}


