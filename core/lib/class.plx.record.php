<?php

/**
 * Classe plxRecord responsable du parcourt des enregistrements
 *
 * @package PLX
 * @author	Anthony GUÉRIN et Florent MONTHEL
 **/
class plxRecord {

	public $size = false; # Nombre d'elements dans le tableau $result
	public $i = -1; # Position dans le tableau $result
	public $result = array(); # Tableau multidimensionnel associatif

	/**
	 * Constructeur qui initialise les variables de classe
	 *
	 * @param	array	tableau associatif des résultats à traiter
	 * @return	null
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function __construct(&$array) {

		# On initialise les variables de classe
		$this->result = &$array;
		$this->size = sizeof($this->result);
	}

	/**
	 * Méthode qui incrémente judicieusement la variable $i
	 *
	 * @return	boolean
	 * @author	Anthony GUÉRIN
	 **/
	public function loop() {

		if($this->i < $this->size-1) { # Tant que l'on est pas en fin de tableau
			$this->i++;
			return true;
		}
		# On sort par une valeur negative
		$this->i = -1;
		return false;
	}

	/**
	 * Méthode qui récupère la valeur du champ $field
	 * correspondant à la position courante
	 *
	 * @param	field	clef du tableau à retourner
	 * @return	string ou false
	 * @author	Anthony GUÉRIN et Florent MONTHEL, Jean-Pierre Pourrez
	 **/
	public function f($field) {

		if($this->i === -1) {
			# Compteur négatif
			$this->i++;
		}

		if($this->i < count($this->result)) {
			$item = $this->result[ $this->i ];
			# On controle que le champ demande existe bien
			if(isset($item[$field]))
				return $item[$field];
			else {
				# Pour rétro-compatibilité
				if($field == 'date' and isset($item['date_publication'])) {
					return $item['date_publication'];
				}
			}
		}

		# Echec. Sinon on sort par une valeur negative
		return false;
	}

	/**
	 * Méthode pour retrouver la plus récente date
	 *
	 * @param	$field	nom du champ contenant la date
	 * @return	Date la plus récente
	 * @author	J.P. Pourrez
	 *
	 * Utilisé pour les flux RSS
	 * */
	public function lastUpdated($field='date') {
		$last = '197001010100';
		for($i=0; $i<$this->size; $i++) {
			if(array_key_exists($field, $this->result[$i])) {
				$value = $this->result[$i][$field];
				if(preg_match('@^\d{12,}$@', $value) and $last < $value) {
					$last = $value;
				}
			}
		}
		return $last;
	}

}
