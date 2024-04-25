<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.7
 *
 * @package PLX
 * @author	Stephane F
 **/

class update_5_1_7 extends plxUpdate{
	const VERSION = '5.1.7';
	const PLX_CONF = PLX_ROOT . PLX_CONFIG_PATH .'parametres.xml';
	const NEW_PARAMS = array(
		'config_path'	=> PLX_CONFIG_PATH,
		'thumbs'		=> 1,
		'version'		=> self::VERSION,
	);
	const CONFIG_PLUGINS = PLX_ROOT . PLX_CONFIG_PATH . 'plugins/';

	/*
	 * mise à jour dossier de configuration
	 * */
	public function step1() {

		# Création du dossier de configuration si besoin
		if(!is_dir(PLX_ROOT . PLX_CONFIG_PATH)) {
?>
		<li>Create config folder</li>
<?php
			mkdir(PLX_ROOT . PLX_CONFIG_PATH, 0755, true);
		}

		# Création du dossier de stockage des parametres des plugins
		if(!is_dir(PLX_ROOT . PLX_CONFIG_PATH . 'plugins')) {
?>
		<li>Create config folder for plugins</li>
<?php
			mkdir(PLX_ROOT . PLX_CONFIG_PATH . 'plugins', 0755,true);
		}

?>
		<li>Protection within <?= PLX_CONFIG_PATH ?> folder</li>
<?php
		# Protection du dossier de configuration
		$content = <<< EOT
<Files *>
	Order allow,deny
	Deny from all
</Files>
EOT;
		plxUtils::write($content, PLX_ROOT . PLX_CONFIG_PATH . ".htaccess");
		plxUtils::write("", PLX_ROOT . PLX_CONFIG_PATH .'index.html');

		# Relocalisation des fichiers de configuration si besoin
		if(
			self::PLX_CONF != path('XMLFILE_PARAMETERS') and
			!plxUtils::write(file_get_contents(PLX_CONF), path('XMLFILE_PARAMETERS'))
		) {
?>
		<p class="error"><?= L_UPDATE_ERR_FILE ?> : <?= path('XMLFILE_PARAMETERS') ?></p>
<?php
			return false;
		}

		foreach(array(
			'statiques'		=> 'XMLFILE_STATICS',
			'categories'	=> 'XMLFILE_CATEGORIES',
			'users'			=> 'XMLFILE_USERS',
			'tags'			=> 'XMLFILE_TAGS',
			'plugins'		=> 'XMLFILE_PLUGINS',
		) as $k=>$xml) {
			$src = PLX_ROOT . $this->aConf[$k];
			$dest = path($xml);
			if(
				$src != $dest
				and !rename($src, $dest)
			) {
?>
		<p class="error"><?= L_UPDATE_ERR_FILE ?> : <?= $dest ?></p>
<?php
				return false;
			}

			# on supprime le paramètre obsolète
			unset($this->aConf[$k]);
		}

		return true; # pas d'erreurs
	}

	/*
	 * mise à jour fichier parametres.xml
	 * */
	public function step2() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></li>
<?php
		# mise à jour du fichier des parametres
		return $this->updateParameters(self::NEW_PARAMS);
	}

	/*
	 * déplacement et renommage des fichiers parametres des plugins
	 * */
	public function step3() {
		$plugins = $this->getPlugins();

		if(empty($plugins)) {
			return true;
		}
?>
		<p><?= L_UPDATE_PLUG_MOVEPARAMFILE ?> :</p>
		<ul>

<?php
		foreach($plugins as $plugName=>$plugAttrs) {
			$plugParamFile = PLX_PLUGINS . $plugName . '/parameters.xml';
			if(is_file($plugParamFile)) {
				$title = $plugAttrs['title'];
				$dest = self::CONFIG_PLUGINS . $plugName . '.xml';
				if(is_writable($plugParamFile)) {
					$success = rename($plugParamFile, $dest);
				} else {
					$success = copy($plugParamFile, $dest);
				}
				if($success) {
?>
			<li><span style="color:green">&#10004; <?= $title ?></span></li>
<?php
				} else {
?>
			<li><span style="color:red">&#10007; <?= $title ?></span></li>
<?php
				}
			}
		}
?>
		</ul>
<?php
		return true; # pas d'erreurs
	}

	/* ------------- */

	public function getPlugins() {

		if(!is_file(path('XMLFILE_PLUGINS'))) {
			 return false;
		}

		# Mise en place du parseur XML
		$data = implode('',file(path('XMLFILE_PLUGINS')));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		unset($parser);

		$aPlugins = array();
		# On verifie qu'il existe des tags "plugin"
		if(isset($iTags['plugin'])) {
			foreach($iTags['plugin'] as $iTag) {
				$attrs = $values[$iTag]['attributes'];
				$name = $attrs['name'];
				$aPlugins[$name] = array(
					'activate' 	=> $attrs['activate'],
					'title'		=> $this->getValue($iTag, $values),
				);
			}
		}
		return $aPlugins;
	}

}
