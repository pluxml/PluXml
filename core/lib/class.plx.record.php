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
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function f($field) {

		if($this->i === -1) # Compteur négatif
			$this->i++;
		# On controle que le champ demande existe bien
		if(isset($this->result[ $this->i ][ $field ]))
			return $this->result[ $this->i ][ $field ];
		else # Sinon on sort par une valeur negative
			return false;
	}

	/**
	 * Méthode qui retourne la date de l'enregistrement le plus récent
	 *
	 * @$update prendre en compte la date de mise à jour (articles,...)
	 * @return  date au format 'YmdHi' ou null
	 * @author J.P. Pourrez @bazooka07
	 **/
	public function lastUpdateDate($update=false) {
		return array_reduce($this->result, function($carry, $item) use($update) {
			if($update and array_key_exists('date_update', $item)) {
				# pour les articles si date miseà jour prise en compte
				$dt = $item['date_update'];
			} elseif(array_key_exists('date', $item)) {
				# pour les commentaires
				$dt = $item['date'];
			} else {
				# aucune clé trouvée
				return $carry;
			}

			return ($carry > $dt) ? $carry : $dt;
		});
	}

}
