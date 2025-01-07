<?php

/**
 * Classe plxGlob responsable de la récupération des fichiers à traiter
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Amaury Graillat, Stéphane F., Jean-Pierre Pourrez @bazooka07
 **/
class plxGlob {

	const PATTERNS = array(
		'arts'			=> '#^\D?(\d{4,})\.(?:\w+|\d{3})(?:,\w+|,\d{3})*\.\d{3}\.\d{12}\..*#',
		'statiques'		=> '#^(\d{3,})\..*#',
		'commentaires'	=> '#^_?\d{4,}\.(?:\d{10,})(?:-\d+)?.*#'
	);
	public $count = 0; # Le nombre de resultats
	public $aFiles = array(); # Tableau des fichiers

	private $dir = false; # Repertoire a checker
	private $onlyfilename = false; # Booleen indiquant si notre resultat sera relatif ou absolu
	private $rep = false; # Boolean pour ne lister que les dossiers

	private static $instance = array();

	/**
	 * Constructeur qui initialise les variables de classe
	 *
	 * @param	dir				repertoire à lire
	 * @param	rep				boolean pour ne prendre que les répertoires sans les fichiers
	 * @param	onlyfilename	boolean pour ne récupérer que le nom des fichiers sans le chemin
	 * @param	type			type de fichier lus (arts ou '')
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Amaury Graillat et Stephane F
	 **/
	private function __construct($dir,$rep=false,$onlyfilename=true,$type='') {

		# On initialise les variables de classe
		if(substr($dir, -1) != '/') {
			$dir .= '/';
		}
		$this->dir = $dir;
		$this->rep = $rep;
		$this->onlyfilename = $onlyfilename;
		$this->initCache($type);
	}

	/**
	 * Méthode qui se charger de créer le Singleton plxGlob
	 *
	 * @param	dir				répertoire à lire
	 * @param	rep				boolean pour ne prendre que les répertoires sans les fichiers
	 * @param	onlyfilename	boolean pour ne récupérer que le nom des fichiers sans le chemin
	 * @param	type			type de fichier lus (arts ou '')
	 * @return	objet			return une instance de la classe plxGlob
	 * @author	Stephane F
	 **/
	public static function getInstance($dir,$rep=false,$onlyfilename=true,$type=''){
		$basename = str_replace(PLX_ROOT, '', $dir);
		if (!isset(self::$instance[$basename]))
			self::$instance[$basename] = new plxGlob($dir,$rep,$onlyfilename,$type);
		return self::$instance[$basename];
	}

	/**
	 * Méthode qui se charge de mémoriser le contenu d'un dossier
	 *
	 * @param	type			type de fichier lus (arts ou '')
	 * @return	null
	 * @author	Amaury Graillat, Stephane F, Jean-Pierre Pourrez @bazooka07
	 **/
	private function initCache($type='') {

		if(is_dir($this->dir)) {
			switch($type) {
				case 'arts':
				case 'commentaires' :
					$suffix = '*.xml';
					break;
				case 'statiques' :
					$suffix = '*.php';
					break;
				default :
					# $type est une expression régulière (regex)
					$suffix = '*';
			}
			foreach(glob($this->dir . $suffix, $this->rep ? GLOB_ONLYDIR : 0) as $filename) {
				if($this->rep) {
					# On collecte uniquement les dossiers (plugins, themes, ...)
					if(is_dir($filename)) {
						$this->aFiles[] = $this->onlyfilename ? basename($filename) : $filename;
					}
				} else {
					# On collecte uniquement les fichiers ( arts, statiques, commentaires, ...)
					$file = basename($filename);
					if (array_key_exists($type, self::PATTERNS)) {
						if(preg_match(self::PATTERNS[$type], $file, $matches)) {
							if (!empty($matches[1])) {
								# On indexe
								$this->aFiles[$matches[1]] = $file;
							} else {
								# commentaires, ...
								$this->aFiles[] = $file;
							}
						}
					} elseif(!empty($type)) {
						# $type est un motif de recherche
						if(preg_match($type, $file, $matches)) {
							if (!empty($matches[1])) {
								# On indexe
								$this->aFiles[$matches[1]] = $file;
							} else {
								$this->aFiles[] = $file;
							}
						}
					} else {
						$this->aFiles[] = $file;
					}
				}
			}
		}
	}

	/**
	 * Méthode qui cherche les fichiers correspondants au motif $motif
	 *
	 * @param	motif			motif de recherche des fichiers sous forme d'expression réguliere
	 * @param	type			type de recherche: article ('art'), commentaire ('com') ou autre (''))
	 * @param	tri				type de tri (sort, rsort, alpha, ralpha)
	 * @param	publi			recherche des fichiers avant ou après la date du jour
	 * @return	array ou false
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F, Jean-Pierre Pourrez @bazooka07
	 **/
	private function search($motif,$type,$tri,$publi) {

		$array=array();
		$this->count = 0;

		if($this->aFiles) {
			$today = date('YmdHi');
			$time4Coms = time();
			# Pour chaque entree du repertoire
			foreach (array_filter($this->aFiles, function($file) use($motif) {
				return preg_match($motif,$file);
			}) as $file) {
				switch($type) {
					case 'art' : # Tri selon les dates de publication (article)
						# On decoupe le nom du fichier
						$index = explode('.',$file);
						# On cree un tableau associatif en choisissant bien nos cles et en verifiant la date de publication
						$key = ($tri === 'alpha' OR $tri === 'ralpha') ? $index[4].'~'.$index[0] : $index[3].$index[0];
						switch($publi) {
							case 'before' : if($index[3] <= $today) { $array[$key] = $file; } break;
							case 'after' : if($index[3] >= $today) { $array[$key] = $file; } break;
							default : $array[$key] = $file; # for 'all'
 						}
						break;
					case 'com' : # Tri selon les dates de publications (commentaire)
						# On decoupe le nom du fichier
						$index = explode('.',$file);
						# On cree un tableau associatif en choisissant bien nos cles et en verifiant la date de publication
						$key = $index[1] . $index[0];
						switch($publi) {
							case 'before' : if($index[1] <= $time4Coms) { $array[$key] = $file; } break;
							case 'after' : if($index[1] >= $time4Coms) { $array[$key] = $file; } break;
							default : $array[$key] = $file; # for 'all'
						}
						break;
					default :
						# Aucun tri
						$array[] = $file;
				}

			}
		}

		# On retourne le tableau si celui-ci existe
		$this->count = count($array);
		if($this->count > 0) {
			return $array;
		}

		return false;
	}

	/**
	 * Méthode qui retourne un tableau trié, des fichiers correspondants
	 * au motif $motif, respectant les différentes limites
	 *
	 * @param	motif			motif de recherche des fichiers sous forme d'expression régulière
	 * @param	type			type de recherche: article ('art'), commentaire ('com') ou autre (''))
	 * @param	tri				type de tri (sort, rsort, alpha, random)
	 * @param	depart			indice de départ de la sélection
	 * @param	limite			nombre d'éléments à sélectionner
	 * @param	publi			recherche des fichiers avant ou après la date du jour
	 * @return	array ou false
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function query($motif,$type='',$tri='',$depart='0',$limite=false,$publi='all') {

		# Si on a des résultats
		if($rs = $this->search($motif,$type,$tri,$publi)) {

			# Ordre de tri du tableau
			if (!empty($tri)) {
				switch ($tri) {
					case 'random':
						shuffle($rs);
						break;
					case 'alpha':
					case 'asc':
					case 'sort':
						if (!empty($type)) {
							ksort($rs);
						} else {
							sort($rs);
						}
						break;
					case 'ralpha':
					case 'desc':
					case 'rsort':
						if (!empty($type)) {
							krsort($rs);
						} else {
							rsort($rs);
						}
						break;
					default:
				}
			}

			# On enlève les clés du tableau
			if (!empty($type)) {
				$rs = array_values($rs);
			}
			# On a une limite, on coupe le tableau
			if (is_integer($limite) and is_integer($depart)) {
				return array_slice($rs, $depart, $limite);
			} else {
				return $rs;
			}
		}

		# On retourne une valeur négative
		return false;
	}

}
