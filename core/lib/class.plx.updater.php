<?php

/**
 * Classe plxUpdater responsable du gestionnaire des mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/

if(!defined('PLX_ROOT')) { exit; }

const PLX_UPDATE = PLX_ROOT . 'update/';

class plxUpdater {

	public static function VERSIONS() {
		return array (
			'4.2',
			'4.3',
			'4.3.1',
			'4.3.2',
			'5.0',
			'5.0.1',
			'5.0.2',
			'5.1',
			'5.1.1',
			'5.1.2',
			'5.1.3',
			'5.1.4',
			'5.1.5',
			'5.1.6',
			'5.1.7',
			'5.2',
			'5.3',
			'5.3.1',
			'5.4',
			'5.5',
			'5.6',
			'5.7',
			'5.8',
			'5.8.1',
			'5.8.2',
			'5.8.3',
			'5.9.0'
		);
	}
	public $newVersion = false;
	public $oldVersion = false;
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
		$this->plxAdmin = plxAdmin::getInstance();
		if(empty(trim($this->plxAdmin->aConf['description']))) {
			$this->plxAdmin->aConf['description'] = plxUtils::strRevCheck(L_SITE_DESCRIPTION);
		}
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
		$offset = array_search($version, self::VERSIONS());
		$this->allVersions = ($offset !== false) ? array_slice(self::VERSIONS(), $offset+1, null, true) : self::VERSIONS;

		# démarrage des mises à jour
		if($this->doUpdate()) {
			# On désactive l'URL-Rewriting par précaution
			$this->pxAdmin->aConf['urlrewriting'] = 0;

			$this->updateVersion();
		}
	}

	/**
	 * Méthode qui récupère l'ancien et le nouveau n° de version de pluxml
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function getVersions() {

		# Récupère l'ancien n° de version de Pluxml
		if(array_key_exists('version', $this->plxAdmin->aConf)) {
			$version = $this->plxAdmin->aConf['version'];
			if(in_array($version, self::VERSIONS())) {
				$this->oldVersion = $version;
			}
		}

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
	 * @author	Stéphane F, J.P. Pourrez
	 *
	 * 2020-04-20 : PluXml 5.8.3 réduction des paramètres de plxAdmin::editConfiguration()
	 **/
	public function updateVersion() {

		# on relit le fichier de paramètre pour récupérer les éventuels nouveaux ajoutés par la mise à jour
		# $this->plxAdmin->getConfiguration(path('XMLFILE_PARAMETERS'));
		$this->plxAdmin->editConfiguration(array(
			'version'	=> $this->newVersion
		));
?>
		<p><strong><?php printf(L_UPDATE_ENDED, $this->newVersion); ?></strong></p>
<?php
	}

	/**
	 * Méthode qui execute les mises à jour étape par étape pour chaque version
	 *
	 * @return	boolean true if success
	 * @author	Stéphane F
	 * 2020-04-20 : optimisation code
	 **/
	public function doUpdate() {
		foreach($this->allVersions as $num_version) {
			$filename = PLX_UPDATE . 'update_' . $num_version . '.php';

			if(!file_exists($filename)) { continue; }

?>
	<p><strong><?= L_UPDATE_INPROGRESS ?> <?= $num_version ?></strong>
<?php
			# inclusion du fichier de mise à jour
			include $filename;

			# création d'un instance de l'objet de mise à jour
			$class_name = 'update_'.str_replace('.', '_', $num_version);
			$class_update = new $class_name();

			# appel des différentes étapes de mise à jour. 10 étapes maxi
			for($step=1; $step<10; $step++) {
				$method_name = 'step' . $step;
				if(!method_exists($class_name, $method_name)) {
					break;
				}

				if(!$class_update->$method_name()) {
?>
	<p class="error"><?php printf(L_UPDATE_ERROR, $step) ?></p>
<?php
					return false;
					break; # erreur détectée
				}
			}
		}
?>
	<p class="msg"><?php L_UPDATE_SUCCESSFUL ?></p>
<?php
		return true;
	}

}

/**
 * Classe plxUpdate responsable d'exécuter des actions de mises à jour
 *
 * @package PLX
 * @author	Stephane F, J.P. Pourrez
 *
 **/
class plxUpdate {

	protected $plxAdmin; # objet de type plxAdmin

	/**
	 * Constructeur qui initialise l'objet plxAdmin par référence
	 *
	 * @return	null
	 * @author	Stephane F, J.P. Pourrez
	 *
	 * 2020-04-20 : correction plxAdmin->aConf['plugins']
	 **/
	public function __construct() {
		$this->plxAdmin = plxAdmin::getInstance();
		# Version antérieur à 5.1.7 ???
		if(array_key_exists('plugins', $this->plxAdmin->aConf)) {
			unset($this->plxAdmin->aConf['plugins']);
		}
	}

	/**
	 *
	 *
	 * Méthode qui met à jour le fichier parametre.xml en important les nouveaux paramètres
	 *
	 * @param	new_params		tableau contenant la liste des nouveaux paramètres avec leur valeur par défaut.
	 * @return	string
	 * @author	Stéphane F
	 *
	 * PluXml 5.8.3 : Réduction paramètres pour plxAdmin::editConfiguration()
	 **/
	public function updateParameters($new_params) {

		# enregistrement des nouveaux paramètres
		$message = $this->plxAdmin->editConfiguration($new_params);
		# valeur de retour
		return '<p>' . $message . '</p>';

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
