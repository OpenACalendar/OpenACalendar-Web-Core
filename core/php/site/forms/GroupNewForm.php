<?php

namespace site\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class GroupNewForm extends AbstractType{


	public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('title', TextType::class, array(
            'label'=>'Title',
            'required'=>true,
            'constraints' => new \Symfony\Component\Validator\Constraints\Length(array('min'=>1,'max'=>VARCHAR_COLUMN_LENGTH_USED)),
            'attr' => array('autofocus' => 'autofocus'),
            'data'=>$options['defaultTitle'],
        ));

        $builder->add('description', TextareaType::class, array(
            'label'=>'Description',
            'required'=>false
        ));
        $builder->add('url', UrlType::class, array(
            'label'=>'URL',
            'required'=>false,
            'constraints' => new \Symfony\Component\Validator\Constraints\Length(array('min'=>1,'max'=>VARCHAR_COLUMN_LENGTH_USED)),
        ));
		
		$builder->add('twitterUsername', TextType::class, array(
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
		$builder->addEventListener(FormEvents::POST_SUBMIT, $myExtraFieldValidator);
	}
	
	public function getName() {
		return 'GroupNewForm';
	}


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'defaultTitle' => null,
        ));
    }
	
}


