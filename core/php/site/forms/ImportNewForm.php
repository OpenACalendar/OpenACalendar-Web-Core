<?php

namespace site\forms;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use repositories\builders\CountryRepositoryBuilder;
use models\SiteModel;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
class ImportNewForm extends AbstractType{

	public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('url', UrlType::class, array(
            'label'=>'URL',
            'required'=>true,
            'constraints' => new \Symfony\Component\Validator\Constraints\Length(array('min'=>1,'max'=>VARCHAR_COLUMN_LENGTH_USED)),
        ));

		/**
		$builder->add("is_manual_events_creation",
            CheckboxType::class,
			array(
				'required'=>false,
				'label'=>'Do you want to create events manually from this import?',
			)
		);
		 * **/
			
		$crb = new CountryRepositoryBuilder($options['app']);
		$crb->setSiteIn($options['site']);
		$countries = array();
		$defaultCountry = null;
		foreach($crb->fetchAll() as $country) {
			$countries[$country->getTitle()] = $country->getId();
			if ($defaultCountry == null && in_array($options['timeZoneName'], $country->getTimezonesAsList())) {
				$defaultCountry = $country->getId();
			}	
		}
		// TODO if current country not in list add it now
		$builder->add('country_id', ChoiceType::class, array(
			'label'=>'Country',
			'choices' => $countries,
			'required' => true,
			'data' => $defaultCountry,
            'choices_as_values' => true,
		));


		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			$form = $event->getForm();
			// URL validation. We really can't do much except verify ppl haven't put a space in, which they might do if they just type in Google search terms (seen it done)
			// or no value
			if (strpos($form->get("url")->getData(), " ") !== false || !trim($form->get("url")->getData())) {
				$form['url']->addError(new FormError("Please enter a URL"));
            } else {
                $scheme = parse_url($form->get("url")->getData(), PHP_URL_SCHEME);
                if (strtolower($scheme) != 'http' && strtolower($scheme) != 'https') {
                    $form['url']->addError(new FormError("Only http:// or https:// URL's are allowed."));
                }
            }
		};

		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_SUBMIT, $myExtraFieldValidator);
	}
	
	public function getName() {
		return 'ImportNewForm';
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'timeZoneName' => null,
            'site' => null,
            'app' => null,
        ));
    }



}

