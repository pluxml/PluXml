<?php

/**
 * Classe plxUpdater responsable du gestionnaire des mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/

const PLX_UPDATE = PLX_ROOT . 'update/';

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
	public function __construct() {
		$this->allVersions = array();
		foreach(glob('update_*.php') as $filename) {
			if (preg_match('#^update_(\d\.[\d.]*)\.php$#', $filename, $matches)) {
				$this->allVersions[$matches[1]] = $filename;
			}
		}
		ksort($this->allVersions);
		$this->plxAdmin = plxAdmin::getInstance();
		$this->getVersions();
	}

	/**
	 * Méthode qui récupère l'ancien et le nouveau n° de version de pluxml
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function getVersions() {

		# Récupère l'ancien n° de version de Pluxml
		if(isset($this->plxAdmin->aConf['version'])) {
			$this->oldVersion = $this->plxAdmin->aConf['version'];
		}

		/*
		 # tester format $version et si $version < 1ère version
		if(!isset($this->allVersions[$this->oldVersion])) {
			$this->oldVersion='';
		}
		* */

		# Récupère le nouveau n° de version de PluXml
		if(defined('PLX_VERSION')) { # PluXml à partir de la version 5.5
			$this->newVersion = PLX_VERSION;
		} elseif(is_readable(PLX_ROOT.'version')) {
			$f = file(PLX_ROOT.'version');
			$this->newVersion = $f['0'];
		} else {
			die('Unknown version of PluXml');
		}
	}

	/**
	 * Méthode chargée de démarrer les mises à jour
	 *
	 * @param	version		précédente version de pluxml à mettre à jour, sélectionner par l'utilisateur
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function startUpdate($version='') {
		# démarrage des mises à jour
		if($this->doUpdate($version)) {
			$this->updateVersion();
		}
	}

	/**
	 * Méthode qui execute les mises à jour étape par étape
	 *
	 * @return	boolean
	 * @author	Stéphane F
	 **/
	public function doUpdate($version) {
		$errors = false;
?>
<ul>
<?php
		foreach($this->allVersions as $num_version => $upd_filename) {
			if(version_compare($num_version, $version, '<=')) {
				continue;
			}

			if($errors) {
				break;
			}
?>
	<li>
		<h2><?= L_UPDATE_INPROGRESS ?> <?= $num_version ?></h2>
		<ul>
<?php
			# inclusion du fichier de mise à jour
			include(PLX_UPDATE . $upd_filename);

			# création d'un instance de l'objet de mise à jour
			$class_name = 'update_'.str_replace('.', '_', $num_version);
			$class_update = new $class_name();

			# appel des différentes étapes de mise à jour
			for($i=1; $i<10; $i++) {
				$method_name = 'step' . $i;
				if(!method_exists($class_name, $method_name)) {
					break;
				}

				if(!$class_update->$method_name()) {
					$errors = true; # erreur détectée
					break;
				}
			}
			unset($class_update);
?>
		</ul>
	</li>
<?php
		}
?>
</ul>
<p class="<?= $errors ? 'error': 'msg' ?>"><?= $errors ? L_UPDATE_ERROR : L_UPDATE_SUCCESSFUL ?></p>
<?php
		return !$errors;
	}

	/**
	 * Méthode qui met à jour le n° de version dans le fichier parametres.xml
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function updateVersion() {

		# on relit le fichier de paramètre pour récupérer les éventuels nouveaux ajoutés par la mise à jour
		$new_params = array();
		$this->plxAdmin->getConfiguration(path('XMLFILE_PARAMETERS'));
		$new_params['version'] = $this->newVersion;
		$this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $new_params);
		printf(L_UPDATE_ENDED.'<br />', $this->newVersion);
	}
}

/**
 * Classe plxUpdate responsable d'exécuter des actions de mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxUpdate {

	protected const XML_HEADER = '<?xml version="1.0" encoding="' . PLX_CHARSET . '"?>' . PHP_EOL;
	protected $plxAdmin; # objet de type plxAdmin

	/**
	 * Constructeur qui initialise l'objet plxAdmin par référence
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct() {
		$this->plxAdmin = plxAdmin::getInstance();
		if(!isset($this->plxAdmin->aConf['plugins'])) {
			$this->plxAdmin->aConf['plugins']='data/configuration/plugins.xml';
		}
	}

	/**
	 * Méthode qui met à jour le fichier parametre.xml en important les nouveaux paramètres
	 *
	 * @param	new_params		tableau contenant la liste des nouveaux paramètres avec leur valeur par défaut.
	 * @return	boolean
	 * @author	Stéphane F, J.P. Pourrez "bazooka07"
	 **/
	public function updateParameters($new_params) {
		# enregistrement des nouveaux paramètres
		return $this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $new_params);
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
