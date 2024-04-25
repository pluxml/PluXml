<?php
# voir réindexation commentaires

/**
 * Classe de mise a jour pour PluXml version 5.5
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_5 extends plxUpdate {

	/*
	 * migration avec réindexation des commentaires
	 * */
	public function step1() {
?>
			<li><?= L_UPDATE_COMMENTS_MIGRATION ?></li>
<?php

		$dir_coms = PLX_ROOT.$this->aConf['racine_commentaires'];
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
				if(preg_match('/([[:punct:]]?)(\d{4})\.(\d{10})\-(\d+)\.xml$/',$file,$capture)) {
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
						$new_filename =  preg_replace('/(.*)\-\d+\.xml$/', '$1-'.($idx+1).'.xml', $filename);
						if(!copy($dir_bkp.$filename, $dir_coms.$new_filename)) { # copie migration
?>
					<p class="error"><?= L_UPDATE_ERR_COMMENTS_MIGRATION ?></p>
<?php
							return false;
						}
					}
				}
			}
		}
		# fin de l'étape sans erreurs
		return true;
	}

	/*
	 * suppression des fichiers obsolètes
	 * */
	public function step2() {
		foreach(array(
			PLX_ROOT . 'version',
			PLX_CORE . 'admin/parametres_pluginhelp.php',
		) as $filename) {
			if(is_writable($filename)) {
				@unlink($filename);
			}
		}

		# Mise à jour version
		return $this->updateParameters();
	}
}
