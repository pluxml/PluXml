<?php

/**
 * Classe de mise a jour pour PluXml version 5.8
 *
 * @package PLX
 * @author Pedro "P3ter" CADETE
 **/
class update_5_8_7 extends plxUpdate
{

	public function step1() {
?>
			<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?></li>
<?php
		$params = array(
			'email_method' => 'sendmail',
			'smtpOauth2_clientId' => '',
			'smtpOauth2_clientSecret' => '',
			'smtpOauth2_emailAdress' => '',
			'smtpOauth2_refreshToken' => '',
			'smtp_password' => '',
			'smtp_port' => 465,
			'smtp_security' => 'ssl',
			'smtp_server' => '',
			'smtp_username' => '',
			'thumbnail' => '',
			'thumbs' => 0,
		);
		foreach(array_keys($params) as $k) {
			if (isset($this->plxAdmin->aConf[$k])) {
				unset($params[$k]);
			}
		}

		if (isset($this->plxAdmin->aConf['plugins'])) {
			unset($this->plxAdmin->aConf['plugins']);
		}

		return $this->updateParameters($params);
	}

	# Reconstruction des fichiers admin.css et site.css pour les plugins actifs
	# dans le dossier data en remplacement du dossiers plguins
	public function step2()
	{
?>
	<li><?= L_BUILD_CSS_PLUGINS_CACHE ?></li>
<?php
		foreach(array('admin', 'site') as $context) {
			if (!$this->plxAdmin->plxPlugins->cssCache($context)) {
				return false;
			}
			$oldFilename = PLX_PLUGINS . $context . '.css';
			if(file_exists($oldFilename)) {
				unlink($oldFilename);
			}
		}
		return true;
	}
}

/*
 *
 * manquants :
*
* A supprimer
* plugins
  * */
