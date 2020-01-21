<?php
/**
 * Classe de mise a jour pour PluXml version 5.1.1
 *
 * @package PLX
 * @author	Stephane F
 **/

use PluxmlUpdater\PlxUpdate;
use Pluxml\PlxUtils;

class update_5_1_1 extends PlxUpdate{

	# Migration du fichier des utilisateurs: renforcement des mots de passe
	public function step1() {

		echo L_UPDATE_USERS_MIGRATION."<br />";

		# On génère le fichier XML
		$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
		$xml .= "<document>\n";
		foreach($this->plxAdmin->aUsers as $user_id => $user) {
			$salt = PlxUtils::charAleatoire(10);
			$password = sha1($salt.$user['password']);
			$xml .= "\t".'<user number="'.$user_id.'" active="'.$user['active'].'" profil="'.$user['profil'].'" delete="'.$user['delete'].'">'."\n";
			$xml .= "\t\t".'<login><![CDATA['.PlxUtils::cdataCheck($user['login']).']]></login>'."\n";
			$xml .= "\t\t".'<name><![CDATA['.PlxUtils::cdataCheck($user['name']).']]></name>'."\n";
			$xml .= "\t\t".'<infos><![CDATA['.PlxUtils::cdataCheck($user['infos']).']]></infos>'."\n";
			$xml .= "\t\t".'<password><![CDATA['.$password.']]></password>'."\n";
			$xml .= "\t\t".'<salt><![CDATA['.$salt.']]></salt>'."\n";
			$xml .= "\t\t".'<email><![CDATA['.$user['email'].']]></email>'."\n";
			$xml .= "\t\t".'<lang><![CDATA['.$user['lang'].']]></lang>'."\n";
			$xml .= "\t</user>\n";
		}
		$xml .= "</document>";

		if(!PlxUtils::write($xml,PLX_ROOT.$this->plxAdmin->aConf['users'])) {
			echo '<p class="error">'.L_UPDATE_ERR_USERS_MIGRATION.' ('.$this->plxAdmin->aConf['users'].')</p>';
			return false;
		}

		return true;
	}

}
?>