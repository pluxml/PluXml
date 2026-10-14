<?php
if (!defined('PLX_CORE')) { exit; }

/**
 * Classe plxMedias regroupant les fonctions pour gérer la librairie des medias
 *
 * @package PLX
 * @author	Stephane F, Pedro "P3ter" CADETE, Jean-Pierre Pourrez @bazooka07
 **/
class plxMedias {

	const DEFAULT_THUMBNAIL = PLX_CORE . 'admin/theme/images/file.png';
	public $path = null; # chemin vers les médias
	public $dir = null;
	public $aDirs = array(); # liste des dossiers et sous dossiers
	public $aFiles = array(); # liste des fichiers d'un dossier
	public $maxUpload = array(); # valeur upload_max_filesize
	public $maxPost = array(); # valeur post_max_size

	const IMG_SUPPORTED = array('.png', '.gif', '.jpg', '.jpeg', '.bmp', '.webp'); # images formats supported
	const IMG_EXTS = '/\.(jpe?g|png|gif|bmp|webp)$/i';
	const DOC_EXTS = '/\.(7z|aiff|asf|avi|csv|docx?|epub|fla|flv|gpx|gz|gzip|m4a|m4v|mid|mov|mp3|mp4|mpc|mpe?g|ods|odt|odp|ogg|pdf|pptx?|ppt|pxd|qt|ram|rar|rm|rmi|rmvb|rtf|svg|swf|sxc|sxw|tar|tgz|txt|vtt|wav|webm|wma|wmv|xcf|xlsx?|zip)$/i';

	/**
	 * Constructeur qui initialise la variable de classe
	 *
	 * @param	string $path	répertoire racine des médias
	 * @param	string $dir		dossier de recherche
	 * @return	void
	 * @author	Stephane F
	 **/
	public function __construct($path, $dir) {

		# Initialisation
		$this->path = $path;
		$this->dir = $dir;

		# Création du dossier réservé à l'utilisateur connecté s'il n'existe pas
		if(!is_dir($this->path) and !mkdir($this->path,0755)) {
			plxMsg::Error(L_PLXMEDIAS_MEDIAS_FOLDER_ERR);
			return;
		}

		# Création du dossier réservé aux miniatures
		if(!is_dir($this->path.'.thumbs/'.$this->dir)) {
			mkdir($this->path.'.thumbs/'.$this->dir,0755,true);
		}

		$this->aDirs = $this->_getAllDirs();
		$this->aFiles = $this->_getDirFiles($this->dir);

		# Taille maxi des fichiers
		$max = strtoupper(ini_get('upload_max_filesize'));
		$value = 0;
		if(preg_match('#^(\d+)\s*([KMG])#', $max, $matches)) {
			$value = intval($matches[1]);
			switch($matches[2]) {
				# No break;
				case 'G': $value *= 1024;
				case 'M': $value *= 1024 ;
				case 'K': $value *= 1024;
			}
		}
		$this->maxUpload = array(
			'display'	=> preg_replace('#\s*(M|K)\b#', ' $1o', $max),
			'value'		=> $value,
		);

		# Taille maxi des données
		$max = strtoupper(ini_get('post_max_size'));
		$value = 0;
		if(preg_match('#^(\d+)\s*([KMG])#', $max, $matches)) {
			$value = intval($matches[1]);
			switch($matches[2]) {
				# No break;
				case 'G': $value *= 1024;
				case 'M': $value *= 1024 ;
				case 'K': $value *= 1024;
			}
		}
		$this->maxPost = array(
			'display'	=> preg_replace('#\s*(M|K)\b#', ' $1o', $max),
			'value'		=> $value,
		);
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
	 * @param	string $dir		répertoire de lecture
	 * @return	array	tableau contenant la liste de tous les fichiers d'un dossier
	 * @author	Stephane F
	 **/
	private function _getDirFiles($dir) {

		$matches = '';

		$src = $this->path.$dir;
		if(!is_dir($src)) return array();

		$offset = strlen($this->path);
		$files = array();
		foreach(array_filter(
			glob($src.'*'),
			function($item) { return !preg_match('@\.tb\.\w+$@', $item); } # On rejette les miniatures
		) as $filename) {
			if(is_dir($filename)) { continue; }

			$thumbInfos = false;
			$sample = self::DEFAULT_THUMBNAIL;
			$imgSize = false;
			if(preg_match(self::IMG_EXTS, $filename, $matches)) {
				$thumbName = plxUtils::thumbName($filename);
				if(file_exists($thumbName)) {
					$thumbInfos = array(
						'infos' 	=> getimagesize($thumbName),
						'filesize'	=> filesize($thumbName)
					);
				}
				$thumbName = $this->path. '.thumbs/' .substr($filename, $offset);
				if(file_exists($thumbName) or
					plxUtils::makeThumb(
						$filename,
						$thumbName
						)
				) {
					$sample = $thumbName;
					$imgSize = getimagesize($filename);
				}
			}
			$stats = stat($filename);
			$extension = '.' . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			if($extension == '.svg') {
				$sample = $filename;
				$sampleOk = true;
			}
			$files[basename($filename)] = array(
				'.thumb'	=> $sample,
				'name' 		=> basename($filename),
				'path' 		=> $filename,
				'date' 		=> $stats['mtime'],
				'filesize' 	=> $stats['size'],
				'extension'	=> $extension,
				'infos' 	=> $imgSize,
				'thumb' 	=> $thumbInfos
			);
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
	 * @param	array $files	liste des fichier à supprimer
	 * @return  bool	faux si erreur sinon vrai
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
	 * @param	string $deldir	répertoire de suppression
	 * @return	bool	résultat de la suppression
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
	 * @param	string $deldir	répertoire à supprimer
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
	 * @param	string $newdir	nom du répertoire à créer
	 * @return  bool	faux si erreur sinon vrai
	 * @author	Stephane F, J.P. Pourrez (bazooka07)
	 **/
	public function newDir($newdir) {
		if (empty(trim($newdir))) {
			return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_ERR);
		}

		$mydir = $this->path.$this->dir;
		$mydir .= plxUtils::urlify(trim($newdir));
		// On vérifie si le dossier existe déjà
		if (is_dir($mydir)) {
			return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_EXISTS);
		}

		if (mkdir($mydir, 0755)) {
			return plxMsg::Info(L_PLXMEDIAS_NEW_FOLDER_SUCCESSFUL);
		}

		# Echec à la création du dossier
		return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_ERR);
	}

	/**
	 * Méthode qui envoie un fichier sur le serveur
	 *
	 * @param	array $file	fichier à uploader
	 * @param	array $resize	taille du fichier à redimensionner si renseigné
	 * @param	array $thumb	taille de la miniature à créer si renseigné
	 * @return  string	message contenant le résultat de l'envoi du fichier
	 * @author	Stephane F, Pedro "P3ter" CADETE
	 **/
	private function _uploadFile($file, $resize, $thumb) {

		$i = 1;
		$filename = array();

		if($file['name'] == '') {
			return L_PLXMEDIAS_UPLOAD_ERR;
		}

		if($file['size'] > $this->maxUpload['value']) {
			return L_PLXMEDIAS_WRONG_FILESIZE;
		}

		if(!preg_match(self::IMG_EXTS, $file['name']) AND !preg_match(self::DOC_EXTS, $file['name'])) {
			return L_PLXMEDIAS_WRONG_FILEFORMAT;
		}

		if(mime_content_type($file['tmp_name']) == 'image/svg+xml' and preg_match('#</?script\b#', file_get_contents($file['tmp_name']))) {
			# Refus pour les images SVG avec tag <script> ( code malicieux possible )
			return L_PLXMEDIAS_WRONG_FILEFORMAT;
		}

		// On teste l'existence du fichier et on formate son nom pour éviter les doublons
		$filename = pathinfo($file['name']);
		$filename['filename'] = plxUtils::urlify($filename['filename']);
		$upFile = $this->path . $this->dir . $filename['filename'] . '.' . $filename['extension'];
		while(file_exists($upFile)) {
			$upFile = $this->path.$this->dir.$filename['filename'].'-'.$i++.".".$filename['extension'];
		}

		if(!move_uploaded_file($file['tmp_name'],$upFile)) { # Erreur de copie
			return L_PLXMEDIAS_UPLOAD_ERR;
		} else { # Ok
			if(
				preg_match(self::IMG_EXTS, $file['name']) and
				plxUtils::makeThumb($upFile, $this->path.'.thumbs/'.$this->dir.basename($upFile), 48, 48)
			) {
				# image bitmap
				if($resize) {
					plxUtils::makeThumb($upFile, $upFile, $resize['width'], $resize['height'], 80);
				}
				if($thumb) {
					plxUtils::makeThumb($upFile, plxUtils::thumbName($upFile), $thumb['width'], $thumb['height'], 80);
				}
			}
		}
		return L_PLXMEDIAS_UPLOAD_SUCCESSFUL;
	}

	/**
	 * Méthode qui envoie une liste de fichiers sur le serveur
	 *
	 * @param	array $usrfiles 	fichiers utilisateur à uploader
	 * @param	array $post		paramètres
	 * @return  bool		Le résultat de l'opération est affichée à l'écran
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
			$res=$this->_uploadFile($file, $resize, $thumb);
			if($res != L_PLXMEDIAS_UPLOAD_SUCCESSFUL) {
				return plxMsg::Error($res);
			}

			$count++;
		}

		switch($count) {
			case 0: return plxMsg::Error(L_PLXMEDIAS_UPLOAD_ERR);
			case 1: return plxMsg::Info(L_PLXMEDIAS_UPLOAD_SUCCESSFUL);
			default : return plxMsg::Info(L_PLXMEDIAS_UPLOADS_SUCCESSFUL);
		}
	}

	/**
	 * Méthode qui déplace une ou plusieurs fichiers
	 *
	 * @param   array $files		liste des fichier à déplacer
	 * @param	string $src_dir		répertoire source
	 * @param	string $dst_dir		répertoire destination
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
				if(rename($this->path.$src_dir.$file, $this->path.$dst_dir.$file)) {
					# Déplacement de la miniature
					$thumbName = plxUtils::thumbName($file);
					if(is_readable($this->path.$src_dir.$thumbName)) {
						rename($this->path.$src_dir.$thumbName, $this->path.$dst_dir.$thumbName);
					}
					# Déplacement de la vignette
					if(is_readable($this->path.'.thumbs/'.$src_dir.$file)) {
						rename($this->path.'.thumbs/'.$src_dir.$file, $this->path.'.thumbs/'.$dst_dir.$file);
					}
				}
				$count++;
			}
		}

		return ($count != count($files)) ? plxMsg::Error(L_PLXMEDIAS_MOVE_FILE_ERR) : plxMsg::Info(L_PLXMEDIAS_MOVE_FILE_SUCCESSFUL);
	}

	/**
	 * Méthode qui recréer les miniatures
	 *
	 * @param   array $files		liste des fichier à déplacer
	 * @param	int $width		largeur des miniatures
	 * @param	int $height		hauteur des miniatures
	 * @return  bool		faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function makeThumbs($files, $width, $height) {

		$count = 0;
		foreach($files as $file) {
			$file=basename($file);
			if(is_file($this->path.$this->dir.$file)) {
				$thumName = plxUtils::thumbName($file);
				$ext = strtolower(strrchr($this->path.$this->dir.$file,'.'));
				if(in_array($ext, self::IMG_SUPPORTED)) {
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
	 * @param   string $oldname		ancien nom
	 * @param	string $newname		nouveau nom
	 * @return  bool		faux si erreur sinon vrai
	 * @author	Stephane F, J.P. "bazooka07" Pourrez, Pedro "P3ter" CADETE
	 **/
	public function renameFile($oldname, $newname) {
		if(!isset($_SESSION['medias'])) {
			return false;
		}

		# Anti-hacking
		$oldname = PLX_ROOT . $_SESSION['medias'] . basename($oldname);
		$newname = basename($newname);

		# Déplacement du fichier
		$result = false;
		if(is_readable($oldname) AND is_file($oldname)) {
			$dirname = dirname($oldname)."/";
			$old_stats = pathinfo($oldname);
			$tmp_stats = pathinfo($newname);
			$new_stats = array(
				'dirname'	=> $old_stats['dirname'] . '/',
				'filename'	=> plxUtils::urlify($tmp_stats['filename']),
				'counter'	=> '',
				'extension'	=> '.' . $old_stats['extension'],
			);
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
							$new_thumbName = $path.basename($filename);
							$result = rename($old_thumbName, $new_thumbName);
						}
					}
				}
			}
		}
		return $result? plxMsg::Info(L_RENAME_FILE_SUCCESSFUL): plxMsg::Error(L_RENAME_FILE_ERR);
	}
}
