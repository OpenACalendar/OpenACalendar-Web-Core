<?php


namespace models;

use Slugify;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
    protected $slug_human;
	protected $title;
	protected $description;
	protected $is_deleted;
	
	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->title = $data['title'];
		$this->description = $data['description'];
		$this->is_deleted = $data['is_deleted'];
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
	}

	public function getSlug() {
		return $this->slug;
	}

    public function getSlugForUrl() {
        global $app;
        if ($this->slug_human) {
            return $this->slug."-".$this->slug_human;
        } else {
            $slugify = new Slugify($app);
            $extraSlug = $slugify->process($this->title);
            return $this->slug.($extraSlug?"-".$extraSlug:'');
        }
    }

	public function setSlug($slug) {
		$this->slug = $slug;
	}

    /**
     * @param mixed $slug_human
     */
    public function setSlugHuman($slug_human)
    {
        $this->slug_human = $slug_human;
    }

    /**
     * @return mixed
     */
    public function getSlugHuman()
    {
        return $this->slug_human;
    }

    public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	
	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}


	public function getIsDeleted() {
		return $this->is_deleted;
	}

	public function setIsDeleted($is_deleted) {
		$this->is_deleted = $is_deleted;
	}


	
}


