<?php

namespace repositories\builders;

use models\SiteModel;
use models\UserAccountModel;
use \BaseUserNotificationModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserNotificationRepositoryBuilder  extends BaseRepositoryBuilder {

	/** @var \ExtensionManager **/
	protected $extensionManager;
	
	function __construct(\ExtensionManager $extensionManager) {
		$this->extensionManager = $extensionManager;
	}

	
	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}

	/** @var UserAccountModel **/
	protected $user;
	
	public function setUser(UserAccountModel $user) {
		$this->user = $user;
	}
	
	protected $isOpenBySysAdminsOnly = true;
	
	public function setIsOpenBySysAdminsOnly($value) {
		$this->isOpenBySysAdminsOnly = $value;
	}
	
	protected function build() {

		$this->joins[] = " LEFT JOIN site_information ON site_information.id = user_notification.site_id  ";
		
		if ($this->site) {
			$this->where[] =  " user_notification.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}

		if ($this->user) {
			$this->where[] =  " user_notification.user_id = :user_id ";
			$this->params['user_id'] = $this->user->getId();
		}

		if ($this->isOpenBySysAdminsOnly) {
			$this->where[] = "   ( site_information.is_closed_by_sys_admin = '0' OR site_information.is_closed_by_sys_admin is null ) ";
		}
		
	}
	
	protected function buildStat() {
				global $DB;
		
		
		
		$sql = "SELECT user_notification.*, ".
				"site_information.id AS site_id,  site_information.slug AS site_slug,  site_information.title AS site_title ".
				"FROM user_notification ".
				implode(" ",$this->joins).
				($this->where?" WHERE ".implode(" AND ", $this->where):"").
				" ORDER BY user_notification.created_at DESC ".
				( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();

		$results = array();
		while($data = $this->stat->fetch()) {
			$extension = $this->extensionManager->getExtensionById($data['from_extension_id']);
			if ($extension) {
				$type = $extension->getUserNotificationType($data['from_user_notification_type']);
				if ($type) {
					$site = new SiteModel();
					$site->setId($data['site_id']);
					$site->setSlug($data['site_slug']);
					$site->setTitle($data['site_title']);
					$notification = $type->getNotificationFromData($data, null, $site);
					if ($notification->isValid()) {
						$results[] = $notification;
					}
				}
			}		
		}
		return $results;
		
	}

}

