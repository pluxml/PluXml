<?php

/**
 * Classe plxGlob responsable de la récupération des fichiers à traiter
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Amaury Graillat, Stéphane F., Jean-Pierre Pourrez @bazooka07
 **/
class plxGlob {

	const PATTERNS = array(
		'arts'			=> '#^\D?(\d{4,})\.(?:\w+|\d{3})(?:,\w+|,\d{3})*\.\d{3}\.\d{12}\.([^\.]+).*#',
		'statiques'		=> '#^(\d{3,})\..*#',
		'commentaires'	=> '#^_?(\d{4,})\.(\d{10,})(?:-(\d+))?.*#'
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
	 * @param	type			type de fichier lus (arts,  statiques, commentaires ou motif de recherche)
	 * @return	objet			return une instance de la classe plxGlob
	 * @author	Stephane F
	 **/
	public static function getInstance($dir, $rep=false, $onlyfilename=true, $type='arts'){
		$basename = str_replace(PLX_ROOT, '', $dir);
		if (!isset(self::$instance[$basename]))
			self::$instance[$basename] = new plxGlob($dir,$rep,$onlyfilename,$type);
		return self::$instance[$basename];
	}

	/**
	 * Méthode qui se charge de mémoriser le contenu d'un dossier
	 *
	 * @param	type  type de fichier lus (arts,  statiques, commentaires ou motif de recherche)
	 * @return	null
	 * @author	Amaury Graillat, Stephane F, Jean-Pierre Pourrez @bazooka07
	 **/
	private function initCache($type) {

		if(is_dir($this->dir)) {
			if($this->rep) {
				# On collecte uniquement les dossiers (plugins, themes, ...)
				foreach(glob($this->dir . '*', GLOB_ONLYDIR) as $filename) {
					$this->aFiles[] = $this->onlyfilename ? basename($filename) : $filename;
				}
				return;
			}

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
			foreach(glob($this->dir . $suffix) as $filename) {
				# On collecte uniquement les fichiers ( arts, statiques, commentaires, ...)
				$file = basename($filename);
				if (array_key_exists($type, self::PATTERNS)) {
					if(preg_match(self::PATTERNS[$type], $file, $matches)) {
						switch($type) {
							case 'arts' :
							case 'statiques' :
								# On indexe
								$this->aFiles[$matches[1]] = $file;
								break;
							default :
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

		$array = [];
		if($this->aFiles) {
			$now = time();
			$today = date('YmdHi');

			# Pour chaque entree du repertoire
			foreach ($this->aFiles as $filename) {

				if(preg_match($motif, $filename, $matches)) {
					if(!empty($tri)) {
						switch($type) {
							case 'art':
								# On decoupe le nom du fichier en artId, cats, user, date, url, extension xml
								list($artId, $cats, $user, $date, $url) = explode('.',$filename);
								if(!empty($url)) {
									# Tri selon les dates de publication (article)
									# On cree un tableau associatif en choisissant bien nos cles et en verifiant la date de publication
									$key = preg_match('#^r?alpha$#', $tri) ? $url . '~' . $artId : $date . $artId;
									$isPinned = preg_match('#^pin,#', $cats); # article épinglé
									switch($publi) {
										case 'before':
											if($date <= $today) {
												# Priorité aux articles épinglés
												if($tri == 'desc') {
													$key = ($isPinned ? '1' : '0') . $key;
												} elseif($tri == 'asc') {
													$key = ($isPinned ? '0' : '1') . $key;
												}
												$array[$key] = $filename;
											}
											break;
										case 'after':
											if($date >= $today) {
												# Priorité aux articles épinglés
												if($tri == 'desc') {
													$key = ($isPinned ? '1' : '0') . $key;
												} elseif($tri == 'asc') {
													$key = ($isPinned ? '0' : '1') . $key;
												}
												$array[$key] = $filename;
											}
											break;
										case 'all':
											$array[$key] = $filename;
											break;
									}
								}
								break;
							case 'com':
								# le nom du fichier en artId, time, ordre dans $matches
								# Tri selon les dates de publications (commentaire)
								$key = $matches[2] . $matches[1] . str_pad($matches[3], 3, '0', STR_PAD_LEFT); # 999 coms maxi pour un article
								# On cree un tableau associatif en choisissant bien nos cles et en verifiant la date de publication
								switch($publi) {
									case'before':
										if($matches[2] <= $now) {
											$array[$key] = $filename;
										}
										break;
									case 'after':
										if($matches[2] >= $now) {
											$array[$key] = $filename;
										}
										break;
									case 'all':
										$array[$key] = $filename;
										break;
								}
								break;
							default:
								# Aucun tri
								$array[] = $filename;
						}
					} else {
						# Aucun tri
						$array[] = $filename;
					}
				}
			}
		}
		$this->count = count($array);

		# On retourne le tableau si celui-ci existe
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
				if($depart > count($rs)) {
					# Auto-reset on first page
					$depart = 0;
				}
				return array_slice($rs, $depart, $limite);
			} else {
				return $rs;
			}
		}

		# On retourne une valeur négative
		return false;
	}

}
