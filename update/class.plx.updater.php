<?php

/**
 * Classe plxUpdater responsable du gestionnaire des mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/

define('PLX_UPDATE', PLX_ROOT.'update/');

class plxUpdater {

	public $newVersion = '';
	public $oldVersion = '' ;
	public $allVersions = null;

	public $plxAdmin; # objet plxAdmin

	/**
	 * Constructeur de la classe plxUpdater
	 *
	 * @param	versions	array	liste des versions + script de mise à jour (fichier versions.php)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($versions) {
		$this->allVersions = $versions;
		$this->plxAdmin = plxAdmin::getInstance();
		$this->getVersions();
	}

	/**
	 * Méthode chargée de démarrer les mises à jour
	 *
	 * @param	version		précédente version de pluxml à mettre à jour, sélectionner par l'utilisateur
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function startUpdate($version='') {

		# suppression des versions qui ont déjà été mises à jour
		$offset = array_search($version, array_keys($this->allVersions));
		if($offset!='') {
			$this->allVersions = array_slice($this->allVersions, $offset+1, null, true);
		}

		# démarrage des mises à jour
		if($this->doUpdate())
			$this->updateVersion();
	}

	/**
	 * Méthode qui récupère l'ancien et le nouveau n° de version de pluxml
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function getVersions() {

		# Récupère l'ancien n° de version de Pluxml
		if(isset($this->plxAdmin->aConf['version']))
			$this->oldVersion = $this->plxAdmin->aConf['version'];
		if(!isset($this->allVersions[$this->oldVersion]))
			$this->oldVersion='';

		# Récupère le nouveau n° de version de PluXml
		if(defined('PLX_VERSION')) { # PluXml à partir de la version 5.5
			$this->newVersion = PLX_VERSION;
		} elseif(is_readable(PLX_ROOT.'version')) {
			$f = file(PLX_ROOT.'version');
			$this->newVersion = $f['0'];
		}
	}

	/**
	 * Méthode qui met à jour le n° de version dans le fichier parametres.xml
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function updateVersion() {

		# on relit le fichier de paramètre pour récupérer les éventuels nouveaux ajoutés par la mise à jour
		$this->plxAdmin->getConfiguration(path('XMLFILE_PARAMETERS'));
		$new_params['version'] = $this->newVersion;
		$this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $new_params);
		printf(L_UPDATE_ENDED.'<br />', $this->newVersion);
	}

	/**
	 * Méthode qui execute les mises à jour étape par étape
	 *
	 * @return	stdout
	 * @author	Stéphane F
	 **/
	public function doUpdate() {

		$errors = false;
		foreach($this->allVersions as $num_version => $upd_filename) {

			if($upd_filename!='') {

				echo '<p><strong>'.L_UPDATE_INPROGRESS.' '.$num_version.'</strong></p>';
				# inclusion du fichier de mise à jour
				include(PLX_UPDATE.$upd_filename);

				# création d'un instance de l'objet de mise à jour
				$class_name = 'update_'.str_replace('.', '_', $num_version);
				$class_update = new $class_name();

				# appel des différentes étapes de mise à jour
				$next = true;
				$step = 1;
				while($next AND !$errors) {
					$method_name = 'step'.$step;
					if(method_exists($class_name, $method_name)) {
						if(!$class_update->$method_name()) {
							$errors = true; # erreur détectée
						} else {
							$step++; # étape suivante
						}
					}
					else $next = false;
				}
				echo '<br />';
			}

		}
		echo '<br />';

		if($errors)
			echo '<p class="error">'.L_UPDATE_ERROR.'</p>';
		else
			echo '<p class="msg">'.L_UPDATE_SUCCESSFUL.'</p>';

		return !$errors;
	}

}

/**
 * Classe plxUpdate responsable d'exécuter des actions de mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxUpdate {

	protected $plxAdmin; # objet de type plxAdmin

	/**
	 * Constructeur qui initialise l'objet plxAdmin par référence
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct() {
		$this->plxAdmin = plxAdmin::getInstance();
		if(!isset($this->plxAdmin->aConf['plugins']))
			$this->plxAdmin->aConf['plugins']='data/configuration/plugins.xml';
	}

	/**
	 * Méthode qui met à jour le fichier parametre.xml en important les nouveaux paramètres
	 *
	 * @param	new_params		tableau contenant la liste des nouveaux paramètres avec leur valeur par défaut.
	 * @return	stdio
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