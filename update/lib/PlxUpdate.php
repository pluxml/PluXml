<?php
/**
 * Classe plxUpdate responsable d'exécuter des actions de mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/

namespace PluxmlUpdater;

use Pluxml\PlxAdmin;

const PLX_UPDATE = PLX_ROOT.'update/';

class PlxUpdate {

	protected $plxAdmin; # objet de type plxAdmin

	/**
	 * Constructeur qui initialise l'objet plxAdmin par référence
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct() {
		$this->plxAdmin = PlxAdmin::getInstance();
		if(!isset($this->plxAdmin->aConf['plugins']))
			$this->plxAdmin->aConf['plugins']='data/configuration/plugins.xml';
	}

	/**
	 * Méthode qui met à jour le fichier parametre.xml en important les nouveaux paramètres
	 *
	 * @param	new_params		tableau contenant la liste des nouveaux paramètres avec leur valeur par défaut.
	 * @return	string
	 * @author	Stéphane F
	 **/
	public function updateParameters($new_params) {

		# enregistrement des nouveaux paramètres
		$ret = $this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $new_params);
		# valeur de retour
		return $ret.'<br />';

	}

	/**
	 * Méthode récursive qui supprimes tous les dossiers et les fichiers d'un répertoire
	 *
	 * @param	deldir	répertoire de suppression
	 * @return	boolean	résultat de la suppression
	 * @author	Stephane F
	 **/
	public function deleteDir($deldir) { #fonction récursive

		if(is_dir($deldir) AND !is_link($deldir)) {
			if($dh = opendir($deldir)) {
				while(FALSE !== ($file = readdir($dh))) {
					if($file != '.' AND $file != '..') {
						$this->deleteDir(($deldir!='' ? $deldir.'/' : '').$file);
					}
				}
				closedir($dh);
			}
			return rmdir($deldir);
		}
		return unlink($deldir);
	}
}
?>
