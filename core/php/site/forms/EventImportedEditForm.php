<?php

namespace site\forms;

use repositories\SiteFeatureRepository;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\SiteModel;
use repositories\builders\CountryRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
class EventImportedEditForm extends AbstractType{


    public function buildForm(FormBuilderInterface $builder, array $options) {

        $siteFeatureRepo = new SiteFeatureRepository($options['app']);
        $siteFeaturePhysicalEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($options['site'],'org.openacalendar','PhysicalEvents');
        $siteFeatureVirtualEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($options['site'],'org.openacalendar','VirtualEvents');


        $crb = new CountryRepositoryBuilder($options['app']);
        $crb->setSiteIn($options['site']);
		$countries = array();
		foreach($crb->fetchAll() as $country) {
			$countries[$country->getTitle()] = $country->getId();
		}
		// TODO if current country not in list add it now
		$builder->add('country_id', ChoiceType::class, array(
			'label'=>'Country',
			'choices' => $countries,
			'required' => true,
            'choices_as_values' => true,
		));
		
		$timezones = array();
		// Must explicetly set name as key otherwise Symfony forms puts an ID in, and that's no good for processing outside form
		foreach($options['site']->getCachedTimezonesAsList() as $timezone) {
			$timezones[$timezone] = $timezone;
		}		// TODO if current timezone not in list add it now
		$builder->add('timezone', ChoiceType::class, array(
			'label'=>'Time Zone',
			'choices' => $timezones,
			'required' => true,
            'choices_as_values' => true,
		));
			
				
		if ($siteFeatureVirtualEvents) {
			
			// if both are an option, user must check which one.
			if ($siteFeaturePhysicalEvents) {
			
				$builder->add("is_virtual",
                    CheckboxType::class,
						array(
							'required'=>false,
							'label'=>'Is event accessible online?'
						)
					);
			}
			
		}

		
		if ($siteFeaturePhysicalEvents) {
			
			// if both are an option, user must check which one.
			if ($siteFeatureVirtualEvents) {
				
				$builder->add("is_physical",
                    CheckboxType::class,
						array(
							'required'=>false,
							'label'=>'Does the event happen at a place?'
						)
					);
				
			}


		}

        foreach($options['customFields'] as $customFieldData) {
            $fieldOptions = $customFieldData['fieldType']->getSymfonyFormOptions($customFieldData['customField']);
            $fieldOptions['mapped'] = false;
            $fieldOptions['data'] = $builder->getData()->getCustomField($customFieldData['customField']);
            $builder->add('custom_' . $customFieldData['customField']->getKey(), $customFieldData['fieldType']->getSymfonyFormType($customFieldData['customField']), $fieldOptions);
        }
		
	}
	
	public function getName() {
		// This is called EventEditForm and not EventImportedEditForm carefully.
		// We want it to imitate the normal edit form so that all JS that relies on the ID continues to work.
		return 'EventEditForm';
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'app' => null,
            'site' => null,
            'timeZoneName' => null,
            'customFields'=> null,
        ));
    }

}

