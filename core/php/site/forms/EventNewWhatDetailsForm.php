<?php

namespace site\forms;

use models\NewEventDraftModel;
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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
class EventNewWhatDetailsForm extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {



        $siteFeatureRepo = new SiteFeatureRepository($options['app']);
        $siteFeaturePhysicalEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($options['app']['currentSite'],'org.openacalendar','PhysicalEvents');
        $siteFeatureVirtualEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($options['app']['currentSite'],'org.openacalendar','VirtualEvents');
        // TODO next two should come from config!
        $fieldIsVirtualDefault = false;
        $fieldIsPhysicalDefault  = true;

		$builder->add('summary', TextType::class, array(
			'label'=>'Summary',
			'required'=>true, // TODO THIS IS NOT RESPCTED
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
			'attr' => array('autofocus' => 'autofocus'),
			'data' => $options['newEventDraftModel']->getDetailsValue('event.summary'),
		));
		
		$builder->add('description', TextareaType::class, array(
			'label'=>'Description',
			'required'=>false,
			'data' => $options['newEventDraftModel']->getDetailsValue('event.description'),
		));
		
		
		$builder->add('url', new \symfony\form\MagicUrlType(), array(
			'label'=>'Information Web Page URL',
			'required'=>false,
			'data' => $options['newEventDraftModel']->getDetailsValue('event.url'),
		));
		
		$builder->add('ticket_url', new \symfony\form\MagicUrlType(), array(
			'label'=>'Tickets Web Page URL',
			'required'=>false,
			'data' => $options['newEventDraftModel']->getDetailsValue('event.ticket_url'),
		));

        foreach($options['customFields'] as $customFieldData) {
            $fieldOptions = $customFieldData['fieldType']->getSymfonyFormOptions($customFieldData['customField']);
            $fieldOptions['data'] = $options['newEventDraftModel']->getDetailsValue('event.custom.'.$customFieldData['customField']->getKey());
            $builder->add('custom_' . $customFieldData['customField']->getKey(), $customFieldData['fieldType']->getSymfonyFormType($customFieldData['customField']), $fieldOptions);
        }

		if ($siteFeatureVirtualEvents) {

			//  if both are an option, user must check which one.
			if ($siteFeaturePhysicalEvents) {

				$builder->add("is_virtual",
                    CheckboxType::class,
					array(
						'required'=>false,
						'label'=>'Is event accessible online?',
						'data'=>$options['newEventDraftModel']->hasDetailsValue('event.is_virtual') ?  $options['newEventDraftModel']->getDetailsValue('event.is_virtual') : $fieldIsVirtualDefault,
					)
				);
			} else {
				$builder->add('is_virtual', HiddenType::class, array( 'data' => true, ));
			}

		} else {
			$builder->add('is_virtual', HiddenType::class, array( 'data' => false, ));

		}

		if ($siteFeaturePhysicalEvents) {

			//  if both are an option, user must check which one.
			if ($siteFeatureVirtualEvents) {

				$builder->add("is_physical",
                    CheckboxType::class,
					array(
						'required'=>false,
						'label'=>'Does the event happen at a place?',
						'data'=>$options['newEventDraftModel']->hasDetailsValue('event.is_physical') ?  $options['newEventDraftModel']->getDetailsValue('event.is_physical') : $fieldIsPhysicalDefault,
					)
				);

			} else {
				$builder->add('is_physical', HiddenType::class, array( 'data' => true, ));

			}

		} else {

			$builder->add('is_physical', HiddenType::class, array( 'data' => false, ));
		}

		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			$form = $event->getForm();
			// URL validation. We really can't do much except verify ppl haven't put a space in, which they might do if they just type in Google search terms (seen it done)
			if (strpos($form->get("url")->getData(), " ") !== false) {
				$form['url']->addError(new FormError("Please enter a URL"));
			}
			if (strpos($form->get("ticket_url")->getData(), " ") !== false) {
				$form['ticket_url']->addError(new FormError("Please enter a URL"));
			}
			// Title
			if (!trim($form->get('summary')->getData())) {
				$form->get('summary')->addError( new FormError("Please enter a summary"));
			}
			// TODO it has to be at least one or the other of physical or virtual
		};



		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);	
	}
	
	public function getName() {
		return 'EventNewWhatDetailsForm';
	}


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'app' => null,
            'newEventDraftModel'=>null,
            'customFields'=>null,
        ));
    }


}


