<?php

namespace site\forms;

use models\CountryModel;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\SiteModel;
use repositories\builders\CountryRepositoryBuilder;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueNewForm extends \BaseFormWithEditComment {


	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);
		
		$builder->add('title', TextType::class, array(
				'label'=>'Title',
				'required'=>true, 
				'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
				'attr' => array('autofocus' => 'autofocus')
			));
		
		$builder->add('description', TextareaType::class, array(
				'label'=>'Description',
				'required'=>false
			));
		
		$builder->add('address', TextareaType::class, array(
				'label'=>'Address',
				'required'=>false
			));
		
		// TODO use proper label for country
		$builder->add('address_code', TextType::class, array(
				'label'=>'Postcode',
				'required'=>false
			));
		
		$crb = new CountryRepositoryBuilder($options['app']);
		$crb->setSiteIn($options['app']['currentSite']);
		$countries = array();
		foreach($crb->fetchAll() as $country) {
            $countries[$country->getTitle()] = $country->getId();
		}
		// TODO if current country not in list add it now
		$builder->add('country_id', ChoiceType::class, array(
			'label'=>'Country',
			'choices' => $countries,
			'required' => true,
			'data' => $options['defaultCountryModel']->getId(),
            'choices_as_values' => true,
		));
		
		$builder->add('lat', HiddenType::class, array());
		$builder->add('lng', HiddenType::class, array());

	}
	
	public function getName() {
		return 'VenueNewForm';
	}


	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'app' => null,
			'defaultCountryModel' => null,
		));
	}
	
}