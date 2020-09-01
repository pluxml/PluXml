<?php

/**
 * Classe plxMedias regroupant les fonctions pour gérer la librairie des medias
 *
 * @package PLX
 * @author	Stephane F, Pedro "P3ter" CADETE
 **/
class plxMedias {

	public $path = null; # chemin vers les médias
	public $dir = null;
	public $aDirs = array(); # liste des dossiers et sous dossiers
	public $aFiles = array(); # liste des fichiers d'un dossier
	public $maxUpload = array(); # valeur upload_max_filesize
	public $maxPost = array(); # valeur post_max_size

	public $img_supported = array('.png', '.gif', '.jpg', '.jpeg', '.bmp', '.webp'); # images formats supported
	public $img_exts = '/\.(jpe?g|png|gif|bmp|webp)$/i';
	public $doc_exts = '/\.(7z|aiff|asf|avi|csv|docx?|epub|fla|flv|gpx|gz|gzip|m4a|m4v|mid|mov|mp3|mp4|mpc|mpe?g|ods|odt|odp|ogg|pdf|pptx?|ppt|pxd|qt|ram|rar|rm|rmi|rmvb|rtf|svg|swf|sxc|sxw|tar|tgz|txt|vtt|wav|webm|wma|wmv|xcf|xlsx?|zip)$/i';

	/**
	 * Constructeur qui initialise la variable de classe
	 *
	 * @param	path	répertoire racine des médias
	 * @param	dir		dossier de recherche
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($path, $dir) {

		# Initialisation
		$this->path = $path;
		$this->dir = $dir;

		# Création du dossier réservé à l'utilisateur connecté s'il n'existe pas
		if(!is_dir($this->path)) {
			if(!mkdir($this->path,0755))
				return plxMsg::Error(L_PLXMEDIAS_MEDIAS_FOLDER_ERR);
		}
		# Création du dossier réservé aux miniatures
		if(!is_dir($this->path.'.thumbs/'.$this->dir)) {
			mkdir($this->path.'.thumbs/'.$this->dir,0755,true);
		}

		$this->aDirs = $this->_getAllDirs($this->path);
		$this->aFiles = $this->_getDirFiles($this->dir);

		# Taille maxi des fichiers
		$maxUpload = strtoupper(ini_get("upload_max_filesize"));
		$this->maxUpload['display'] = str_replace('M', ' Mo', $maxUpload);
		$this->maxUpload['display'] = str_replace('K', ' Ko', $this->maxUpload['display']);
		if(substr_count($maxUpload, 'K')) $this->maxUpload['value'] = str_replace('K', '', $maxUpload) * 1024;
		elseif(substr_count($maxUpload, 'M')) $this->maxUpload['value'] = str_replace('M', '', $maxUpload) * 1024 * 1024;
		elseif(substr_count($maxUpload, 'G')) $this->maxUpload['value'] = str_replace('G', '', $maxUpload) * 1024 * 1024 * 1024;
		else $this->maxUpload['value'] = 0;

		# Taille maxi des données
		$maxPost = strtoupper(ini_get("post_max_size"));
		$this->maxPost['display'] = str_replace('M', ' Mo', $maxPost);
		$this->maxPost['display'] = str_replace('K', ' Ko', $this->maxPost['display']);
		if(substr_count($maxPost, 'K')) $this->maxPost['value'] = str_replace('K', '', $maxPost) * 1024;
		elseif(substr_count($maxPost, 'M')) $this->maxPost['value'] = str_replace('M', '', $maxPost) * 1024 * 1024;
		elseif(substr_count($maxPost, 'G')) $this->maxPost['value'] = str_replace('G', '', $maxPost) * 1024 * 1024 * 1024;
		else $this->maxPost['value'] = 0;

	}

	/**
	 * Méthode récursive qui retourne un tableau de tous les dossiers et sous dossiers dans un répertoire
	 *
	 * @return	array tableau contenant la liste de tous les dossiers et sous dossiers
	 * @author	Stephane F, J.P. "bazooka07" Pourrez
	 **/
	private function _getAllDirs() {
		$result = array();
		$pattern = '*/';
		$offset = strlen($this->path);
		for($i=1; $i<10; $i++) {
			$dirs = glob($this->path . str_repeat($pattern, $i), GLOB_ONLYDIR);
			if(empty($dirs)) { break; }
			foreach($dirs as $d) {
				$path = substr($d, $offset);
				$result[] = array(
					'level' => $i,
					'path'	=> $path
				);
			}
		}
		usort($result, function($a, $b) { return strcasecmp($a['path'], $b['path']); });
		return $result;
	}

	/**
	 * Méthode qui retourne la liste des fichiers d'un répertoire
	 *
	 * @param	dir		répertoire de lecture
	 * @return	array	tableau contenant la liste de tous les fichiers d'un dossier
	 * @author	Stephane F
	 **/
	private function _getDirFiles($dir) {

		$matches = '';

		$src = $this->path.$dir;
		if(!is_dir($src)) return array();

		$defaultSample = PLX_CORE.'admin/theme/images/file.png';
		$offset = strlen($this->path);
		$files = array();
		foreach(array_filter(
			glob($src.'*'),
			function($item) { return !preg_match('@\.tb\.\w+$@', $item); } # On rejette les miniatures
			) as $filename) {
				if(is_dir($filename)) { continue; }

				$thumbInfos = false;
				if(preg_match($this->img_exts, $filename, $matches)) {
					$thumbName = plxUtils::thumbName($filename);
					if(file_exists($thumbName)) {
						$thumbInfos = array(
							'infos' 	=> getimagesize($thumbName),
							'filesize'	=> filesize($thumbName)
						);
					}
					$sample = $this->path. '.thumbs/' .substr($filename, $offset);
					$sampleOk = (
						file_exists($sample) or
						plxUtils::makeThumb(
							$filename,
							$sample
							)
						);
					$imgSize = getimagesize($filename);
				} else {
					$imgSize = false;
				}
				$stats = stat($filename);
				$extension = '.' . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
				if($extension == '.svg') {
					$defaultSample = $filename;
				}
				$files[basename($filename)] = array(
					'.thumb'	=> (!empty($sampleOk)) ? $sample : $defaultSample,
					'name' 		=> basename($filename),
					'path' 		=> $filename,
					'date' 		=> $stats['mtime'],
					'filesize' 	=> $stats['size'],
					'extension'	=> $extension,
					'infos' 	=> $imgSize,
					'thumb' 	=> $thumbInfos
				);
				$sample = '';
				$sampleOk = "";
			}

			ksort($files);
			return $files;
	}

	/**
	 * Méthode qui affiche la liste déroulante des dossiers
	 *
	 * @return	void
	 * @author	Stephane F, Danielsan, J.P. "bazooka07" Pourrez
	 **/
	public function contentFolder() {
		plxUtils::printSelectDir('folder', $this->dir, $this->path, 'no-margin', true, 'folder');
	}

	/**
	 * Méthode qui supprime un fichier (et sa vignette si elle existe dans le cas d'une image)
	 *
	 * @param	files	liste des fichier à supprimer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function deleteFiles($files) {

		$count = 0;
		foreach($files as $file) {
			# protection pour ne pas supprimer un fichier en dehors de $this->path.$this->dir
			$file=basename($file);
			if(!unlink($this->path.$this->dir.$file)) {
				$count++;
			} else {
				# Suppression de la vignette
				if(is_file($this->path.'.thumbs/'.$this->dir.$file))
					unlink($this->path.'.thumbs/'.$this->dir.$file);
				# Suppression de la miniature
				$thumName = plxUtils::thumbName($file);
				if(is_file($this->path.$this->dir.$thumName))
					unlink($this->path.$this->dir.$thumName);
			}
		}

		if(sizeof($files)==1) {
			if($count==0)
				return plxMsg::Info(L_PLXMEDIAS_DELETE_FILE_SUCCESSFUL);
			else
				return plxMsg::Error(L_PLXMEDIAS_DELETE_FILE_ERR);
		}
		else {
			if($count==0)
				return plxMsg::Info(L_PLXMEDIAS_DELETE_FILES_SUCCESSFUL);
			else
				return plxMsg::Error(L_PLXMEDIAS_DELETE_FILES_ERR);
		}
	}


	/**
	 * Méthode récursive qui supprimes tous les dossiers et les fichiers d'un répertoire
	 *
	 * @param	deldir	répertoire de suppression
	 * @return	boolean	résultat de la suppression
	 * @author	Stephane F
	 **/
	private function _deleteDir($deldir) { #fonction récursive

		if(is_dir($deldir) AND !is_link($deldir)) {
			if($dh = opendir($deldir)) {
				while(FALSE !== ($file = readdir($dh))) {
					if($file != '.' AND $file != '..') {
						$this->_deleteDir($deldir.'/'.$file);
					}
				}
				closedir($dh);
			}
			return rmdir($deldir);
		}
		return unlink($deldir);
	}

	/**
	 * Méthode qui supprime un dossier et son contenu
	 *
	 * @param	deleteDir	répertoire à supprimer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function deleteDir($deldir) {

		# suppression du dossier des miniatures et de son contenu
		$this->_deleteDir($this->path.'.thumbs/'.$deldir);

		# suppression du dossier des images et de son contenu
		if($this->_deleteDir($this->path.$deldir))
			return plxMsg::Info(L_PLXMEDIAS_DEL_FOLDER_SUCCESSFUL);
		else
			return plxMsg::Error(L_PLXMEDIAS_DEL_FOLDER_ERR);
	}

	/**
	 * Méthode qui crée un nouveau dossier
	 *
	 * @param	newdir	nom du répertoire à créer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F, J.P. Pourrez (bazooka07)
	 **/
	public function newDir($newdir) {
		if (!empty(trim($newdir))) {
			$mydir = $this->path.$this->dir;
			$mydir .= plxUtils::urlify(trim($newdir));
			// Si le dossier n'existe pas on le créer
			if (!is_dir($mydir)) {
				if (!mkdir($mydir, 0755))
					return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_ERR);
				else
					return plxMsg::Info(L_PLXMEDIAS_NEW_FOLDER_SUCCESSFUL);
			} else {
				return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_EXISTS);
			}
		}
	}

	/**
	 * Méthode qui envoie un fichier sur le serveur
	 *
	 * @param	file	fichier à uploader
	 * @param	resize	taille du fichier à redimensionner si renseigné
	 * @param	thumb	taille de la miniature à créer si renseigné
	 * @return  string	message contenant le résultat de l'envoi du fichier
	 * @author	Stephane F, Pedro "P3ter" CADETE
	 **/
	private function _uploadFile($file, $resize, $thumb) {

		$i = 1;
		$filename = array();

		if($file['name'] == '')
			return false;

		if($file['size'] > $this->maxUpload['value'])
			return L_PLXMEDIAS_WRONG_FILESIZE;

		if(!preg_match($this->img_exts, $file['name']) AND !preg_match($this->doc_exts, $file['name']))
			return L_PLXMEDIAS_WRONG_FILEFORMAT;

		// On teste l'existence du fichier et on formate son nom pour éviter les doublons
		$filename = pathinfo($file['name']);
		$filename['filename'] = plxUtils::urlify($filename['filename']);
		$upFile = $this->path.$this->dir.$filename['filename'].".".$filename['extension'];
		while(file_exists($upFile)) {
			$upFile = $this->path.$this->dir.$filename['filename'].'-'.$i++.".".$filename['extension'];
		}

		if(!move_uploaded_file($file['tmp_name'],$upFile)) { # Erreur de copie
			return L_PLXMEDIAS_UPLOAD_ERR;
		} else { # Ok
			if(preg_match($this->img_exts, $file['name'])) {
				plxUtils::makeThumb($upFile, $this->path.'.thumbs/'.$this->dir.basename($upFile), 48, 48);
				if($resize)
					plxUtils::makeThumb($upFile, $upFile, $resize['width'], $resize['height'], 80);
				if($thumb)
					plxUtils::makeThumb($upFile, plxUtils::thumbName($upFile), $thumb['width'], $thumb['height'], 80);
			}
		}
		return L_PLXMEDIAS_UPLOAD_SUCCESSFUL;
	}

	/**
	 * Méthode qui envoie une liste de fichiers sur le serveur
	 *
	 * @param	usrfiles 	fichiers utilisateur à uploader
	 * @param	post		paramètres
	 * @return  string		résultat de l'envoi des fichiers
	 * @author	Stephane F
	 **/
	public function uploadFiles($usrfiles, $post) {

		$files = array();
		if(isset($post['myfiles'])) {
			foreach($post['myfiles'] as $key => $val) {
				list($selnum, $selval) = explode('_', $val);
				$files[] = array(
					'name'		=> $usrfiles['selector_'.$selnum]['name'][$selval],
					'size'		=> $usrfiles['selector_'.$selnum]['size'][$selval],
					'tmp_name'	=> $usrfiles['selector_'.$selnum]['tmp_name'][$selval]
				);
			}
		}

		$count=0;
		foreach($files as $file) {
			$resize = false;
			$thumb = false;
			if(!empty($post['resize'])) {
				if($post['resize']=='user') {
					$resize = array('width' => intval($post['user_w']), 'height' => intval($post['user_h']));
				} else {
					list($width,$height) = explode('x', $post['resize']);
					$resize = array('width' => $width, 'height' => $height);
				}
			}
			if(!empty($post['thumb'])) {
				if($post['thumb']=='user') {
					$thumb = array('width' => intval($post['thumb_w']), 'height' => intval($post['thumb_h']));
				} else {
					list($width,$height) = explode('x', $post['thumb']);
					$thumb = array('width' => $width, 'height' => $height);
				}
			}
			if($res=$this->_uploadFile($file, $resize, $thumb)) {
				switch($res) {
					case L_PLXMEDIAS_WRONG_FILESIZE:
						return plxMsg::Error(L_PLXMEDIAS_WRONG_FILESIZE);
						break;
					case L_PLXMEDIAS_WRONG_FILEFORMAT:
						return plxMsg::Error(L_PLXMEDIAS_WRONG_FILEFORMAT);
						break;
					case L_PLXMEDIAS_UPLOAD_ERR:
						return plxMsg::Error(L_PLXMEDIAS_UPLOAD_ERR);
						break;
					case L_PLXMEDIAS_UPLOAD_SUCCESSFUL:
						$count++;
						break;
				}
			}
		}

		if($count==1)
			return plxMsg::Info(L_PLXMEDIAS_UPLOAD_SUCCESSFUL);
		elseif($count>1)
			return plxMsg::Info(L_PLXMEDIAS_UPLOADS_SUCCESSFUL);
	}

	/**
	 * Méthode qui déplace une ou plusieurs fichiers
	 *
	 * @param   files		liste des fichier à déplacer
	 * @param	src_dir		répertoire source
	 * @param	dst_dir		répertoire destination
	 * @return  boolean		faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function moveFiles($files, $src_dir, $dst_dir) {

		if($dst_dir=='.') $dst_dir='';

		$count = 0;
		foreach($files as $file) {
			# protection pour ne pas déplacer un fichier en dehors de $this->path.$this->dir
			$file=basename($file);

			# Déplacement du fichier
			if(is_readable($this->path.$src_dir.$file)) {
				$result = rename($this->path.$src_dir.$file, $this->path.$dst_dir.$file);
				$count++;
			}
			# Déplacement de la miniature
			$thumbName = plxUtils::thumbName($file);
			if($result AND is_readable($this->path.$src_dir.$thumbName)) {
				$result = rename($this->path.$src_dir.$thumbName, $this->path.$dst_dir.$thumbName);
			}
			# Déplacement de la vignette
			if($result AND is_readable($this->path.'.thumbs/'.$src_dir.$file)) {
				$result = rename($this->path.'.thumbs/'.$src_dir.$file, $this->path.'.thumbs/'.$dst_dir.$file);
			}
		}

		if(sizeof($files)==1) {
			if($count==0)
				return plxMsg::Error(L_PLXMEDIAS_MOVE_FILE_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_MOVE_FILE_SUCCESSFUL);
		}
		else {
			if($count==0)
				return plxMsg::Error(L_PLXMEDIAS_MOVE_FILES_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_MOVE_FILES_SUCCESSFUL);
		}

	}

	/**
	 * Méthode qui recréer les miniatures
	 *
	 * @param   files		liste des fichier à déplacer
	 * @param	width		largeur des miniatures
	 * @param	height		hauteur des miniatures
	 * @return  boolean		faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function makeThumbs($files, $width, $height) {

		$count = 0;
		foreach($files as $file) {
			$file=basename($file);
			if(is_file($this->path.$this->dir.$file)) {
				$thumName = plxUtils::thumbName($file);
				$ext = strtolower(strrchr($this->path.$this->dir.$file,'.'));
				if(in_array($ext, $this->img_supported)) {
					if(plxUtils::makeThumb($this->path.$this->dir.$file, $this->path.$this->dir.$thumName, $width, $height, 80))
						$count++;
				}
			}
		}

		if(sizeof($files)==1) {
			if($count==0)
				return plxMsg::Error(L_PLXMEDIAS_RECREATE_THUMB_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_RECREATE_THUMB_SUCCESSFUL);
		}
		else {
			if($count==0)
				return plxMsg::Error(L_PLXMEDIAS_RECREATE_THUMBS_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_RECREATE_THUMBS_SUCCESSFUL);
		}

	}

	/**
	 * Méthode qui renomme un fichier
	 *
	 * @param   oldname		ancien nom
	 * @param	newname		nouveau nom
	 * @return  boolean		faux si erreur sinon vrai
	 * @author	Stephane F, J.P. "bazooka07" Pourrez, Pedro "P3ter" CADETE
	 **/
	public function renameFile($oldname, $newname) {
		$result = false;
		$new_stats = array();
		# Déplacement du fichier
		if(is_readable($oldname) AND is_file($oldname)) {
			$dirname = dirname($oldname)."/";
			$old_stats = pathinfo($oldname);
			$tmp_stats = pathinfo($newname);
			$new_stats['dirname'] = $old_stats['dirname'].'/';
			$new_stats['filename'] = plxUtils::urlify($tmp_stats['filename']);
			$new_stats['counter'] = '';
			$new_stats['extension'] = '.'.$old_stats['extension'];
			# On teste l'existence du nouveau fichier et on formate le nom pour éviter les doublons
			$i = 1;
			while(file_exists(implode('', array_values($new_stats)))) {
				$new_stats['counter'] = str_pad($i, 2, '-', STR_PAD_LEFT);
				$i++;
			}
			# changement du nom du fichier
			$filename = implode('', array_values($new_stats));
			if($result = rename($oldname, $filename)) {
				# changement du nom de la miniature
				$old_thumbName = plxUtils::thumbName($oldname);
				if(is_writable($old_thumbName)) {
					$new_thumbName = plxUtils::thumbName($filename);
					if(rename($old_thumbName, $new_thumbName)) {
						# changement du nom de la vignette
						$path = str_replace($this->path, $this->path.'.thumbs/', $dirname);
						$old_thumbName = $path.$filename;
						if(is_writable($old_thumbName)) {
							$new_thumbName = $path.basename($file);
							$result = rename($old_thumbName, $new_thumbName);
						}
					}
				}
			}
		}
		return $result? plxMsg::Info(L_RENAME_FILE_SUCCESSFUL): plxMsg::Error(L_RENAME_FILE_ERR);
	}
}