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


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportNewForm extends AbstractType{
	
	protected $timeZoneName;
	/** @var SiteModel **/
	protected $site;

    protected $app;

    function __construct(Application $application, SiteModel $site, $timeZoneName) {
        $this->app = $application;
		$this->site = $site;
		$this->timeZoneName = $timeZoneName;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('title', 'text', array(
			'label'=>'Title',
			'required'=>false, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));
		
		$builder->add('url', 'url', array(
			'label'=>'URL',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));

		/**
		$builder->add("is_manual_events_creation",
			"checkbox",
			array(
				'required'=>false,
				'label'=>'Do you want to create events manually from this import?',
			)
		);
		 * **/
			
		$crb = new CountryRepositoryBuilder($this->app);
		$crb->setSiteIn($this->site);
		$countries = array();
		$defaultCountry = null;
		foreach($crb->fetchAll() as $country) {
			$countries[$country->getId()] = $country->getTitle();
			if ($defaultCountry == null && in_array($this->timeZoneName, $country->getTimezonesAsList())) {
				$defaultCountry = $country->getId();
			}	
		}
		// TODO if current country not in list add it now
		$builder->add('country_id', 'choice', array(
			'label'=>'Country',
			'choices' => $countries,
			'required' => true,
			'data' => $defaultCountry,
		));


		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			$form = $event->getForm();
			// URL validation. We really can't do much except verify ppl haven't put a space in, which they might do if they just type in Google search terms (seen it done)
			// or no value
			if (strpos($form->get("url")->getData(), " ") !== false || !trim($form->get("url")->getData())) {
				$form['url']->addError(new FormError("Please enter a URL"));
			}
		};

		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);
	}
	
	public function getName() {
		return 'ImportNewForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

