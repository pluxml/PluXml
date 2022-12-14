<?php
/**
 * Classe de mise a jour pour PluXml version 5.2
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_2 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
?>
			<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?></li>
<?php
		# on supprime les parametres obsoletes
		unset($this->plxAdmin->aConf['racine']);

		# nouveaux parametres
		return $this->updateParameters(array(
			'hometemplate' => 'home.php',
		));
	}

	# mise à jour fichier parametres.xml
	public function step2() {
?>
			<li><?= L_UPDATE_UPDATE_PLUGINS_FILE ?></li>
<?php
		$filename = path('XMLFILE_PLUGINS');
		# récupération de la liste des plugins
		$aPlugins = $this->loadConfig($filename);
		# Migration du format du fichier plugins.xml
		ob_start();
?>
<document>
<?php
		foreach($aPlugins as $k=>$v) {
			if(isset($v['activate']) AND $v['activate']!='0')
?>
	<plugin name="<?= $k ?>"></plugin>
<?php
		}
?>
</document>
<?php
		if(!plxUtils::write(self::XML_HEADER . ob_get_clean(), $filename)) {
			echo '<p class="error">'.L_UPDATE_ERR_FILE_PROCESSING.'</p>';
			return false;
		}
		return true;
	}

	/*=====*/

	/**
	 * Méthode qui charge le fichier plugins.xml (ancien format)
	 * @param string $filename chemin du fichier
	 * @return	null
	 * @author	Stephane F
	 **/
	public function loadConfig($filename) {
		if(!is_file($filename)) {
			return false;
		}

		# Mise en place du parseur XML
		$data = implode('', file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser, $data, $values, $iTags);
		xml_parser_free($parser);

		$aPlugins = array();
		# On verifie qu'il existe des tags "plugin"
		if(isset($iTags['plugin'])) {
			# On compte le nombre de tags "plugin"
			$nb = sizeof($iTags['plugin']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				$name = $values[$iTags['plugin'][$i] ]['attributes']['name'];
				$activate = $values[$iTags['plugin'][$i] ]['attributes']['activate'];
				$value = isset($values[$iTags['plugin'][$i]]['value']) ? $values[$iTags['plugin'][$i]]['value'] : '';
				$aPlugins[$name] = array(
					'activate' 	=> $activate,
					'title'		=> $value,
					'instance'	=> null,
				);
			}
		}
		return $aPlugins;
	}
}
