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
		# nouveaux parametres
		$new_parameters = array();
		$new_parameters['hometemplate'] = 'home.php';
		# on supprime les parametres obsoletes
		unset($this->plxAdmin->aConf['racine']);
		# mise à jour du fichier des parametres
		$this->updateParameters($new_parameters);
		return true; # pas d'erreurs
	}

	# mise à jour fichier parametres.xml
	public function step2() {
?>
		<li><?= L_UPDATE_UPDATE_PLUGINS_FILE ?></li>
<?php
		# récupération de la liste des plugins
		$aPlugins = $this->loadConfig();
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
		if(!plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_PLUGINS'))) {
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
	public function loadConfig() {

		$aPlugins = array();

		if(!is_file(path('XMLFILE_PLUGINS'))) return false;
		# Mise en place du parseur XML
		$data = implode('',file(path('XMLFILE_PLUGINS')));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
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
