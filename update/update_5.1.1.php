<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.1
 *
 * @package PLX
 * @author	Stephane F
 **/
class update_5_1_1 extends plxUpdate {

	# Migration du fichier des utilisateurs: cryptage des mots de passe
	public function step1() {
?>
		<li><?= L_UPDATE_USERS_MIGRATION ?></li>
<?php
		# On génère le fichier XML
		ob_start();
?>
<document>
<?php
		foreach($this->_getUsers(PLX_ROOT . $this->aConf['users']) as $user_id => $user) {
			$salt = plxUtils::charAleatoire(10);
			$password = sha1($salt.$user['password']);
?>
	<user number="<?= $user_id ?>" active="<?= $user['active'] ?>" profil="<?= $user['profil'] ?>" delete="<?= $user['delete'] ?>">
		<login><![CDATA[<?= plxUtils::cdataCheck($user['login']) ?>]]></login>
		<name><![CDATA[<?= plxUtils::cdataCheck($user['name']) ?>]]></name>
		<infos><![CDATA[<?= plxUtils::cdataCheck($user['infos']) ?>]]></infos>
		<password><![CDATA[<?= $password ?>]]></password>
		<salt><![CDATA[<?= $salt ?>]]></salt>
		<email><![CDATA[<?= $user['email'] ?>]]></email>
		<lang><![CDATA[<?= $this->aConf['default_lang'] ?>]]></lang>
	</user>
<?php
		}
?>
</document>
<?php
		if(!plxUtils::write(XML_HEADER . ob_get_clean(), PLX_ROOT . $this->aConf['users'])) {
			echo '<p class="error">'.L_UPDATE_ERR_USERS_MIGRATION.' ('.$this->aConf['users'].')</p>';
			return false;
		}

		$this->updateParameters();
		return true;
	}

	private function _getUsers($filename) {
		$aUsers=array();
		if(is_file($filename)) {
			# Mise en place du parseur XML
			$data = implode('',file($filename));
			$parser = xml_parser_create(PLX_CHARSET);
			xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
			xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
			xml_parse_into_struct($parser,$data,$values,$iTags);
			xml_parser_free($parser);
			# On verifie qu'il existe des tags "user"
			if(isset($iTags['user']) AND isset($iTags['login'])) {
				# On compte le nombre d'utilisateur
				$nb = sizeof($iTags['login']);
				$step = ceil(sizeof($iTags['user']) / $nb);
				# On boucle sur $nb
				for($i = 0; $i < $nb; $i++) {
					$attrs = $values[$iTags['user'][$i * $step]]['attributes'];
					$userId = $attrs['number'];
					$user = array(
						'active'	=> $attrs['active'],
						'delete'	=> $attrs['delete'],
						'profil'	=> $attrs['profil'],
					);
					foreach(array('login', 'name', 'infos', 'password', 'email') as $k) {
						$user[$k] = $this->getValue($iTags[$k][$i], $values);
					}
					$aUsers[$userId] = $user;
				}
			}
		}
		# On retourne le tableau
		return $aUsers;
	}
}
