<?php

/**
 * Classe de mise a jour pour PluXml version 5.8.22
 *
 * @package PLX
 * @author Jean-Pierre Pourrez @bazooka07
 **/
class update_5_10_0 extends plxUpdate
{

	# pour dossier articles, commentaires, statiques
	const HTACCESS_1 = <<< EOT
<Files "*">
    Require all denied
</Files>

EOT;
	# pour dossier medias
	const HTACCESS_2 = <<< EOT
Options -Indexes

EOT;

	/**
	 * Fix rules in .htaccess files in data folder for Apache
	 **/
    public function step1()
    {
?>
    <p><?= L_UPDATE_UPDATE_HTACCESS_FILE ?></p>
    <ul>
<?php
		foreach(array('articles', 'commentaires', 'statiques') as $k) {
			$folder = $this->plxAdmin->aConf['racine_' . $k];
			$filename = PLX_ROOT . $folder . '.htaccess';
			if(!file_exists($filename)) {
				$success = plxUtils::write(self::HTACCESS_1, $filename) ? '✅' : '❌';
				echo '<li>' . $folder . ' ' . $success . '</li>' . PHP_EOL;
				continue;
			}

			$content = trim(file_get_contents($filename));
			if(!preg_match('#^'. self::HTACCESS_1 . '#ims', $content)) {
				$success = plxUtils::write(self::HTACCESS_1 . $content, $filename) ? '✅' : '❌';
			} else {
				$success = '✅';
			}
			echo '<li>' . $folder . ' ' . $success . '</li>' . PHP_EOL;
		}

		# folder for medias
		$folder = $this->plxAdmin->aConf['medias'];
		$filename = PLX_ROOT . $folder . '.htaccess';
		if(!file_exists($filename)) {
			$success = plxUtils::write(self::HTACCESS_2, $filename) ? '✅' : '❌';
			echo '<li>' . $folder . ' ' . $success . '</li>' . PHP_EOL;
		}

		$content = trim(file_get_contents($filename));
		if(!preg_match('#^'. self::HTACCESS_2 . '#ims', $content)) {
			$success = plxUtils::write(self::HTACCESS_2 . $content, $filename) ? '✅' : '❌';
		} else {
			$success = '✅';
		}
		echo '<li>' . $folder . ' ' . $success . '</li>' . PHP_EOL;
?>
    </ul>
<?php
        return true;
    }

	/**
	 * new '.htaccess' files in PLX_ROOT and PLX_ROOT . 'themes/' folders
	 **/
	public function step2() {
		$this->plxAdmin->htaccess($this->plxAdmin->aConf['urlrewriting'], $this->plxAdmin->aConf['racine']);
		return true;
	}

	/**
	 *  Check for disable_functions in php.ini
	 **/
	public function step3() {
		$root = PLX_ROOT . $this->plxAdmin->aConf['racine_statiques'];
		$success = true;
?>
    <p><?= L_PHP_CRITICAL_FUNCTIONS ?></p>
<ul>
<?php
		foreach($this->plxAdmin->aStats as $id=>$infos) {
			# See core/admin/statique.php
			$content = $this->plxAdmin->getFileStatique($id);
			# See core/lib/class.plx.admin.php
			# Vérifie si le code PHP de la page statique contient des fonctions critiques
			if(preg_match(plxAdmin::CRITICAL_FUNCTIONS_PHP_PATTERN, $content, $matches)) {
				$success = false;
?>
	<li>Use of <?= $matches[1] ?>() function in static page #<?= $id ?> : <?= $infos['name'] ?></li>
<?php
			}
		}

		if($success) {
			echo '<li>Ok</li>' . PHP_EOL;
		}
?>
</ul>
<?php
		return true;
	}

}
