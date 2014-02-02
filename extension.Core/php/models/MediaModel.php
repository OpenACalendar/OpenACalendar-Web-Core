<?php



namespace models;


use Symfony\Component\HttpFoundation\Response;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaModel {
	
	protected $id;
	protected $site_id;
	protected $slug;
	protected $storage_size;
	protected $title;
	protected $source_text;
	protected $source_url;
	protected $md5;
	protected $created_by_user_account_id;
	protected $created_at;
	protected $deleted_by_user_account_id;
	protected $deleted_at;

	public function setFromDataBaseRow($data) {
		$this->id = $data['id'];
		$this->site_id = $data['site_id'];
		$this->slug = $data['slug'];
		$this->storage_size = $data['storage_size'];
		$this->title = $data['title'];
		$this->source_text = $data['source_text'];
		$this->source_url = $data['source_url'];
		$this->md5 = $data['md5'];
		$this->created_at = $data['created_at'];
		$this->created_by_user_account_id = $data['created_by_user_account_id'];
		$this->deleted_at = $data['deleted_at'];
		$this->deleted_by_user_account_id = $data['deleted_by_user_account_id'];
	}
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	public function getSiteId() {
		return $this->site_id;
	}

	public function setSiteId($site_id) {
		$this->site_id = $site_id;
		return $this;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function setSlug($slug) {
		$this->slug = $slug;
		return $this;
	}

	public function getStorageSize() {
		return $this->storage_size;
	}

	public function setStorageSize($storage_size) {
		$this->storage_size = $storage_size;
		return $this;
	}
	
	public function getMd5() {
		return $this->md5;
	}

	public function setMd5($md5) {
		$this->md5 = $md5;
		return $this;
	}

		public static function getAllowedImageExtensions() {
		return array('png','jpeg','jpg','gif');
	}
	
	
	protected $mimeTypes = array(
			'png'=>'image/png',
			'jpeg'=>'image/jpeg',
			'jpg'=>'image/jpeg',
			'gif'=>'image/gif',
		);
	
	

	
	protected function getImageDataAtSize($sizeName, $sizeInt) {
		global $CONFIG;
		// First, look in cache
		$data = $this->getImageDataAtSizeFromCache($sizeName, $sizeInt);
		if ($data) return $data;
		// Second, try and put in Cache
		$this->cacheImageDataAtSize($sizeName, $sizeInt);
		// Now look in cache again, should be there
		$data = $this->getImageDataAtSizeFromCache($sizeName, $sizeInt);
		if ($data) return $data;
		// Caching failed?
		// TODO
	}
	
	protected function cacheImageDataAtSize($sizeName, $sizeInt) {
		global $CONFIG;
		$dirname = $CONFIG->fileStoreLocation. DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR;
		$cacheDirname = $CONFIG->tmpFileCacheLocation. DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.  strtolower($sizeName).DIRECTORY_SEPARATOR;
		if (!file_exists($cacheDirname)) {
			if (!mkdir($cacheDirname, $CONFIG->tmpFileCacheCreationPermissions, true)) {
				return false;
			}
		}
		foreach(MediaModel::getAllowedImageExtensions() as $extension) {
			$filename = $dirname .  $this->getId().".".$extension;
			$cacheFileName = $cacheDirname . $this->getId().".".$extension;
			if (file_exists($filename) && !file_exists($cacheFileName)) {
				$data = getimagesize($filename);
				if (is_array($data)) {
					if ($data[0] < $sizeInt && $data[1] < $sizeInt) {
						copy($filename,$cacheFileName);
						return true;
					} else {
						$scale = max(1,max($data[0]/$sizeInt, $data[1]/$sizeInt));
						$new_width  = intval($data[0]/$scale);
						$new_height = intval($data[1]/$scale);
						switch ($data[2]) {
							case IMAGETYPE_JPEG: case IMAGETYPE_JPEG2000:
								$image = imagecreatefromjpeg($filename);
								break;
							case IMAGETYPE_PNG:
								$image = imagecreatefrompng($filename);
								break;
							case IMAGETYPE_GIF:
								$image = imagecreatefromgif($filename);
								break;						
							default:
								return false;
						}
						$image_p = imagecreatetruecolor($new_width, $new_height);
						if ($data[2] == IMAGETYPE_PNG) {
							imagealphablending($image_p, false);
							imagesavealpha($image_p, true);
							$transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
							imagefilledrectangle($image_p, 0, 0, $new_width, $new_height, $transparent);
						}
						imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $data[0], $data[1]);
						switch ($data[2]) {
							case IMAGETYPE_JPEG: case IMAGETYPE_JPEG2000:
								if (!imagejpeg($image_p, $cacheFileName, $CONFIG->mediaQualityJpeg)) {
									throw new Exception("Creating smaller image failed for some reason!");
								}
								break;
							case IMAGETYPE_PNG:
								if (!imagepng($image_p, $cacheFileName, $CONFIG->mediaQualityPng)) {
									throw new Exception("Creating smaller image failed for some reason!");
								}
								break;
							case IMAGETYPE_GIF:
								if (!imagegif($image_p, $cacheFileName)) {
									throw new Exception("Creating smaller image failed for some reason!");
								}
								break;						
							default:
								return false;
						}
						return true;
					}
				}
			}
		}
		return false;		
	}
	
	protected function getImageDataAtSizeFromCache($sizeName, $sizeInt) {
		global $CONFIG;
		$dirname = $CONFIG->fileStoreLocation. DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR;
		$cacheDirname = $CONFIG->tmpFileCacheLocation. DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.  strtolower($sizeName).DIRECTORY_SEPARATOR;
		foreach(MediaModel::getAllowedImageExtensions() as $extension) {
			$cacheFileName = $cacheDirname . $this->getId().".".$extension;
			if (file_exists($cacheFileName)) {
				return array(
					'type'=>$extension,
					'data'=>file_get_contents($cacheFileName),
				);
			}
		}
	}

	
	public function writeThumbnailImageToWebBrowser($expiresInSeconds) {
		global $CONFIG;
		$data = $this->getImageDataAtSize('thumbnail', $CONFIG->mediaThumbnailSize);
		if ($data) {
			header("Content-Type: ". $this->mimeTypes[$data['type']]);
			if ($expiresInSeconds > 0) {
				header("Cache-Control: cache");
				header("Expires: ".date("r", time() + $expiresInSeconds));
			}
			print $data['data'];
			return true;
		}
		return false;
	}
	
	
	
	public function getResponse($expiresInSeconds) {
		global $CONFIG;
		$dirname = $CONFIG->fileStoreLocation. DIRECTORY_SEPARATOR.'media';
		foreach(MediaModel::getAllowedImageExtensions() as $extension) {
			$filename = $dirname . DIRECTORY_SEPARATOR . $this->getId().".".$extension;
			if (file_exists($filename)) {
				$response = new Response(file_get_contents($filename));
				$response->headers->set('Content-Type', $this->mimeTypes[$extension]);
				$response->setPublic();
				$response->setMaxAge($expiresInSeconds);				
				return $response;
			}
		}
		return false;
	}
	
	
	
	public function getNormalResponse($expiresInSeconds) {
		global $CONFIG;
		$data = $this->getImageDataAtSize('normal', $CONFIG->mediaNormalSize);
		$response = new Response($data['data']);
		$response->headers->set('Content-Type', $this->mimeTypes[$data['type']]);
		$response->setPublic();
		$response->setMaxAge($expiresInSeconds);				
		return $response;
	}
	
	public function getThumbnailResponse($expiresInSeconds) {
		global $CONFIG;
		$data = $this->getImageDataAtSize('thumbnail', $CONFIG->mediaThumbnailSize);
		$response = new Response($data['data']);
		$response->headers->set('Content-Type', $this->mimeTypes[$data['type']]);
		$response->setPublic();
		$response->setMaxAge($expiresInSeconds);				
		return $response;
	}
	
	
	public function deleteFiles() {
		global $CONFIG;
		$dirname = $CONFIG->fileStoreLocation. DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR;
		$cacheNormalDirname = $CONFIG->tmpFileCacheLocation. DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'normal'.DIRECTORY_SEPARATOR;
		$cacheThumbDirname = $CONFIG->tmpFileCacheLocation. DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'thumbnail'.DIRECTORY_SEPARATOR;
		foreach(MediaModel::getAllowedImageExtensions() as $extension) {
			$filename = $dirname .  $this->getId().".".$extension;
			$cacheNormalFileName = $cacheNormalDirname . $this->getId().".".$extension;
			$cacheThumbFileName = $cacheThumbDirname . $this->getId().".".$extension;
			if (file_exists($filename)) {
				@unlink($filename);
			}
			if (file_exists($cacheNormalFileName)) {
				@unlink($cacheNormalFileName);
			}
			if (file_exists($cacheThumbFileName)) {
				@unlink($cacheThumbFileName);
			}
		}
	}
			
	
	
	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	public function getSourceText() {
		return $this->source_text;
	}

	public function setSourceText($source_text) {
		$this->source_text = $source_text;
		return $this;
	}

	public function getSourceUrl() {
		return $this->source_url;
	}

	public function setSourceUrl($source_url) {
		$this->source_url = $source_url;
		return $this;
	}
	public function getCreated_by_user_account_id() {
		return $this->created_by_user_account_id;
	}

	public function setCreated_by_user_account_id($created_by_user_account_id) {
		$this->created_by_user_account_id = $created_by_user_account_id;
		return $this;
	}

	public function getCreated_at() {
		return $this->created_at;
	}

	public function setCreated_at($created_at) {
		$this->created_at = $created_at;
		return $this;
	}

	
	public function getDeleteByUserAccountId() {
		return $this->deleted_by_user_account_id;
	}

	public function setDeletedByUserAccountId($deleted_by_user_account_id) {
		$this->deleted_by_user_account_id = $deleted_by_user_account_id;
		return $this;
	}

	public function getDeletedAt() {
		return $this->deleted_at;
	}

	public function getIsDeleted() {
		return (boolean)$this->deleted_at;
	}

	public function setDeletedAt($deleted_at) {
		$this->deleted_at = $deleted_at;
		return $this;
	}



}



