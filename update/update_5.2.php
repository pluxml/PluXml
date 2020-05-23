<?php
/**
 * Classe de mise a jour pour PluXml version 5.2
 *
 * Release on 4 Aug 2013
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_2 extends plxUpdate{

	# mise à jour fichier plugins.xml
	public function step1() {
?>
		<p><?= L_UPDATE_UPDATE_PLUGINS_FILE ?></p>
<?php
		# récupération de la liste des plugins
		$aPlugins = $this->loadPluginsConfig();
		# Migration du format du fichier plugins.xml
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>" . PHP_EOL;
		$xml .= "<document>" . PHP_EOL;
		if(!empty($aPlugins)) {
	 		foreach($aPlugins as $k=>$v) {
				if(isset($v['activate']) AND !empty($v['activate']))
					$xml .= "\t<plugin name=\"$k\"></plugin>" . PHP_EOL;
			}
		}
		$xml .= "</document>";

		if(!plxUtils::write($xml, path('XMLFILE_PLUGINS'))) {
?>
		<p class="error"><?= L_UPDATE_ERR_FILE_PROCESSING ?></p>
<?php
			return false;
		}

		# Pour version < 5.1.7 ?
		if(array_key_exists('plugins', $this->plxAdmin->aConf)) {
			unset($this->plxAdmin->aConf['plugins']);
		}

		return true;
	}

	/**
	 * Méthode qui charge le fichier plugins.xml (ancien format)
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function loadPluginsConfig() {

		$filename = path('XMLFILE_PLUGINS');
		if(!is_file($filename)) return false;

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE, 0);
		xml_parse_into_struct($parser, $data, $values, $iTags);
		xml_parser_free($parser);

		# On verifie qu'il existe des tags "plugin"
		if(isset($iTags['plugin'])) {
			$aPlugins = array();
			# On boucle sur $nb
			for($i = 0, $nb = sizeof($iTags['plugin']); $i < $nb; $i++) {
				$name = $values[$iTags['plugin'][$i] ]['attributes']['name'];
				$aPlugins[$name] = array(
					'activate' 	=> $values[$iTags['plugin'][$i] ]['attributes']['activate'],
				);
			}
			return $aPlugins;
		}

		return false;
	}

}
