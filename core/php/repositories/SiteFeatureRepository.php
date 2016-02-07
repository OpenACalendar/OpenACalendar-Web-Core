<?php


namespace repositories;

use models\SiteModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteFeatureRepository
{

	protected $app;

	function __construct($app)
	{
		$this->app = $app;
	}


	public function getForSiteAsTree(SiteModel $site) {


		$features = array();

		foreach($this->app['extensions']->getExtensionsIncludingCore() as $ext) {
			foreach($ext->getSiteFeatures($site) as $feature) {
				if (!isset($features[$feature->getExtensionId()])) {
					$features[$feature->getExtensionId()] = array();
				}
				$features[$feature->getExtensionId()][$feature->getFeatureId()] = $feature;
			}
		}


		$stat = $this->app['db']->prepare("SELECT extension_id,feature_id,is_on FROM site_feature_information WHERE site_id=:site_id");
		$stat->execute(array(
			'site_id'=>$site->getId(),
		));
		while($data = $stat->fetch()) {
			if (isset($features[$data['extension_id']]) && isset($features[$data['extension_id']][$data['feature_id']])) {
				$features[$data['extension_id']][$data['feature_id']]->setOn($data['is_on'] == 1);
			}
		}


		return $features;

	}

	public function getForSiteAsList(SiteModel $site)
	{
		$out = array();
		foreach ($this->getForSiteAsTree($site) as $features) {
			foreach($features as $feature) {
				$out[] = $feature;
			}
		}
		return $out;
	}

	public function doesSiteHaveFeatureByExtensionAndId(SiteModel $siteModel, $extension, $feature) {
		$stat = $this->app['db']->prepare("SELECT is_on FROM site_feature_information WHERE site_id=:site_id AND extension_id=:extension_id AND feature_id=:feature_id");
		$stat->execute(array(
			'site_id'=>$siteModel->getId(),
			'extension_id'=>$extension,
			'feature_id'=>$feature,
		));
		if ($stat->rowCount() == 0) {
			return false;
		} else {
			$data = $stat->fetch();
			return $data['is_on'];
		}
	}

	public  function setFeature(SiteModel $site, \BaseSiteFeature $siteFeature, $value, UserAccountModel $userAccountModel = null) {
		try {
			$this->app['db']->beginTransaction();

			$changeMade = false;

			$stat = $this->app['db']->prepare("SELECT is_on FROM site_feature_information WHERE site_id=:site_id AND extension_id =:extension_id AND feature_id =:feature_id");
			$stat->execute(array(
				'site_id'=>$site->getId(),
				'extension_id'=>$siteFeature->getExtensionId(),
				'feature_id'=>$siteFeature->getFeatureId(),
			));
			if ($stat->rowCount() == 1) {

				$data = $stat->fetch();
				if($data['is_on'] != $value) {


					$stat = $this->app['db']->prepare("UPDATE site_feature_information SET  is_on=:is_on ".
						" WHERE site_id=:site_id AND extension_id =:extension_id AND feature_id =:feature_id ");
					$stat->execute(array(
						'site_id'=>$site->getId(),
						'extension_id'=>$siteFeature->getExtensionId(),
						'feature_id'=>$siteFeature->getFeatureId(),
						'is_on'=>$value?1:0,
					));
					$changeMade = true;

				}

			} else {


				$stat = $this->app['db']->prepare("INSERT INTO site_feature_information (site_id, extension_id, feature_id, is_on) ".
					" VALUES(:site_id, :extension_id, :feature_id, :is_on) ");
				$stat->execute(array(
					'site_id'=>$site->getId(),
					'extension_id'=>$siteFeature->getExtensionId(),
					'feature_id'=>$siteFeature->getFeatureId(),
					'is_on'=>$value?1:0,
				));
				$changeMade = true;
			}

			if ($changeMade) {
				$stat = $this->app['db']->prepare("INSERT INTO site_feature_history (site_id, extension_id, feature_id, is_on, user_account_id, created_at) ".
					" VALUES (:site_id, :extension_id, :feature_id, :is_on, :user_account_id, :created_at)");
				$stat->execute(array(
					'site_id'=>$site->getId(),
					'extension_id'=>$siteFeature->getExtensionId(),
					'feature_id'=>$siteFeature->getFeatureId(),
					'is_on'=>$value?1:0,
					'user_account_id'=>$userAccountModel ? $userAccountModel->getId() : null,
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			}

			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'SiteFeatureSaved', array('site_id'=>$site->getId(), 'feature_extension_id'=>$siteFeature->getExtensionId(), 'feature_id'=>$siteFeature->getFeatureId()));
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}

}
