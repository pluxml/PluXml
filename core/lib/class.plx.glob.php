<?php

/**
 * Classe plxGlob responsable de la récupération des fichiers à traiter
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Amaury Graillat et Stéphane F.
 **/
class plxGlob {

	const PATTERNS = array(
		'arts'			=> '#^\D?(\d{4,})\.(?:\w+|\d{3})(?:,\w+|,\d{3})*\.\d{3}\.\d{12}\..*\.xml$#',
		'statiques'		=> '#^(\d{3,})\..*\.php$#',
		'commentaires'		=> '#^_?\d{4,}\.(?:\d{10,})(?:-\d+)?\.xml$#'
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
	 * @author	Amaury Graillat et Stephane F
	 **/
	private function initCache($type='') {

		if(is_dir($this->dir)) {
			# On ouvre le repertoire
			if($dh = opendir($this->dir)) {
                # On recupere le nom du repertoire éventuellement
                $dirname = $this->onlyfilename ? '' : $this->dir;
				# Pour chaque entree du repertoire
				while(false !== ($file = readdir($dh))) {
					if($file[0]!='.') {
						$dir = is_dir($this->dir.'/'.$file);
						if($this->rep AND $dir) {
							# On collecte uniquement les dossiers (plugins, themes, ...)
							$this->aFiles[] = $dirname.$file;
						}
						elseif(!$this->rep AND !$dir) {
							# On collecte uniquement les fichiers ( arts, statiques, commentaires, ...)
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
							} elseif (!empty($type)) {
								# $type est un motif de recherche
								if(preg_match($type, $file, $matches)) {
									if (!empty($matches[1])) {
										# On indexe
										$this->aFiles[$matches[1]] = $file;
									} else {
										$this->aFiles[] = $file;
									}
								}
							}else {
								$this->aFiles[] = $file;
							}
						}
					}
				}
				# On ferme la ressource sur le repertoire
				closedir($dh);
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
	 * @author	Anthony GUÉRIN, Florent MONTHEL et Stephane F
	 **/
	private function search($motif,$type,$tri,$publi) {

		$array=array();
		$this->count = 0;

		if($this->aFiles) {

			# Pour chaque entree du repertoire
			foreach ($this->aFiles as $file) {

				if(preg_match($motif,$file)) {

					if($type === 'art') { # Tri selon les dates de publication (article)
						# On decoupe le nom du fichier
						$index = explode('.',$file);
						# On cree un tableau associatif en choisissant bien nos cles et en verifiant la date de publication
						$key = ($tri === 'alpha' OR $tri === 'ralpha') ? $index[4].'~'.$index[0] : $index[3].$index[0];
						if($publi === 'before' AND $index[3] <= date('YmdHi'))
							$array[$key] = $file;
						elseif($publi === 'after' AND $index[3] >= date('YmdHi'))
							$array[$key] = $file;
						elseif($publi === 'all')
							$array[$key] = $file;
						# On verifie que l'index existe
						if(isset($array[$key]))
							$this->count++; # On incremente le compteur
					}
					elseif($type === 'com') { # Tri selon les dates de publications (commentaire)
						# On decoupe le nom du fichier
						$index = explode('.',$file);
						# On cree un tableau associatif en choisissant bien nos cles et en verifiant la date de publication
						if($publi === 'before' AND $index[1] <= time())
							$array[ $index[1].$index[0] ] = $file;
						elseif($publi === 'after' AND $index[1] >= time())
							$array[ $index[1].$index[0] ] = $file;
						elseif($publi === 'all')
							$array[ $index[1].$index[0] ] = $file;
						# On verifie que l'index existe
						if(isset($array[ $index[1].$index[0] ]))
							$this->count++; # On incremente le compteur
					}
					else { # Aucun tri
						$array[] = $file;
						# On incremente le compteur
						$this->count++;
					}
				}
			}
		}

		# On retourne le tableau si celui-ci existe
		if($this->count > 0)
			return $array;
		else
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
