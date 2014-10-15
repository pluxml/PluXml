<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.7
 *
 * @package PLX
 * @author	Stephane F
 **/

define('PLX_CONF', PLX_ROOT.'data/configuration/parametres.xml');

class update_5_1_7 extends plxUpdate{

	# mise à jour dossier de configuration
	public function step1() {

		# Création du dossier de configuration si besoin
		if(!is_dir(PLX_ROOT.PLX_CONFIG_PATH)) {
			mkdir(PLX_ROOT.PLX_CONFIG_PATH,0755,true);
		}

		# Création du dossier de stockage des parametres des plugins
		if(!is_dir(PLX_ROOT.PLX_CONFIG_PATH.'plugins')) {
			mkdir(PLX_ROOT.PLX_CONFIG_PATH.'plugins',0755,true);
		}

		# Protection du dossier de configuration
		plxUtils::write("<Files *>\n\tOrder allow,deny\n\tDeny from all\n</Files>", PLX_ROOT.PLX_CONFIG_PATH.".htaccess");
		plxUtils::write("", PLX_ROOT.PLX_CONFIG_PATH."index.html");
		# Relocalisation des fichiers de configuration si besoin
		if(!plxUtils::write(file_get_contents(PLX_CONF), path('XMLFILE_PARAMETERS'))) {
			echo '<p class="error">'.L_UPDATE_ERR_FILE.' : '.path('XMLFILE_PARAMETERS').'</p>';
			return false;
		}
		if(!plxUtils::write(file_get_contents(PLX_ROOT.$this->plxAdmin->aConf['statiques']), path('XMLFILE_STATICS'))) {
			echo '<p class="error">'.L_UPDATE_ERR_FILE.' : '.path('XMLFILE_STATICS').'</p>';
			return false;
		}
		if(!plxUtils::write(file_get_contents(PLX_ROOT.$this->plxAdmin->aConf['categories']), path('XMLFILE_CATEGORIES'))) {
			echo '<p class="error">'.L_UPDATE_ERR_FILE.' : '.path('XMLFILE_CATEGORIES').'</p>';
			return false;
		}
		if(!plxUtils::write(file_get_contents(PLX_ROOT.$this->plxAdmin->aConf['users']), path('XMLFILE_USERS'))) {
			echo '<p class="error">'.L_UPDATE_ERR_FILE.' : '.path('XMLFILE_USERS').'</p>';
			return false;
		}
		if(!plxUtils::write(file_get_contents(PLX_ROOT.$this->plxAdmin->aConf['tags']), path('XMLFILE_TAGS'))) {
			echo '<p class="error">'.L_UPDATE_ERR_FILE.' : '.path('XMLFILE_TAGS').'</p>';
			return false;
		}
		if(!plxUtils::write(file_get_contents(PLX_ROOT.$this->plxAdmin->aConf['plugins']), path('XMLFILE_PLUGINS'))) {
			echo '<p class="error">'.L_UPDATE_ERR_FILE.' : '.path('XMLFILE_PLUGINS').'</p>';
			return false;
		}

		return true; # pas d'erreurs
	}

	# mise à jour fichier parametres.xml
	public function step2() {

		echo L_UPDATE_UPDATE_PARAMETERS_FILE."<br />";
		$new_parameters['config_path'] = PLX_CONFIG_PATH;
		$new_parameters['thumbs'] = 1;
		# on supprime les parametres obsoletes
		unset($this->plxAdmin->aConf['statiques']);
		unset($this->plxAdmin->aConf['categories']);
		unset($this->plxAdmin->aConf['users']);
		unset($this->plxAdmin->aConf['tags']);
		unset($this->plxAdmin->aConf['plugins']);
		# mise à jour du fichier des parametres
		$this->updateParameters($new_parameters);

		return true; # pas d'erreurs
	}

	# déplacement et renommage des fichiers parametres des plugins
	public function step3() {

		# Récupère le nouveau n° de version de PluXml
		if(is_readable(PLX_ROOT.'version')) {
			$f = file(PLX_ROOT.'version');
			$newVersion = $f['0'];
		}

		echo L_UPDATE_PLUG_MOVEPARAMFILE."<br />";
		foreach($this->plxAdmin->plxPlugins->aPlugins as $plugName=>$plugAttrs) {
			$plugParamFile = PLX_PLUGINS.$plugName.'/parameters.xml';
			if(is_file($plugParamFile)) {
				if (version_compare($newVersion, '5.1.7') > 0)
					$title = $plugAttrs->getInfo('title');
				else
					$title = $plugAttrs['title'];
				if(plxUtils::write(file_get_contents($plugParamFile), PLX_ROOT.PLX_CONFIG_PATH.'/plugins/'.$plugName.'.xml')) {
					echo '<span style="color:green">&#10004; '.$title.'</span><br />';
					unlink($plugParamFile);
				}
				else
					echo '<span style="color:red">&#10007; '.$title.'</span><br />';
			}
		}
		return true; # pas d'erreurs
	}

}
?>