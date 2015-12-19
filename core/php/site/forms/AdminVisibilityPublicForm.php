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
class AdminVisibilityPublicForm extends AbstractType{

	/** @var \Config **/
	protected $config;
	
	function __construct(\Config $config) {
		$this->config = $config;
	}

	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('is_web_robots_allowed', 'checkbox', array(
			'label'=>'Allow search engines to list',
			'required'=>false
		));
		
		if (!$this->config->isSingleSiteMode) {
			$builder->add('is_listed_in_index', 'checkbox', array(
				'label'=>'List is directory for others to discover',
				'required'=>false
			));
		}
		
		
	}
	
	public function getName() {
		return 'AdminVisibilityPublicForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}