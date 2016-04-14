<?php


namespace models;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaHistoryModel extends MediaModel implements \InterfaceHistoryModel {

	//protected $user_account_id;
	protected $created_at; 
	protected $user_account_id;
	protected $user_account_username;
	
	protected $title_changed = 0;
	protected $source_url_changed = 0;
	protected $source_text_changed = 0;


	protected $is_new = 0;

	public function setFromDataBaseRow($data) {
		$this->id = $data['media_id'];
		$this->slug = isset($data['media_slug']) ? $data['media_slug'] : null;
		$this->title = $data['title'];
		$this->source_text = $data['source_text'];
		$this->source_url = $data['source_url'];
		$utc = new \DateTimeZone("UTC");
		$this->created_at = new \DateTime($data['created_at'], $utc);
		$this->user_account_id = $data['user_account_id'];
		$this->user_account_username = isset($data['user_account_username']) ? $data['user_account_username'] : null;
		$this->title_changed = $data['title_changed'];
		$this->source_text_changed = $data['source_text_changed'];
		$this->source_url_changed = $data['source_url_changed'];
		$this->is_new = isset($data['is_new']) ? $data['is_new'] : 0;
	}
	
	public function getCreatedAt() {
		return $this->created_at;
	}


	public function getCreatedAtTimeStamp() {
		return $this->created_at->getTimestamp();
	}
	
	public function getMediaSlug() {
		return $this->slug;
	}

	public function setMediaSlug($group_slug) {
		$this->slug = $group_slug;
	}

	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getUserAccountUsername() {
		return $this->user_account_username;
	}

	public function setUserAccountUsername($user_account_username) {
		$this->user_account_username = $user_account_username;
	}

	public function isAnyChangeFlagsUnknown() {
		return $this->title_changed == 0 || $this->source_url_changed == 0 || $this->source_text_changed == 0  ;
	}
	
	public function setChangedFlagsFromNothing() {
		$this->title_changed = $this->title ? 1 : -1;
		$this->source_url_changed = $this->source_url ? 1 : -1;
		$this->source_text_changed = $this->source_text ? 1 : -1;
		$this->is_new = 1;
	}
	
	public function setChangedFlagsFromLast(MediaHistoryModel $last) {
		if ($this->title_changed == 0 && $last->title_changed != -2) {
			$this->title_changed  = ($this->title  != $last->title  )? 1 : -1;
		}
		if ($this->source_text_changed == 0 && $last->source_text_changed != -2) {
			$this->source_text_changed  = ($this->source_text  != $last->source_text  )? 1 : -1;
		}
		if ($this->source_url_changed == 0 && $last->source_url_changed != -2) {
			$this->source_url_changed  = ($this->source_url  != $last->source_url  )? 1 : -1;
		}
		$this->is_new = 0;
	}
	
	public function getTitleChanged() {
		return ($this->title_changed > -1);
	}

	public function getTitleChangedKnown() {
		return ($this->title_changed > -2);
	}

	public function getSourceURLChanged() {
		return ($this->source_url_changed > -1);
	}

	public function getSourceURLChangedKnown() {
		return ($this->source_url_changed > -2);
	}

	public function getSourceTextChanged() {
		return ($this->source_text_changed > -1);
	}

	public function getSourceTextChangedKnown() {
		return ($this->source_text_changed > -2);
	}

	public function getIsNew() {
		return ($this->is_new == 1);
	}

	public function getSiteEmailTemplate() {
		return '/email/common/mediaHistoryItem.html.twig';
	}

	public function getSiteWebTemplate() {
		return '/site/common/mediaHistoryItem.html.twig';
	}


	/** @return boolean */
	public function isEqualTo(\InterfaceHistoryModel $otherHistoryModel) {
		return $otherHistoryModel instanceof $this &&
		$otherHistoryModel->getCreatedAtTimeStamp() == $this->getCreatedAtTimeStamp() &&
		$otherHistoryModel->getId() == $this->getId();
	}




}

