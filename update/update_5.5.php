<?php
/**
 * Classe de mise a jour pour PluXml version 5.5
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_5 extends plxUpdate{

	# mise à jour fichier parametres.xml
	public function step1() {
		# migration avec réindexation des commentaires
		echo L_UPDATE_COMMENTS_MIGRATION."<br />";

		$dir_coms = PLX_ROOT.$this->plxAdmin->aConf['racine_commentaires'];
		$dir_bkp  = $dir_coms.'backup-5.4/';

		# création d'un dossier de sauvegarde
		@mkdir($dir_bkp,0755,true);
		if(!is_dir($dir_bkp)) {
			echo '<p class="error">'.L_UPDATE_ERR_COMMENTS_MIGRATION.'</p>';
			return false;
		}
		# réindexation
		if($hd = opendir($dir_coms)) {
			$coms = array();
			while (false !== ($file = readdir($hd))) {
				if(preg_match('/([[:punct:]]?)([0-9]{4}).([0-9]{10})-([0-9]+).xml$/',$file,$capture)) {
					$coms[$capture[2]][] = $file;
					if(copy($dir_coms.$file, $dir_coms.'backup-5.4/'.$file)) { #sauvegarde
						unlink($dir_coms.$file); # suppression fichier original
					} else {
						echo '<p class="error">'.L_UPDATE_ERR_COMMENTS_MIGRATION.'</p>';
						return false;
					}
				}
			}
			ksort($coms);
			if($coms) {
				foreach($coms as $com) {
					foreach($com as $idx => $filename) {
						$new_filename =  preg_replace('/(.*)-[0-9]+.xml$/', '$1-'.($idx+1).'.xml', $filename);
						if(!copy($dir_bkp.$filename, $dir_coms.$new_filename)) { # copie migration
							echo '<p class="error">'.L_UPDATE_ERR_COMMENTS_MIGRATION.'</p>';
							return false;
						}
					}
				}
			}
		}
		# fin de l'étape sans erreurs
		return true;
	}

	# suppression des fichiers obsolètes
	public function step2() {
		# fichier version
		if(is_readable(PLX_ROOT.'version')) {
			unlink(PLX_ROOT.'version');
		}
		# fichier parametres_pluginhelp.php
		if(is_readable(PLX_CORE.'admin/parametres_pluginhelp.php')) {
			unlink(PLX_CORE.'admin/parametres_pluginhelp.php');
		}
		return true;
	}
}