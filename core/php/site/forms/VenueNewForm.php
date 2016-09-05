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


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueNewForm extends \BaseFormWithEditComment {

	protected $timeZoneName;
	
	/** @var SiteModel **/
	protected $site;

    /** @var  CountryModel */
    protected $defaultCountryModel;
	
	function __construct(Application $app, CountryModel $countryModel) {
		parent::__construct($app);
		$this->site = $app['currentSite'];
        $this->defaultCountryModel = $countryModel;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);
		
		$builder->add('title', 'text', array(
				'label'=>'Title',
				'required'=>true, 
				'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
				'attr' => array('autofocus' => 'autofocus')
			));
		
		$builder->add('description', 'textarea', array(
				'label'=>'Description',
				'required'=>false
			));
		
		$builder->add('address', 'textarea', array(
				'label'=>'Address',
				'required'=>false
			));
		
		// TODO use proper label for country
		$builder->add('address_code', 'text', array(
				'label'=>'Postcode',
				'required'=>false
			));
		
		$crb = new CountryRepositoryBuilder($this->app);
		$crb->setSiteIn($this->site);
		$countries = array();
		foreach($crb->fetchAll() as $country) {
			$countries[$country->getId()] = $country->getTitle();
		}
		// TODO if current country not in list add it now
		$builder->add('country_id', 'choice', array(
			'label'=>'Country',
			'choices' => $countries,
			'required' => true,
			'data' => $this->defaultCountryModel->getId(),
            'choices_as_values'=>false,
		));
		
		$builder->add('lat', 'hidden', array());
		$builder->add('lng', 'hidden', array());

	}
	
	public function getName() {
		return 'VenueNewForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}