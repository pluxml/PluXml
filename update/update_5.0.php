<?php
/**
 * Classe de mise a jour pour PluXml version 5.0
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_0 extends plxUpdate {
	const NEW_PARAMS = array(
		'urlrewriting' 	=> 0,
		'gzip'		 	=> 0,
		'feed_chapo' 	=> 0,
		'feed_footer' 	=> '',
		'users' 		=> PLX_CONFIG_PATH . 'users.xml',
		'tags' 			=> PLX_CONFIG_PATH . 'tags.xml',
		'editor'		=> 'plxtoolbar',
		'homestatic'	=> '',
	);
	const ART_PATTERN = '#^(\d{4})\.(\d{3}|home|draft)\.(\d{12})\.([\w-]+)\.xml$#';
	public $aTags = array();

	/* Création des nouveaux paramètres dans le fichier parametres.xml */
	public function step1() {
?>
		<li><?= L_UPDATE_UPDATE_PARAMETERS_FILE ?> : <em><?= implode(', ', array_keys(self::NEW_PARAMS)) ?></em></li>
<?php
		$this->updateParameters(self::NEW_PARAMS);
		$this->getConfiguration(path('XMLFILE_PARAMETERS')); # on recharge le fichier de configuration
		return true; # pas d'erreurs
	}

	/* Création du fichier data/configuration/tags.xml */
	public function step2() {
?>
		<li><?= L_UPDATE_CREATE_TAGS_FILE ?></li>
<?php
		if(!$this->writeAsXML('', $this->aConf['tags'])) {
?>
			<p class="error"><?= L_UPDATE_ERR_CREATE_TAGS_FILE ?></p>
<?php
			return false;
		}
		return true;
	}

	/* Création du fichier themes/style/tags.php */
	/* Création du fichier themes/style/archives.php */
	public function step3() {
		$style = PLX_ROOT . 'themes/' . $this->aConf['style'] . '/';
		$srcfile = $style . 'home.php';
		foreach(array('tags', 'archives',) as $k) {
			$dstfile = $style . $k .'.php';
			if(!is_file($dstfile)) {
?>
		<li><?= L_UPDATE_CREATE_THEME_FILE ?>: <?= $filename ?></li>
<?php
				if(!copy($srcfile, $dstfile)) {
?>
			<p class="error"><?= L_UPDATE_ERR_CREATE_THEME_FILE . ' ' . substr($filename, strlen(PLX_ROOT)) ?></p>
<?php
					return false;
				}
			}
		return true;
		}
	}

	/* Migration des articles: formatage xml + renommage des fichiers */
	public function step4() {
?>
		<li><?= L_UPDATE_ARTICLES_CONVERSION ?></li>
<?php
		$rep = PLX_ROOT.$this->aConf['racine_articles'];
		$artFiles = glob($rep . '*.xml');
		foreach($artFiles as $filename) {
			if(preg_match(self::ART_PATTERN, basename($filename))) {
				$art = $this->parseArticle($filename);
				if(!$this->_editArticle($art, $art['artId'])) {
?>
			<p class="error"><?= L_UPDATE_ERR_FILE_PROCESSING ?> : <?= basename($filename) ?></p>
<?php
					return false;
				}

				unlink($filename);
			}
		}

		return true;
	}

	/* Migration du fichier des pages statiques */
	public function step5() {
?>
		<li><?= L_UPDATE_STATICS_MIGRATION ?></li>
<?php
		if($statics = $this->getStatiques(PLX_ROOT.$this->aConf['statiques'])) {
			# On génère le fichier XML
			ob_start();
?>
<document>
<?php
			foreach($statics as $static_id => $static) {
?>
	<statique number="<?= $static_id ?>" active="<?= $static['active'] ?>" menu="<?= $static['menu'] ?>" url="<?= $static['url'] ?>" template="static.php">
		<group></group>
		<name><![CDATA[<?= $static['name'] ?>]]></name>
	</statique>
<?php
			}
?>
</document>
<?php
			if(!plxUtils::write(XML_HEADER . ob_get_clean(), PLX_ROOT . $this->aConf['statiques'])) {
?>
		<p class="error"><?= L_UPDATE_ERR_STATICS_MIGRATION ?> (<em><?= $this->aConf['statiques'] ?></em> )</p>
<?php
				return false;
			}
		}
		return true;
	}

	/* Création du fichier des utilisateurs */
	public function step6() {
?>
		<li><?= L_UPDATE_CREATE_USERS_FILE ?></li>
<?php
		if($users = $this->getUsers(PLX_ROOT . $this->aConf['passwords'])) {
			ob_start();
			$num_user = 1;
			foreach($users as $login => $password) {
?>
	<user number="<?= str_pad($num_user++, 3, '0', STR_PAD_LEFT) ?>" active="1" profil="0" delete="0">
		<login><![CDATA[<?= $login ?>]]></login>
		<name><![CDATA[<?= $login ?>]]></name>
		<infos></infos>
		<password><![CDATA[<?= $password ?>]]></password>
	</user>
<?php
			}

			if(!$this->writeAsXML(ob_get_clean(), $this->aConf['users'])) {
?>
		<p class="error"><?= L_UPDATE_ERR_CREATE_USERS_FILE ?> (<em><?= $this->aConf['users'] ?></em> )</p>
<?php
				return false;
			}
		} else {
?>
		<p class="error"><?= L_UPDATE_ERR_NO_USERS ?> ( <em><?= $this->aConf['passwords'] ?></em> )</p>
<?php
			return false;
		}

		return true;
	}

	/* Suppression des données obsolètes */
	public function step7() {
		# suppression du fichier data/configuration/passwords.xml
		unlink(PLX_ROOT.$this->aConf['passwords']);
		# suppression du fichier d'installation
		# unlink(PLX_ROOT.'install.php');
		# suppression des clés obsolètes dans le fichier data/configuration/parametres.xml
		unset($this->aConf['passwords']);
		$this->updateParameters();
		return true;
	}

	# Création du fichier .htaccess
	public function step8() {
		if(!is_file(PLX_ROOT.'.htaccess')) {
?>
		<li><?= L_UPDATE_CREATE_HTACCESS_FILE ?></li>
<?php
			$txt = <<< EOT
<Files "version">
    Order allow,deny
    Deny from all
</Files>
EOT;
			if(!plxUtils::write($txt,PLX_ROOT.'.htaccess')) {
?>
		<p class="warning"><?= L_UPDATE_ERR_CREATE_HTACCESS_FILE ?></p>
<?php
			}
		}
		return true;
	}

	/*=====*/

	private	function artInfoFromFilename($filename) {

		# On effectue notre capture d'informations
		if(preg_match(self::ART_PATTERN, basename($filename), $capture)) {
			return array(
				'artId'		=> $capture[1],
				'catId'		=> $capture[2],
				'artDate'	=> $capture[3],
				'artUrl'	=> $capture[4]
			);
		}

		return false;
	}

	private function parseArticle($filename) {
		# Informations obtenues en analysant le nom du fichier
		$tmp = $this->artInfoFromFilename($filename);

		if(preg_match('#^(\d{4})(\d{2})(\d{2})(\d{4})$#', $tmp['artDate'], $capture)) {
			$artDate = array(
				'year' => $capture[1],
				'month' => $capture[2],
				'day' => $capture[3],
				'time' => $capture[4],
			);
		}

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# Recuperation des valeurs de nos champs XML
		$art = array(
			'filename'	=> basename($filename),
			'artId'		=> $tmp['artId'],
			'catId'		=> array($tmp['catId']),
			'url'		=> $tmp['artUrl'],
			#nouveaux champs
			'author'	=> '001',
			'template'	=> 'article.php',
			'tags'		=> 'PluXml',
		);
		foreach(array('title', 'allow_com', 'chapo', 'content') as $k) {
			$art[$k] = $this->getValue($iTags[$k][0], $values);
		}
		return array_merge($art, $artDate);
	}

	private function _editArticle($content, $id) {
		# Génération de notre url d'article
		if(trim($content['url']) == '')
			$content['url'] = plxUtils::title2url($content['title']);
		else
			$content['url'] = plxUtils::title2url($content['url']);
		# URL vide après le passage de la fonction ;)
		if($content['url'] == '') $content['url'] = 'nouvel-article';
		# Génération du fichier XML
		ob_start();
?>
	<title><![CDATA[<?= trim($content['title']) ?>]]></title>
	<allow_com><?= $content['allow_com'] ?></allow_com>
	<template><![CDATA[<?= $content['template'] ?>]]></template>
	<chapo><![CDATA[<?= trim($content['chapo']) ?>]]></chapo>
	<content><![CDATA[<?= trim($content['content']) ?>]]></content>
	<tags><![CDATA[<?= trim($content['tags']) ?>]]></tags>
<?php

		# On génère le nom de notre fichier
		$time = $content['year'] . $content['month'] . $content['day'] . substr(str_replace(':', '', $content['time']) , 0, 4);
		$filename = $this->aConf['racine_articles'].$id.'.'.implode(',', $content['catId']).'.'.trim($content['author']).'.'.$time.'.'.$content['url'].'.xml';

		# On va mettre à jour notre fichier
		if($this->writeAsXML(ob_get_clean(), $filename)) {
			# mise à jour de la liste des tags
			$this->aTags[$id] = array('tags'=>trim($content['tags']), 'date'=>$time, 'active'=>intval(!in_array('draft', $content['catId'])));
			$this->_editTags();
			return true;
		}

		return false;
	}

	private function _editTags() {
		# Génération du fichier XML
		ob_start();
		foreach($this->aTags as $id => $tag) {
?>
	<article number="<?= $id ?>" date="<?= $tag['date'] ?>" active="<?= $tag['active'] ?>"><![CDATA[<?= $tag['tags'] ?>]]></article>
<?php
		}
		# On écrit le fichier
		return $this->writeAsXML(ob_get_clean(), $this->aConf['tags']);
	}

	private function getUsers($filename) {
		$users = array();
		if(is_file($filename)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			# On verifie qu'il existe des tags "user"
			if(isset($iTags['user'])) {
				# On boucle sur $iTags['user']
				foreach($iTags['user'] as $k) {
					$node = $values[$k];
					$attrs = $node['attributes'];
					$users[$attrs['login'] ] = $node['value'];
				}
			}
		}
		# On retourne le tableau
		return $users;
	}

	private function getStatiques($filename) {
		$aStats = array();
		if(is_file($filename)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);

			# On verifie qu'il existe des tags "statique"
			if(isset($iTags['statique'])) {
				# On boucle sur $iTags['statique']
				foreach($iTags['statique'] as $i=>$k) {
					$attrs = $values[$k]['attributes'];
					$statId = $attrs['number'];
					$file = PLX_ROOT . $statId . '.' . $attrs['url'] . '.php';
					$aStats[$statId] = array(
						# Recuperation de l'url de la page statique
						'url' => $attrs['url'],
						# Recuperation de l'etat de la page
						'active' => intval($attrs['active']),
						# Afficher la page statique dans le menu
						'menu' => isset($attrs['menu']) ? $attrs['menu'] : 'oui',
						# Recuperation du nom de la page statique
						'name'=> $this->getValue($k, $values, 'statique-' . ($i + 1)),
						# On verifie que la page statique existe bien
						'readable' => is_readable($file) ? 1 : 0,
					);
				}
			}
		}
		return $aStats;
	}
}
