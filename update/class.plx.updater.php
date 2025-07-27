<?php

/**
 * Classe plxUpdater responsable du gestionnaire des mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/

class plxUpdater {
	const VERSION_PATTERN = '(\d+(?:\.\d+){1,2})';

	public $oldVersion = null;
	public $allVersions = null;
	public $updatedVersions = null;

	public $plxMotor; # objet plxMotor

	/**
	 * Constructeur de la classe plxUpdater
	 *
	 * @return	null
	 * @author	Stephane F, Jean-Pierre Pourrez @bazooka07
	 **/
	public function __construct() {
		$versions = array();
		foreach(glob('update_*.php') as $filename) {
			if(preg_match('#^update_' . self::VERSION_PATTERN . '\.php$#', $filename, $matches)) {
				$versions[$matches[1]] = $filename;
			}
		}
		if(!empty($versions)) {
			uksort($versions, function($a, $b) {
				return version_compare($a, $b);
			});
		}
		$this->allVersions = $versions;

		# Récupère l'ancien n° de version de Pluxml
		$this->oldVersion = '';
		$this->plxMotor = plxMotor::getInstance();
		if(!empty($this->plxMotor->aConf['version'])) {
			# PluXml version >= 5.5
			$this->oldVersion = $this->plxMotor->aConf['version'];
		} else {
			$filename = PLX_ROOT . 'version';
			if(is_readable($filename)) {
				$oldVersion = trim(file(PLX_ROOT.'version')[0]);
				if(preg_match(self::VERSION_PATTERN, $oldVersion)) {
					$this->oldVersion = $oldVersion;
				}
			} else {
				return;
			}
		}

		$this->getVersions($this->oldVersion);
	}

	/**
	 * Méthode chargée de démarrer les mises à jour
	 *
	 * @param	version		précédente version de pluxml à mettre à jour, sélectionner par l'utilisateur
	 * @return	null
	 * @author	Stéphane F, Jean-Pierre Pourrez
	 **/
	public function startUpdate($version) {

		if(!preg_match('#' . self::VERSION_PATTERN . '#', $version)) {
			return false;
		}

		if(empty($this->oldVersion) or $this->oldVersion != $version) {
			$this->getVersions($version);
		}
		# démarrage des mises à jour. true si succès
		return $this->doUpdate();
	}

	/**
	 * Méthode qui récupère l'ancien et le nouveau n° de version de pluxml
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function getVersions($oldVersion) {
		$this->updatedVersions = array_filter(
			$this->allVersions,
			function($key) use($oldVersion) {
				return version_compare($key, $oldVersion, '>');
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Méthode qui execute les mises à jour étape par étape
	 *
	 * @return	boolean
	 * @author	Stéphane F
	 **/
	public function doUpdate() {

		if(empty($this->updatedVersions)) {
			# Nothing to do
			return true;
		}

		$errors = false;
?>
			<ul id="updated-list" style="max-height: 75vh; overflow-y: scroll; background-color: #eee;">
<?php
		foreach($this->updatedVersions as $num_version => $upd_filename) {
?>
				<li>
					<p><strong><?= L_UPDATE_INPROGRESS .' '. $num_version ?></strong></p>
					<ul>
<?php
			# inclusion du fichier de mise à jour
			include $upd_filename;

			# création d'un instance de l'objet de mise à jour
			$class_name = 'update_' . str_replace('.', '_', $num_version);
			$class_update = new $class_name();

			# appel des différentes étapes de mise à jour
			$step = 1;
			while(!$errors) {
				$method_name = 'step' . $step;
				if(!method_exists($class_name, $method_name)) {
					break;
				}

				if(!$class_update->$method_name()) {
					$errors = true; # erreur détectée
					break;
				}

				$step++; # étape suivante
			}
?>
					</ul>
				</li>
<?php
			unset($class_update);
			if($errors) {
				break;
			}
		}
?>
			</ul>
			<script>
				document.addEventListener('DOMContentLoaded', (ev) => {
					const el = document.querySelector('#updated-list > li:last-of-type');
					el.scrollIntoView({behavior: 'smooth'});
				});
			</script>
<?php
		if($errors) {
?>
				<p class="alert error"><?= L_UPDATE_ERROR ?></em></p>
<?php
			return false;
		}

		# Mise à jour finale avec le numéro de la dernière version de PluXml
		$class_update = new plxUpdate();
		$class_update->updateParameters(array('version' => PLX_VERSION));
?>
				<p class="alert success"><?= L_UPDATE_SUCCESSFUL ?></p>
		<p><?php printf(L_UPDATE_ENDED, PLX_VERSION); ?></p>
<?php

		# On nettoie tous les fichiers obsolètes
		$this->cleanUp();
		return true;
	}

	/*
	 * Drop old files older then half an year
	 * */
	public function cleanUp() {
?>
		<p><?= DELETION_OUTDATED_FILES ?> :</p>
		<ul>
<?php
		$dateRef = filemtime(PLX_CORE . 'lib/class.plx.motor.php') - 3600 * 24 * 182; # minus half an year
		$masks = array(PLX_ROOT . '*', PLX_ROOT . 'core/lib/*' , PLX_ROOT . 'update/*', PLX_ROOT . 'themes/defaut/*.php');
		$auths = glob(PLX_ROOT . '*/*/auth.php');
		if(!empty($paths)) {
			$auths = array_map(
				function($value) {
					return pre_replace('#/auth\.php$', '/*', $value); # on supprime le nom du fichier auth.php
				},
				$auths
			);
			$masks = array_merge($masks, $auths);
		}
		foreach($masks as $mask) {
			foreach(glob($mask) as $filename) {
				if(is_file($filename) and filemtime($filename) < $dateRef) {
					try {
						unlink($filename);
?>
			<li><?= $filename ?></li>
<?php
					} catch(Exception $e) {
?>
			<li class="error"><?= $e->getMessage() ?></li>
<?php
					}

				}
			}
		}
?>
		</ul>
<?php

	}

}

/**
 * Classe plxUpdate responsable d'exécuter des actions de mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxUpdate {

	const XML_HEADER = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
	protected $plxMotor; # objet de type plxMotor
	protected $aConf = null;

	/**
	 * Constructeur qui initialise l'objet plxMotor par référence
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct() {
		$this->plxMotor = plxMotor::getInstance();
		$this->getConfiguration(path('XMLFILE_PARAMETERS'));
	}

	protected function getConfiguration($filename) {
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);

		# On verifie qu'il existe des tags "parametre"
		if(isset($iTags['parametre'])) {
			$this->aConf = array();
			foreach($iTags['parametre'] as $iTag) {
				$param = $values[$iTag];
				$name = $param['attributes']['name'];
				# $this->aConf[$name] = isset($param['value']) ? $param['value'] : '';
				$this->aConf[$name] = $this->getValue($iTag, $values);
			}
		}
	}

	protected function editConfiguration($content='') {
		if(is_array($content) and !empty($content)) {
			$this->aConf = array_merge($this->aConf, $content);
		}

		# On recense les champs avec des valeurs libres saisies par l'utilisateur. A encadrer par <![CDATA[..]]>
		$cdata = array(
			'title',
			'description',
			'meta_description',
			'meta_keywords',
			'feed_footer',
			'custom_admincss_file',
			'smtp_username',
			'smtp_password',
			'smtpOauth2_clientId',
			'smtpOauth2_clientSecret',
		);

		if(preg_match('#^update_(\d+_\d+(?:_\d+)?)$#', get_class($this), $matches)) {
			$this->aConf['version'] = str_replace('_', '.', $matches[1]);
		}

		# Début du fichier XML
		ob_start();
		foreach($this->aConf as $k=>$v) {
			if (empty($v) or !in_array($k, $cdata)) {
				# <!CDATA[..]]> est inutile :  valeur numerique, champs uniquement avec caractères alphanumérique
				$content = plxUtils::strCheck($v);
			} elseif(in_array($k, array('description', 'feed_footer'))) {
				# On tolère quelques balises par défaut : <i>, <em>, <a>, <sup>, <span>,
				$content = plxUtils::strCheck($v, true);
			} else {
				# Aucune balise HTML tolérée
				$content = plxUtils::strCheck($v, true, null);
			}
?>
	<parametre name="<?= $k ?>"><?= $content ?></parametre>
<?php
		}

		# Mise à jour du fichier parametres.xml
		return $this->writeAsXML(ob_get_clean(), path('XMLFILE_PARAMETERS'), ''); # path('XMLFILE_PARAMETERS') includes PLX_ROOT
	}

	/**
	 * Méthode qui met à jour le fichier parametre.xml en important les nouveaux paramètres
	 *
	 * @param	new_params		tableau contenant la liste des nouveaux paramètres avec leur valeur par défaut.
	 * @return	string
	 * @author	Stéphane F
	 **/
	public function updateParameters($new_params=null) {
		# enregistrement des nouveaux paramètres
		return $this->editConfiguration($new_params);
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

	public function writeAsXML($content, $filename, $root = PLX_ROOT) {
		return plxUtils::write(self::XML_HEADER . '<document>' . PHP_EOL . $content . '</document>', $root . $filename);
	}

	protected function getValue($iTag, &$values, $default_value='') {
		if(
			empty($iTag) or
			!isset($values[$iTag]) or
			!isset($values[$iTag]['value'])
		) {
			return $default_value;
		}

		return trim($values[$iTag]['value']);
	}
}
