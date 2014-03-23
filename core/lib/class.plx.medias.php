<?php

/**
 * Classe plxMedias regroupant les fonctions pour gérer la librairie des medias
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxMedias {

	public $path = null; # chemin vers les médias
	public $dir = null;
	public $aDirs = array(); # liste des dossiers et sous dossiers
	public $aFiles = array(); # liste des fichiers d'un dossier
	public $maxUpload = array(); # taille maxi des images

	public $thumbQuality = 60; # qualite image
	public $thumbWidth = 60; # largeur des miniatures
	public $thumbHeight = 60; # hauteur des miniatures

	public $img_exts = '/\.(jpg|gif|png|bmp|jpeg)$/i';
	public $doc_exts = '/\.(7z|aiff|asf|avi|csv|doc|docx|fla|flv|gz|gzip|mid|mov|mp3|mp4|mpc|mpeg|mpg|ods|odt|odp|ogg|pdf|ppt|pptx|pxd|qt|ram|rar|rm|rmi|rmvb|rtf|swf|sxc|sxw|tar|tgz|txt|wav|wma|wmv|xls|xlsx|zip)$/i';

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

		# Taille maxi pour l'upload de fichiers sur le serveur
		$maxUpload = strtoupper(ini_get("upload_max_filesize"));
		$this->maxUpload['display'] = str_replace('M', ' Mo', $maxUpload);
		$this->maxUpload['display'] = str_replace('K', ' Ko', $this->maxUpload['display']);
		if(substr_count($maxUpload, 'K')) $this->maxUpload['value'] = str_replace('K', '', $maxUpload) * 1024;
		elseif(substr_count($maxUpload, 'M')) $this->maxUpload['value'] = str_replace('M', '', $maxUpload) * 1024 * 1024;
		elseif(substr_count($maxUpload, 'G')) $this->maxUpload['value'] = str_replace('G', '', $maxUpload) * 1024 * 1024 * 1024;
		else $this->maxUpload['value'] = 0;
	}

	/**
	 * Méthode récursive qui retourne un tableau de tous les dossiers et sous dossiers dans un répertoire
	 *
	 * @param	dir		repertoire de lecture
	 * @param	level	profondeur du repertoire
	 * @return	folders	tableau contenant la liste de tous les dossiers et sous dossiers
	 * @author	Stephane F
	 **/
	private function _getAllDirs($dir,$level=0) {

		# Initialisation
		$folders = array();

		$alldirs = scandir($dir);
		natsort($alldirs);

		foreach($alldirs as $folder) {
			if($folder[0] != '.') {
				if(is_dir(($dir!=''?$dir.'/':$dir).$folder)) {
					$dir = (substr($dir, -1)!='/' AND $dir!='') ? $dir.'/' : $dir;
					$path = str_replace($this->path, '',$dir.$folder.'/');
					$folders[] = array(
							'level' => $level,
							'name' => $folder,
							'path' => $path
						);

					$folders = array_merge($folders, $this->_getAllDirs($dir.$folder, $level+1) );
				}
			}
		}

		return $folders;
	}

	/**
	 * Méthode qui retourne la liste des des fichiers d'un répertoire
	 *
	 * @param	dir		répertoire de lecture
	 * @return	files	tableau contenant la liste de tous les fichiers d'un dossier
	 * @author	Stephane F
	 **/
	private function _getDirFiles($dir) {

		# Initialisation
		$files = array();
		# Ouverture et lecture du dossier demandé
		if($handle = opendir($this->path.$dir)) {
			while(FALSE !== ($file = readdir($handle))) {
				$thumName = plxUtils::thumbName($file);
				if($file[0] != '.' AND !preg_match('/index.htm/i', $file) AND !preg_match('/^(.*\.)tb.([^.]+)$/D', $file)) {
					if(is_file($this->path.$dir.$file)) {
						$ext = strtolower(strrchr($this->path.$dir.$file,'.'));
						$_thumb1=file_exists($this->path.'.thumbs/'.$dir.$file);
						if(!$_thumb1 AND in_array($ext, array('.gif', '.jpg', '.png'))) {
							$_thumb1 = plxUtils::makeThumb($this->path.$dir.$file, $this->path.'.thumbs/'.$dir.$file, $this->thumbWidth, $this->thumbHeight, $this->thumbQuality);
						}
						$_thumb2=false;
						if(is_file($this->path.$dir.$thumName)) {
							$_thumb2 = array(
								'infos' => getimagesize($this->path.$dir.$thumName),
								'filesize'	=> filesize($this->path.$dir.$thumName)
							);
						}
						$files[$file] = array(
							'.thumb'	=> $_thumb1 ? $this->path.'.thumbs/'.$dir.$file : PLX_CORE.'admin/theme/images/file.png',
							'name' 		=> $file,
							'path' 		=> $this->path.$dir.$file,
							'date' 		=> filemtime($this->path.$dir.$file),
							'filesize' 	=> filesize($this->path.$dir.$file),
							'extension'	=> $ext,
							'infos' 	=> getimagesize($this->path.$dir.$file),
							'thumb' 	=> $_thumb2
						);
					}
				}
			}
			closedir($handle);
		}
		# On tri le contenu
		ksort($files);
		# On retourne le tableau
		return $files;
	}

	/**
	 * Méthode qui formate l'affichage de la liste déroulante des dossiers
	 *
	 * @return	string	chaine formatée à afficher
	 * @author	Stephane F, Danielsan
	 **/
	public function contentFolder() {

		$str  = "\n".'<select class="folder" id="folder" size="1" name="folder">'."\n";
		$selected = (empty($this->dir)?'selected="selected" ':'');
		$str .= '<option '.$selected.'value=".">|. ('.L_PLXMEDIAS_ROOT.') &nbsp; </option>'."\n";
		# Dir non vide
		if(!empty($this->aDirs)) {
			foreach($this->aDirs as $k => $v) {
				$prefixe = '|&nbsp;&nbsp;';
				$i = 0;
				while($i < $v['level']) {
					$prefixe .= '&nbsp;&nbsp;';
					$i++;
				}
				$selected = ($v['path']==$this->dir?'selected="selected" ':'');
				$str .= '<option class="level_'.$v['level'].'" '.$selected.'value="'.$v['path'].'">'.$prefixe.$v['name'].'</option>'."\n";
			}
		}
		$str  .= '</select>'."\n";
		# On retourne la chaine
		return $str;
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
	 * @author	Stephane F
	 **/
	public function newDir($newdir) {

		$newdir = $this->path.$this->dir.$newdir;

		if(!is_dir($newdir)) { # Si le dossier n'existe pas on le créer
			if(!mkdir($newdir,0755))
				return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_NEW_FOLDER_SUCCESSFUL);
		} else {
			return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_EXISTS);
		}
	}

	/**
	 * Méthode qui envoi un fichier sur le serveur
	 *
	 * @param	file	fichier à uploader
	 * @param	resize	taille du fichier à redimensionner si renseigné
	 * @param	thumb	taille de la miniature à créer si renseigné
	 * @return  msg		message contenant le résultat de l'envoi du fichier
	 * @author	Stephane F
	 **/
	private function _uploadFile($file, $resize, $thumb) {

		if($file['name'] == '')
			return false;

		if($file['size'] > $this->maxUpload['value'])
			return L_PLXMEDIAS_WRONG_FILESIZE;

		if(!preg_match($this->img_exts, $file['name']) AND !preg_match($this->doc_exts, $file['name']))
			return L_PLXMEDIAS_WRONG_FILEFORMAT;

		# On teste l'existence du fichier et on formate le nom du fichier pour éviter les doublons
		$i = 1;
		$upFile = $this->path.$this->dir.plxUtils::title2filename($file['name']);
		$name = substr($upFile, 0, strrpos($upFile,'.'));
		$ext = strrchr($upFile, '.');
		while(file_exists($upFile)) {
			$upFile = $this->path.$this->dir.$name.'.'.$i++.$ext;
		}

		if(!move_uploaded_file($file['tmp_name'],$upFile)) { # Erreur de copie
			return L_PLXMEDIAS_UPLOAD_ERR;
		} else { # Ok
			if(preg_match($this->img_exts, $file['name'])) {
				plxUtils::makeThumb($upFile, $this->path.'.thumbs/'.$this->dir.basename($upFile), $this->thumbWidth, $this->thumbHeight, $this->thumbQuality);
				if($resize)
					plxUtils::makeThumb($upFile, $upFile, $resize['width'], $resize['height'], 80);
				if($thumb)
					plxUtils::makeThumb($upFile, plxUtils::thumbName($upFile), $thumb['width'], $thumb['height'], 80);
			}
		}
		return L_PLXMEDIAS_UPLOAD_SUCCESSFUL;
	}

	/**
	 * Méthode qui envoi une liste de fichiers sur le serveur
	 *
	 * @param	files	fichiers à uploader
	 * @param	post	parametres
	 * @return  msg		resultat de l'envoi des fichiers
	 * @author	Stephane F
	 **/
	public function uploadFiles($files, $post) {
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
				if(in_array($ext, array('.gif', '.jpg', '.png'))) {
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
}
?>
