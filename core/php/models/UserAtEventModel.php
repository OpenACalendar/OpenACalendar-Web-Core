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
class UserAtEventModel {
	

	protected $user_account_id;
	protected $event_id;
	protected $is_plan_attending = false;
	protected $is_plan_maybe_attending = false;
	protected $is_plan_not_attending = false;
	protected $is_plan_public = false;
	
	protected $user_username;


	public function setFromDataBaseRow($data) {
		$this->user_account_id = $data['user_account_id'];
		$this->event_id = $data['event_id'];
		$this->is_plan_attending = (boolean)$data['is_plan_attending'];
		$this->is_plan_maybe_attending = (boolean)$data['is_plan_maybe_attending'];
		$this->is_plan_not_attending = (boolean)$data['is_plan_not_attending'];
		$this->is_plan_public = (boolean)$data['is_plan_public'];
		$this->user_username = isset($data['user_username']) ? $data['user_username'] : 0;
	}
	
	public function getUserAccountId() {
		return $this->user_account_id;
	}

	public function setUserAccountId($user_account_id) {
		$this->user_account_id = $user_account_id;
	}

	public function getEventId() {
		return $this->event_id;
	}

	public function setEventId($event_id) {
		$this->event_id = $event_id;
	}

    /**
     * @return boolean
     */
    public function getIsPlanUnknownAttending()
    {
        return !$this->is_plan_not_attending && !$this->is_plan_maybe_attending && !$this->is_plan_attending;
    }


    public function setIsPlanUnknownAttending($value)
    {
        if ($value)
        {
            $this->is_plan_attending = false;
            $this->is_plan_maybe_attending = false;
            $this->is_plan_not_attending = false;
        }
    }

	public function getIsPlanAttending() {
		return $this->is_plan_attending;
	}

	public function setIsPlanAttending($is_plan_attending) {
		$this->is_plan_attending = $is_plan_attending;
        if ($is_plan_attending) {
            $this->is_plan_not_attending = false;
            $this->is_plan_maybe_attending = false;
        }
	}

	public function getIsPlanMaybeAttending() {
		return $this->is_plan_maybe_attending;
	}

	public function setIsPlanMaybeAttending($is_plan_maybe_attending) {
		$this->is_plan_maybe_attending = $is_plan_maybe_attending;
        if ($is_plan_maybe_attending) {
            $this->is_plan_not_attending = false;
            $this->is_plan_attending = false;
        }
	}

    /**
     * @return boolean
     */
    public function getIsPlanNotAttending()
    {
        return $this->is_plan_not_attending;
    }

    /**
     * @param boolean $is_plan_not_attending
     */
    public function setIsPlanNotAttending($is_plan_not_attending)
    {
        $this->is_plan_not_attending = $is_plan_not_attending;
        if ($is_plan_not_attending) {
            $this->is_plan_attending = false;
            $this->is_plan_maybe_attending = false;
        }
    }

	public function getIsPlanPublic() {
		return $this->is_plan_public;
	}

	public function setIsPlanPublic($is_plan_public) {
		$this->is_plan_public = $is_plan_public;
	}

	public function getUserUsername() {
		return $this->user_username;
	}


	
	
	
}


