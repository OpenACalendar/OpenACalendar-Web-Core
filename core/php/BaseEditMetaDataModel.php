<?php
use models\UserAccountModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class BaseEditMetaDataModel {


	/** @var  UserAccountModel */
	protected $userAccount;

	protected $editComment;

	/**
     *
     *
     * @TODO This is a bad title as this field is not only used for reverted from history - it's also used by events for editing future events in a chain based on the first edit.
     *
     * @var  \DateTime */
	protected $revertedFromHistoryCreatedAt;

	protected $ip;


	public function setForSecondaryEditFromPrimaryEditMeta(BaseEditMetaDataModel $baseEditMetaDataModel) {
		$this->userAccount = $baseEditMetaDataModel->getUserAccount();
		$this->ip = $baseEditMetaDataModel->getIp();
		// Not doing $this->editComment because the comment applies to the primary edit and wouldn't make sense to apply to primary
	}

	/**
	 * @return mixed
	 */
	public function getEditComment()
	{
		return $this->editComment;
	}

	/**
	 * @param mixed $editComment
	 */
	public function setEditComment($editComment)
	{
		$this->editComment = $editComment;
	}

	/**
	 * @return UserAccountModel
	 */
	public function getUserAccount()
	{
		return $this->userAccount;
	}

	/**
	 * @param UserAccountModel $userAccount
	 */
	public function setUserAccount(UserAccountModel $userAccount = null)
	{
		$this->userAccount = $userAccount;
	}




	/**
	 * @return DateTime
	 */
	public function getRevertedFromHistoryCreatedAt()
	{
		return $this->revertedFromHistoryCreatedAt;
	}

	/**
	 * @param DateTime $revertedFromHistoryCreatedAt
	 */
	public function setRevertedFromHistoryCreatedAt(\DateTime $revertedFromHistoryCreatedAt)
	{
		$this->revertedFromHistoryCreatedAt = $revertedFromHistoryCreatedAt;
	}

	/**
	 * @return mixed
	 */
	public function getIp()
	{
		return $this->ip;
	}


	public function setFromRequest(\Symfony\Component\HttpFoundation\Request $request) {
		$this->ip = $request->server->get('REMOTE_ADDR');
	}




}

