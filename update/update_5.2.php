<?php
/**
 * Classe de mise a jour pour PluXml version 5.2
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_2 extends plxUpdate {
	const NEW_PARAMS = array(
		'hometemplate' => 'home.php',
	);

	/*
	 * mise à jour fichier parametres.xml
	 * */
	public function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></li>
<?php
		# on supprime les parametres obsoletes
		unset($this->aConf['racine']);
		# mise à jour du fichier des parametres
		return $this->updateParameters(self::NEW_PARAMS);
	}

	/*
	 * mise à jour fichier plugins.xml - suppression de l'attribut activate et valeur vide pour le tag plugin
	 * */
	public function step2() {
?>
		<li><?= L_UPDATE_UPDATE_PLUGINS_FILE ?></li>
<?php
		# récupération de la liste des plugins
		$aPlugins = $this->getPlugins();
		# Migration du format du fichier plugins.xml
		ob_start();
		foreach($aPlugins as $k=>$v) {
			if(isset($v['activate']) AND $v['activate']!='0')
?>
	<plugin name="<?= $k ?>"></plugin>
<?php
		}

		if(!$this->writeAsXML(ob_get_clean(), path('XMLFILE_PLUGINS'), '')) {
?>
			<p class="error"><?= L_UPDATE_ERR_FILE_PROCESSING ?></p>
<?php
			return false;
		}

		return true;
	}

	/*=====*/

	/**
	 * Méthode qui charge le fichier plugins.xml (ancien format)
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
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
