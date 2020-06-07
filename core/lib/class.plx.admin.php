<?php

/**
 * Classe plxAdmin responsable des modifications dans l'administration
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F et Pedro "P3ter" CADETE
 **/

const PLX_ADMIN = true;

class plxAdmin extends plxMotor {

	const PATTERN_CONFIG_CDATA = '@^(:?clef|custom_admincss_file|default_lang|email_method|hometemplate|medias|racine_(:?article|commentaire|plugin|statique|theme)s|style|timezone|tri(:?_coms)?|smtp_security|version)$@';
	const PATTERN_RACINES = '@^(:?medias|racine_(:?article|commentaire|plugin|statique|theme)s)$@';
	private static $EMPTY_FIELDS_CATEGORIE = array(
		'description', 'thumbnail', 'thumbnail_title', 'thumbnail_alt',
		'title_htmltag', 'meta_description', 'meta_keywords'
	);
	private static $EMPTY_FIELDS_USER = array('infos', 'password_token', 'password_token_expiry');
	private static $EMPTY_FIELD_STATIQUES = array('title_htmltag', 'meta_description', 'meta_keywords');

	public $update_link = PLX_URL_REPO; // overwritten by self::checkMaj()

	/**
	 * Méthode qui se charger de créer le Singleton plxAdmin
	 *
	 * @return	self	return une instance de la classe plxAdmin
	 * @author	Stephane F, Jean-Pierre Pourrez "Bazooka07"
	 **/
	public static function getInstance(){
		if (empty(parent::$instance))
			parent::$instance = new plxAdmin(path('XMLFILE_PARAMETERS'));
		return parent::$instance;
	}

	/**
	 * Constructeur qui appel le constructeur parent
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Florent MONTHEL
	 **/
	protected function __construct($filename) {

		parent::__construct($filename);

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			eval($this->plxPlugins->callHook('plxAdminConstruct'));
		}
	}

	/**
	 * Méthode qui applique un motif de recherche
	 *
	 * @param	motif	motif de recherche à appliquer
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function prechauffage($motif='') {
		$this->mode='admin';
		$this->motif=$motif;
		$this->bypage=$this->aConf['bypage_admin'];
	}

	/**
	 * Méthode qui récupère le numéro de la page active
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function getPage() {

		# Initialisation
		$pageName = basename($_SERVER['PHP_SELF']);
		$savePage = preg_match('@admin/(index|comments)\.php@', $_SERVER['PHP_SELF']);
		# On check pour avoir le numero de page
		if(!empty($_GET['page']) AND is_numeric($_GET['page']) AND $_GET['page'] > 0)
			$this->page = $_GET['page'];
			elseif($savePage) {
				if(!empty($_POST['sel_cat']))
					$this->page = 1;
					else
						$this->page = !empty($_SESSION['page'][$pageName])?intval($_SESSION['page'][$pageName]):1;
			}
			# On sauvegarde
			if($savePage) $_SESSION['page'][$pageName] = $this->page;
	}

	/**
	 * Méthode qui édite le fichier XML de configuration selon le tableau $global et $content
	 *
	 * @param	content	tableau contenant la configuration à modifier
	 * @return	string
	 * @author	Florent MONTHEL
	 **/
	public function editConfiguration($content=false) {

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			eval($this->plxPlugins->callHook('plxAdminEditConfiguration'));
		}

		if(!empty($content)) {
			foreach($content as $k=>$v) {
				if(!in_array($k,array('token', 'config_path'))) # parametres à ne pas mettre dans le fichier
					$this->aConf[$k] = $v;
			}
		}

		# On teste la clef
		if(empty($this->aConf['clef'])) $this->aConf['clef'] = plxUtils::charAleatoire(15);

		# Début du fichier XML
		ob_start();
?>
<document>
<?php
		foreach($this->aConf as $k=>$v) {
			if($k != 'racine') {
				$cdata = (preg_match(self::PATTERN_CONFIG_CDATA, $k) === 0);

				# Le chemin du dossier doit finir par "/"
				if(!empty($v) and preg_match(self::PATTERN_RACINES, $k) === 1 and substr($v, -1) != '/') {
					$v .= '/';
				}
?>
	<parametre name="<?= $k ?>"><?= plxUtils::cdataCheck($v, $cdata) ?></parametre>
<?php
			}
		}
?>
</document>
<?php

		# Mise à jour du fichier parametres.xml
		if(!plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_PARAMETERS')))
			return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_PARAMETERS'));

		# On réinitialise la pagination au cas où modif de bypage_admin
		unset($_SESSION['page']);

		# On réactualise la langue
		$_SESSION['lang'] = $this->aConf['default_lang'];

		# Actions sur le fichier htaccess
		if(!empty($content['urlrewriting']))
			if(!$this->htaccess($content['urlrewriting'], $this->aConf['racine']))
				return plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, '.htaccess'));

		# Si nouvel emplacement du dossier de configuration
		if(!empty($content['config_path'])) {
			$newpath = trim($content['config_path']);
			if(substr($newpath, -1) != '/') { $newpath .= '/'; }
			if($newpath != PLX_CONFIG_PATH) {
				# relocalisation du dossier de configuration de PluXml
				if(!rename(PLX_ROOT.PLX_CONFIG_PATH, PLX_ROOT . $newpath))
					return plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, $newpath));
				# mise à jour du fichier de configuration config.php
				$output = <<< OUTPUT
<?php const PLX_CONFIG_PATH = '$newpath') ?>

OUTPUT;
				if(!plxUtils::write($output, PLX_ROOT . 'config.php'))
					return plxMsg::Error(L_SAVE_ERR . ' config.php');
			}
		}

		return plxMsg::Info(L_SAVE_SUCCESSFUL);

	}

	/**
	 * Méthode qui crée le fichier .htaccess en cas de réécriture d'urls
	 *
	 * @param	action	création (add) ou suppression (remove)
	 * @param	url		url de base du site
	 * @return	null
	 * @author	Stephane F, Amaury Graillat
	 **/
	public function htaccess($action, $url) {

		$capture = '';
		$base = parse_url($url);

		$plxhtaccess = '
# BEGIN -- Pluxml
Options -Multiviews
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteBase '.$base['path'].'
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l
# Réécriture des urls
RewriteRule ^(?!feed)(.*)$ index.php?$1 [L]
RewriteRule ^feed\/(.*)$ feed.php?$1 [L]
</IfModule>
# END -- Pluxml
';

		$htaccess = '';
		if(is_file(PLX_ROOT.'.htaccess'))
			$htaccess = file_get_contents(PLX_ROOT.'.htaccess');

		switch($action) {
			case '0': # désactivation
				if(preg_match("/^(.*)(# BEGIN -- Pluxml.*# END -- Pluxml)(.*)$/ms", $htaccess, $capture))
					$htaccess = str_replace($capture[2], '', $htaccess);
				break;
			case '1': # activation
				if(preg_match("/^(.*)(# BEGIN -- Pluxml.*# END -- Pluxml)(.*)$/ms", $htaccess, $capture))
					$htaccess = trim($capture[1]).$plxhtaccess.trim($capture[3]);
				else
					$htaccess .= $plxhtaccess;
				break;
		}

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminHtaccess'));
		# On écrit le fichier .htaccess à la racine de PluXml
		$htaccess = trim($htaccess);
		if($htaccess=='' AND is_file(PLX_ROOT.'.htaccess')) {
			unlink(PLX_ROOT.'.htaccess');
			return true;
		} else {
			return plxUtils::write($htaccess, PLX_ROOT.'.htaccess');
		}

	}

	/**
	 * Méthode qui controle l'accès à une page en fonction du profil de l'utilisateur connecté
	 *
	 * @param	profil		profil(s) autorisé(s). Doit être numérique ou tableau de numérique
	 * @param	redirect	si VRAI redirige sur la page index.php en cas de mauvais profil(s). Si is_numeric, 2ème profil permis.
	 * @return	boolean or void
	 * @author	Stephane F, J.P. Pourrez
	 *
	 * Pour recensement dans code : grep -n checkProfil *.php update/*.php core/{admin,lib}/*.php
	 **/
	public function checkProfil($profil, $redirect=true) {

		if(!isset($_SESSION['profil']) or !is_numeric($_SESSION['profil'])) {
			# No authentification. Run away !
			session_abort();
			header('Location: ' . PLX_ROOT);
			exit;
		}

		if(!is_bool($redirect)) {
			if(is_numeric($redirect) and is_numeric($profil)) {
				$profils = array($profil, $redirect);
				$redirect = true;
			}
		} elseif(is_array($profil)) {
			$profils = array_filter($profil, function($item) { return is_numeric($item); });
		} elseif(is_numeric($profil)) {
			$profils = array($profil);
		}

		if(!empty($profils)) {
			if(count($profils) > 1) {
				if(in_array($_SESSION['profil'], $profils)) {
					return true;
				}
			} elseif($profils[0] <= PROFIL_WRITER and $_SESSION['profil'] <= $profils[0]) {
				return true;
			}
		}

		# accès refusé
		if($redirect) {
			plxMsg::Error(L_NO_ENTRY);
			if(!empty($_SERVER['HTTP_REFERER'])) {
				$url = basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
				if($url == 'index.php') {
					# Avoid for infinite loops
					header('Content-Type: text/plain');
					echo 'CheckProfil() fails. Abort!';
					exit;
				}
			} else {
				$url = 'index.php';
			}
			$location = 'Location: ' . $url;
			header($location);
			exit;
		}

		return false;
	}

	/**
	 * Méthode qui édite le profil d'un utilisateur
	 *
	 * @param	content	tableau contenant les informations sur l'utilisateur
	 * @return	string
	 * @author	Stéphane F
	 **/
	public function editProfil($content) {

		if(isset($content['profil']) AND trim($content['name'])=='')
			return plxMsg::Error(L_ERR_USER_EMPTY);

			if(trim($content['email'])!='' AND !plxUtils::checkMail(trim($content['email'])))
				return plxMsg::Error(L_ERR_INVALID_EMAIL);

			if(!in_array($content['lang'], plxUtils::getLangs()))
				return plxMsg::Error(L_UNKNOWN_ERROR);

			$this->aUsers[$_SESSION['user']]['name'] = trim($content['name']);
			$this->aUsers[$_SESSION['user']]['infos'] = trim($content['content']);
			$this->aUsers[$_SESSION['user']]['email'] = trim($content['email']);
			$this->aUsers[$_SESSION['user']]['lang'] = $content['lang'];

			$_SESSION['admin_lang'] = $content['lang'];

			# Hook plugins
			if(eval($this->plxPlugins->callHook('plxAdminEditProfil'))) return;
			return $this->editUsers(null, true);
	}

	/**
	 * Méthode qui édite le mot de passe d'un utilisateur
	 *
	 * @param	content	tableau contenant le nouveau mot de passe de l'utilisateur
	 * @return	string
	 * @author	Stéphane F, PEdro "P3ter" CADETE
	 **/
	public function editPassword($content) {

		$token = '';
		$action = false;

		if(trim($content['password1'])=='' OR trim($content['password1'])!=trim($content['password2'])) {
			return plxMsg::Error(L_ERR_PASSWORD_EMPTY_CONFIRMATION);
		}

		if(!empty($token = $content['lostPasswordToken'])) {
			foreach($this->aUsers as $user_id => $user) {
				if ($user['password_token'] == $token) {
					$salt = $this->aUsers[$user_id]['salt'];
					$this->aUsers[$user_id]['password'] = sha1($salt.md5($content['password1']));
					$this->aUsers[$user_id]['password_token'] = '';
					$this->aUsers[$user_id]['password_token_expiry'] = '';
					$action = true;
					break;
				}
			}
		}
		else {
			$salt = $this->aUsers[$_SESSION['user']]['salt'];
			$this->aUsers[$_SESSION['user']]['password'] = sha1($salt.md5($content['password1']));
			$action = true;
		}

		return $this->editUsers(null, $action);

	}

	/**
	 * Create a token and send a link by e-mail with "email-lostpassword.xml" template
	 *
	 * @param	loginOrMail	user login or e-mail address
	 * @return	string		token to password reset
	 * @author	Pedro "P3ter" CADETE, J.P. Pourrez aka bazooka07
	 **/
	public function sendLostPasswordEmail($loginOrMail) {
		if (!empty($loginOrMail) and plxUtils::testMail(false)) {
			foreach($this->aUsers as $user_id => $user) {
				if(!$user['active'] or $user['delete'] or empty($user['email'])) { continue; }

				if($user['login'] == $loginOrMail OR $user['email'] == $loginOrMail) {
					// Attention à l'unicité des logins !!!
					// token and e-mail creation
					$mail = array();
					$tokenExpiry = 24;
					$lostPasswordToken = plxToken::getTokenPostMethod(32, false);
					$lostPasswordTokenExpiry = plxToken::generateTokenExperyDate($tokenExpiry);
					$templateName = 'email-lostpassword-'.PLX_SITE_LANG.'.xml';

					$placeholdersValues = array(
						"##LOGIN##"			=> $user['login'],
						"##URL_PASSWORD##"	=> $this->aConf['racine'] . substr(PLX_ADMIN_PATH, strlen(PLX_ROOT)) . 'auth.php?action=changepassword&token='. $lostPasswordToken,
						"##URL_EXPIRY##"	=> $tokenExpiry,
					);
					if (($mail ['body'] = $this->aTemplates[$templateName]->getTemplateGeneratedContent($placeholdersValues)) != '1') {
						$mail['subject'] = $this->aTemplates[$templateName]->getTemplateEmailSubject();

						if(empty($this->aConf['email_method']) or $this->aConf['email_method'] == 'sendmail' or !method_exists(plxUtils, 'sendMailPhpMailer')) {
							# fonction mail() intrinséque à PHP
							$success = plxUtils::sendMail('', '', $user['email'], $mail['subject'], $mail['body']);
						} else {
							# On utilise PHPMailer
							if (!empty($this->aConf['title'])) {
								$mail ['name'] = $this->aConf['title'];
							} else {
								$mail ['name'] = $this->aTemplates[$templateName]->getTemplateEmailName();
							}
							$mail ['from'] = $this->aTemplates[$templateName]->getTemplateEmailFrom();
							// send the e-mail and if it is OK store the token
							$success = plxUtils::sendMailPhpMailer($mail['name'], $mail['from'], $user['email'], $mail['subject'], $mail['body'], false, $this->aConf, false);
						}

						if (!empty($success)) {
							$this->aUsers[$user_id]['password_token'] = $lostPasswordToken;
							$this->aUsers[$user_id]['password_token_expiry'] = $lostPasswordTokenExpiry;
							$this->editUsers($user_id, true);
							return $lostPasswordToken;
						}
					}
					break;
				}
			}
		}

		return '';
	}

	/**
	 * Verify the lost password token validity
	 *
	 * @param	token	the token to verify
	 * @return	boolean	true if the token exist and is not expire
	 * @author	Pedro "P3ter" CADETE
	 */
	public function verifyLostPasswordToken($token) {

		foreach($this->aUsers as $user_id => $user) {
			if ($user['password_token'] == $token) {
				return ($user['password_token_expiry'] >= date('YmdHi'));
			}
		}
		return false;
	}

	/**
	 * Méthode qui édite le fichier XML des utilisateurs
	 *
	 * @param	content	tableau les informations sur les utilisateurs
	 * @param	$save	enregistre les catégories dans un fichier .xml
	 * @return	string
	 * @author	Stéphane F, Pedro "P3ter" CADETE
	 **/
	public function editUsers($content, $save=false) {

		$archive = $this->aUsers;

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			if(eval($this->plxPlugins->callHook('plxAdminEditUsersBegin'))) return;
		}

		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idUser']) AND empty($content['update'])) {
			foreach($content['idUser'] as $user_id) {
				if($content['selection']=='delete' AND $user_id!='001') {
					$this->aUsers[$user_id]['delete']=1;
					$save = true;
				}
			}
		}

		# mise à jour de la liste des utilisateurs
		elseif(!empty($content['update'])) {
			foreach($content['userNum'] as $user_id) {
				$username = trim($content[$user_id.'_name']);
				if($username!='' AND trim($content[$user_id.'_login'])!='') {

					# controle du mot de passe
					$salt = plxUtils::charAleatoire(10);
					if(trim($content[$user_id.'_password'])!='')
						$password=sha1($salt.md5($content[$user_id.'_password']));
					elseif(isset($content[$user_id.'_newuser'])) {
						$this->aUsers = $archive;
						return plxMsg::Error(L_ERR_PASSWORD_EMPTY.' ('.L_CONFIG_USER.' <em>'.$username.'</em>)');
					}
					else {
						$salt = $this->aUsers[$user_id]['salt'];
						$password = $this->aUsers[$user_id]['password'];
					}

					# controle de l'adresse email
					$email = trim($content[$user_id.'_email']);
					if(isset($content[$user_id.'_newuser']) AND empty($email))
						return plxMsg::Error(L_ERR_INVALID_EMAIL);
					if(!empty($email) AND !plxUtils::checkMail($email))
						return plxMsg::Error(L_ERR_INVALID_EMAIL);

					$this->aUsers[$user_id] = array(
						'login'					=> trim($content[$user_id . '_login']),
						'name'					=> trim($content[$user_id . '_name']),
						'active'				=> (!empty($_SESSION['user']) && $_SESSION['user'] == $user_id) ? $this->aUsers[$user_id]['active'] : $content[$user_id . '_active'],
						'profil'				=> (!empty($_SESSION['user']) && $_SESSION['user'] == $user_id) ? $this->aUsers[$user_id]['profil'] : $content[$user_id . '_profil'],
						'password'				=> $password,
						'salt'					=> $salt,
						'email'					=> $email,

						'delete'				=> plxUtils::getValue($this->aUsers[$user_id]['delete'], 0),
						'lang'					=> plxUtils::getValue($this->aUsers[$user_id]['lang'], $this->aConf['default_lang']),
					);
					foreach(self::$EMPTY_FIELDS_USER as $k) {
						if(!array_key_exists($k, $this->aUsers[$user_id])) {
							$this->aUsers[$user_id][$k] = '';
						}
					}

					if(!empty($this->plxPlugins)) {
						# Hook plugins
						eval($this->plxPlugins->callHook('plxAdminEditUsersUpdate'));
					}

					$save = true;
				}
			}
		}

		if(empty($save)) { return; }

 		# sauvegarde
		$users_name = array();
		$users_login = array();
		$users_email = array();

		# On génére le fichier XML
		ob_start();
?>
<document>
<?php
		foreach($this->aUsers as $user_id => $user) {
			# controle de l'unicité du nom de l'utilisateur
			if(in_array($user['name'], $users_name)) {
				$this->aUsers = $archive;
				ob_clean();
				return plxMsg::Error(L_ERR_USERNAME_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['name']));
			}
			else if ($user['delete'] == 0) {
				$users_name[] = $user['name'];
			}
			# controle de l'unicité du login de l'utilisateur
			if(in_array($user['login'], $users_login)) {
				$this->aUsers = $archive;
				ob_clean();
				return plxMsg::Error(L_ERR_LOGIN_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['login']));
			}
			else if ($user['delete'] == 0) {
				$users_login[] = $user['login'];
			}
			# controle de l'unicité de l'adresse e-mail
			if(!empty($user['email'])) {
				if(in_array($user['email'], $users_email)) {
					$this->aUsers = $archive;
					ob_clean();
					return plxMsg::Error(L_ERR_EMAIL_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['email']));
				}
				else if ($user['delete'] == 0) {
					$users_email[] = $user['email'];
				}
			}
?>
	<user number="<?= $user_id ?>" active="<?= $user['active'] ?>" profil="<?= $user['profil'] ?>" delete="<?= $user['delete'] ?>">
		<login><?= plxUtils::cdataCheck($user['login']) ?></login>
		<name><?= plxUtils::cdataCheck($user['name']) ?></name>
		<infos><?= plxUtils::cdataCheck($user['infos']) ?></infos>
		<password><?= $user['password'] ?></password>
		<salt><?= $user['salt'] ?></salt>
		<email><?= plxUtils::cdataCheck($user['email']) ?></email>
		<lang><?= $user['lang'] ?></lang>
		<password_token><?= plxUtils::cdataCheck($user['password_token']) ?></password_token>
		<password_token_expiry><?= plxUtils::cdataCheck($user['password_token_expiry']) ?></password_token_expiry>
<?php
			if(!empty($this->plxPlugins)) {
				# Hook plugins
				$xml = '';
				eval($this->plxPlugins->callHook('plxAdminEditUsersXml'));
				if(!empty($xml)) { echo $xml; }
			}
?>
	</user>
<?php
		}
?>
</document>
<?php

		# On écrit le fichier
		if(plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_USERS'))) {
			return plxMsg::Info(L_SAVE_SUCCESSFUL);
		}

		$this->aUsers = $archive;
		return plxMsg::Error(L_SAVE_ERR . ' ' . path('XMLFILE_USERS'));
	}

	/**
	 * Méthode qui sauvegarde le contenu des options d'un utilisateur
	 *
	 * @param	content	données à sauvegarder
	 * @return	string
	 * @author	Stephane F.
	 **/
	public function editUser($content) {

		# controle de l'adresse email
		if(trim($content['email'])!='' AND !plxUtils::checkMail(trim($content['email'])))
			return plxMsg::Error(L_ERR_INVALID_EMAIL);

			# controle de la langue sélectionnée
			if(!in_array($content['lang'], plxUtils::getLangs()))
				return plxMsg::Error(L_UNKNOWN_ERROR);

				$this->aUsers[$content['id']]['email'] = $content['email'];
				$this->aUsers[$content['id']]['infos'] = trim($content['content']);
				$this->aUsers[$content['id']]['lang'] = $content['lang'];
				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditUser'));
				return $this->editUsers(null,true);
	}

	/**
	 *  Méthode qui retourne le prochain id d'une catégorie
	 *
	 * @return	string	id d'un nouvel article sous la forme 001
	 * @author	Stephane F.
	 **/
	public function nextIdCategory() {
		if(!empty($this->aCats) and is_array($this->aCats)) {
			$idx = key(array_slice($this->aCats, -1, 1, true));
			return str_pad($idx+1,3, '0', STR_PAD_LEFT);
		} else {
			return '001';
		}
	}

	/**
	 * Méthode qui édite le fichier XML des catégories selon le tableau $content
	 *
	 * @param	content	tableau multidimensionnel des catégories
	 * @param	save	enregistre les catégories dans un fichier .xml
	 * @return	boolean	true if success
	 * @author	Stephane F, Pedro "P3ter" CADETE, sudwebdesign, J.P. Pourrez
	 **/
	public function editCategories($content, $save=false) {

		$archive = $this->aCats;

		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idCategory']) AND empty($content['update'])) {
			foreach($content['idCategory'] as $cat_id) {
				// change article category to the default category id
				foreach($this->plxGlob_arts->aFiles as $numart => $filename) {
					$filenameArray = explode(".", $filename);
					$filenameArrayCat = explode(",", $filenameArray[1]);
					if (in_array($cat_id, $filenameArrayCat)) {
						$key = array_search($cat_id, $filenameArrayCat);
						if(count(preg_grep('#\d{3}#', $filenameArrayCat)) > 1) {
							// this article has more than one category
							unset($filenameArrayCat[$key]);
						}
						else {
							$filenameArrayCat[$key] = '000';
						}
						$filenameArray[1] = implode(",", $filenameArrayCat);
						$filenameNew = implode(".", $filenameArray);
						rename(PLX_ROOT.$this->aConf['racine_articles'].$filename, PLX_ROOT.$this->aConf['racine_articles'].$filenameNew);
					}
				}
				unset($this->aCats[$cat_id]);
				$save = true;
			}
		}
		# Ajout d'une nouvelle catégorie à partir de la page article
		elseif(!empty($content['new_category'])) {
			$cat_name = $content['new_catname'];
			if(!empty($cat_name)) {
				$cat_id = $this->nextIdCategory();
				$this->aCats[$cat_id] = array(
					'name'		=> $cat_name,
					'url'		=> plxUtils::urlify($cat_name),
					'tri'		=> $this->aConf['tri'],
					'bypage'	=> $this->aConf['bypage'],
					'menu'		=> 'oui',
					'active'	=> 1,
					'ordre'		=> 'asc',
					'homepage'	=> 1,
					'template'	=> 'categorie.php',
				);
				foreach(self::$EMPTY_FIELDS_CATEGORIE as $k) {
					$this->aCats[$cat_id][$k] = '';
				}

				if(!empty($this->plxPlugins)) {
					# Hook plugins
					eval($this->plxPlugins->callHook('plxAdminEditCategoriesNew'));
					$save = true;
				}
			}
		}
		# mise à jour de la liste des catégories
		elseif(!empty($content['update'])) {
			foreach($content['catNum'] as $cat_id) {
				$cat_name = $content[$cat_id.'_name'];
				if($cat_name!='') {
					$tmpstr = (!empty($content[$cat_id.'_url'])) ? $content[$cat_id.'_url'] : $cat_name;
					$cat_url = plxUtils::urlify($tmpstr);
					if(empty($cat_url)) $cat_url = L_DEFAULT_NEW_CATEGORY_URL;
					$this->aCats[$cat_id] = array(
						'name'		=> $cat_name,
						'url'		=> $cat_url,
						'tri'		=> $content[$cat_id.'_tri'],
						'bypage'	=> intval($content[$cat_id.'_bypage']),
						'menu'		=> $content[$cat_id.'_menu'],
						'active'	=> $content[$cat_id.'_active'],
						'ordre'		=> intval($content[$cat_id.'_ordre']),
						'homepage'	=> plxUtils::getValue($this->aCats[$cat_id]['homepage'], 1),
						'template'	=> plxUtils::getValue($this->aCats[$cat_id]['template'], 'categorie.php'),
					);

					foreach(self::$EMPTY_FIELDS_CATEGORIE as $k) {
						if(!array_key_exists($k, $this->aCats[$cat_id])) {
							$this->aCats[$cat_id][$k] = '';
						}
					}

					if(!empty($this->plxPlugins)) {
						# Hook plugins
						eval($this->plxPlugins->callHook('plxAdminEditCategoriesUpdate'));
						$save = true;
					}
				}
			}
			# On va trier les clés selon l'ordre choisi
			if(sizeof($this->aCats)>1) uasort($this->aCats, function($a, $b) { return $a["ordre"]>$b["ordre"]; });
		}

		if(empty($save)) { return; }

		# sauvegarde
		$cats_name = array();
		$cats_url = array();

		# On génére le fichier XML
		ob_start();
?>
<document>
<?php
		foreach($this->aCats as $cat_id => $cat) {

			# controle de l'unicité du nom de la categorie
			if(in_array($cat['name'], $cats_name)) {
				$this->aCats = $archive;
				return plxMsg::Error(L_ERR_CATEGORY_ALREADY_EXISTS.' : '.plxUtils::strCheck($cat['name']));
			}
			else
				$cats_name[] = $cat['name'];

			# controle de l'unicité de l'url de la catégorie
			if(in_array($cat['url'], $cats_url))
				return plxMsg::Error(L_ERR_URL_ALREADY_EXISTS.' : '.plxUtils::strCheck($cat['url']));
			else
				$cats_url[] = $cat['url'];
?>
	<categorie number="<?= $cat_id ?>" active="<?= $cat['active'] ?>" homepage="<?= $cat['homepage'] ?>" tri="<?= $cat['tri'] ?>" bypage="<?= $cat['bypage'] ?>" menu="<?= $cat['menu'] ?>" url="<?= $cat['url'] ?>" template="<?= basename($cat['template']) ?>">
		<name><?= plxUtils::cdataCheck($cat['name']) ?></name>
		<description><?= plxUtils::cdataCheck($cat['description']) ?></description>
		<meta_description><?= plxUtils::cdataCheck($cat['meta_description']) ?></meta_description>
		<meta_keywords><?= plxUtils::cdataCheck($cat['meta_keywords']) ?></meta_keywords>
		<title_htmltag><?= plxUtils::cdataCheck($cat['title_htmltag']) ?></title_htmltag>
		<thumbnail><?= $cat['thumbnail'] ?></thumbnail>
		<thumbnail_alt><?= plxUtils::cdataCheck($cat['thumbnail_alt']) ?></thumbnail_alt>
		<thumbnail_title><?= plxUtils::cdataCheck($cat['thumbnail_title']) ?></thumbnail_title>
<?php
			if(!empty($this->plxPlugins)) {
				# Hook plugins
				$xml = '';
				eval($this->plxPlugins->callHook('plxAdminEditCategoriesXml'));
				if(!empty($xml)) { echo $xml; }
			}
?>
	</categorie>
<?php
		}
?>
</document>
<?php
		# On écrit le fichier
		if(plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_CATEGORIES'))) {
			return plxMsg::Info(L_SAVE_SUCCESSFUL);
		}

		$this->aCats = $archive;
		return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_CATEGORIES'));
	}

	/**
	 * Méthode qui sauvegarde le contenu des options d'une catégorie
	 *
	 * @param	content	données à sauvegarder
	 * @return	string
	 * @author	Stephane F.
	 **/
	public function editCategorie($content) {
		# Mise à jour du fichier categories.xml
		$this->aCats[$content['id']]['homepage'] = intval($content['homepage']);
		$this->aCats[$content['id']]['description'] = trim($content['content']);
		$this->aCats[$content['id']]['template'] = $content['template'];
		$this->aCats[$content['id']]['thumbnail'] = $content['thumbnail'];
		$this->aCats[$content['id']]['thumbnail_title'] = $content['thumbnail_title'];
		$this->aCats[$content['id']]['thumbnail_alt'] = $content['thumbnail_alt'];
		$this->aCats[$content['id']]['title_htmltag'] = trim($content['title_htmltag']);
		$this->aCats[$content['id']]['meta_description'] = trim($content['meta_description']);
		$this->aCats[$content['id']]['meta_keywords'] = trim($content['meta_keywords']);

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditCategorie'));
		return $this->editCategories(null,true);
	}

	/**
	 * Méthode qui édite le fichier XML des pages statiques selon le tableau $content
	 *
	 * @param	content	tableau multidimensionnel des pages statiques
	 * @param	action	enregistre les catégories dans un fichier .xml
	 * @return	string
	 * @author	Stephane F.
	 **/
	public function editStatiques($content, $action=false) {

		$save = $this->aStats;

		if(empty($content['update'])) {
			# suppression
			if(!empty($content['selection']) AND $content['selection']=='delete' AND !empty($content['idStatic'])) {
				foreach($content['idStatic'] as $static_id) {
					$filename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$this->aStats[$static_id]['url'].'.php';
					if(is_file($filename)) unlink($filename);
					# si la page statique supprimée est la page d'accueil on met à jour le parametre
					if($static_id == $this->aConf['homestatic']) {
						$this->aConf['homestatic'] = '';
						$this->editConfiguration();
					}
					unset($this->aStats[$static_id]);
					$action = true;
				}
			}
		}
		else {
			# mise à jour de la liste des pages statiques
			foreach($content['staticNum'] as $static_id) {
				$stat_name = $content[$static_id . '_name'];
				if(!empty($stat_name)) {
					$url = (!empty($content[$static_id.'_url'])) ? plxUtils::urlify($content[$static_id . '_url']) : '';
					$stat_url = (!empty($url)) ? $url : plxUtils::urlify($stat_name);
					if(empty($stat_url)) {
						$stat_url = L_DEFAULT_NEW_STATIC_URL . '-' . $static_id;
					}
					# On vérifie si on a besoin de renommer le fichier de la page statique
					if(!empty($this->aStats[$static_id]) AND $this->aStats[$static_id]['url'] != $stat_url) {
						$oldfilename = PLX_ROOT . $this->aConf['racine_statiques'] . $static_id . '.' . $this->aStats[$static_id]['url'] . '.php';
						$newfilename = PLX_ROOT . $this->aConf['racine_statiques'] . $static_id . '.' . $stat_url . '.php';
						if(is_file($oldfilename)) {
							rename($oldfilename, $newfilename);
						}
					}
					$kOrder = $static_id . '_ordre';
					$this->aStats[$static_id] = array(
						'group'			=> trim($content[$static_id . '_group']),
						'name'			=> $stat_name,
						'url'			=> $stat_url,
						'active'		=> plxUtils::getValue($content[$static_id . '_active'], 0),
						'menu'			=> plxUtils::getValue($content[$static_id . '_menu'], 'oui'),
						'ordre'			=> array_key_exists($kOrder, $content) ? intval($content[$kOrder]) : count($this->aStats),
						'template'		=> plxUtils::getValue($content[$static_id . '_template'], 'static.php'),
					);

					foreach(self::$EMPTY_FIELD_STATIQUES as $k) {
						if(!array_key_exists($k, $this->aStats[$static_id])) {
							$this->aStats[$static_id][$k] = '';
						}
					}

					if(empty($this->aStats[$static_id]['date_creation'])) {
						$now = date('YmdHi');
						$this->aStats[$static_id]['date_creation']	= $now;
						$this->aStats[$static_id]['date_update']	= $now;
					}

					if(!empty($this->plxPlugins)) {
						# Hook plugins
						eval($this->plxPlugins->callHook('plxAdminEditStatiquesUpdate'));
					}

					$action = true;
				}
			}
			# On va trier les clés selon l'ordre choisi
			if(sizeof($this->aStats) > 1)
				uasort($this->aStats, function($a, $b){return $a["ordre"]>$b["ordre"];});
		}

		if(empty($action)) { return; }

		# sauvegarde
		$statics_name = array();
		$statics_url = array();

		# On génére le fichier XML
		ob_start();
?>
<document>
<?php
		foreach($this->aStats as $static_id => $static) {

			# controle de l'unicité du titre de la page
			if(in_array($static['name'], $statics_name))
				return plxMsg::Error(L_ERR_STATIC_ALREADY_EXISTS.' : '.plxUtils::strCheck($static['name']));
			else
				$statics_name[] = $static['name'];

			# controle de l'unicité de l'url de la page
			if(in_array($static['url'], $statics_url)) {
				$this->aStats = $save;
				return plxMsg::Error(L_ERR_URL_ALREADY_EXISTS.' : '.plxUtils::strCheck($static['url']));
			}
			else
				$statics_url[] = $static['url'];
?>
	<statique number="<?= $static_id ?>" active="<?= $static['active'] ?>" menu="<?= $static['menu'] ?>" url="<?= $static['url'] ?>" template="<?= basename($static['template']) ?>">
		<group><?= plxUtils::cdataCheck($static['group']) ?></group>
		<name><?= plxUtils::cdataCheck($static['name']) ?></name>
		<meta_description><?= plxUtils::cdataCheck($static['meta_description']) ?></meta_description>
		<meta_keywords><?= plxUtils::cdataCheck($static['meta_keywords']) ?></meta_keywords>
		<title_htmltag><?= plxUtils::cdataCheck($static['title_htmltag']) ?></title_htmltag>
		<date_creation><?= $static['date_creation'] ?></date_creation>
		<date_update><?= $static['date_update'] ?></date_update>
<?php
			if(!empty($this->plxPlugins)) {
				# Hook plugins
				$xml = '';
				eval($this->plxPlugins->callHook('plxAdminEditStatiquesXml')); # Hook Plugins
				if(!empty($xml)) { echo $xml; }
			}
?>
	</statique>
<?php
		}
?>
</document>
<?php
		# On écrit le fichier si une action valide a été faite
		if(plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_STATICS'))) {
			return plxMsg::Info(L_SAVE_SUCCESSFUL);
		}

		$this->aStats = $save;
		return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_STATICS'));
	}

	/**
	 * Méthode qui lit le fichier d'une page statique
	 *
	 * @param	num		numero du fichier de la page statique
	 * @return	string	contenu de la page
	 * @author	Stephane F.
	 **/
	public function getFileStatique($num) {

		# Emplacement de la page
		$filename = PLX_ROOT.$this->aConf['racine_statiques'].$num.'.'.$this->aStats[ $num ]['url'].'.php';
		if(file_exists($filename) AND filesize($filename) > 0) {
			if($f = fopen($filename, 'r')) {
				$content = fread($f, filesize($filename));
				fclose($f);
				# On retourne le contenu
				return $content;
			}
		}
		return null;
	}

	/**
	 * Méthode qui sauvegarde le contenu d'une page statique
	 *
	 * @param	content	données à sauvegarder
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function editStatique($content) {

		# Mise à jour du fichier statiques.xml
		$statId = $content['id'];

		if(!defined('PLX_INSTALLER')) {
			$dates = array();
			foreach(array('creation', 'update') as $k) {
				$buf = array();
				foreach(array('year', 'month', 'day') as $part) {
					$buf[] = trim($content['date_' . $k . '_' . $part]);
				}
				$buf[] = substr(str_replace(':','',trim($content['date_' . $k . '_time'])), 0, 4);
				$dates[$k] = implode('', $buf);
			}
			if($dates['update'] == $content['date_update']) {
				$dates['update'] = date('YmdHi');
			}
		} else {
			# Installation de PluXml
			$now = date('YmdHi');
			$dates = array(
				'creation'	=> $now,
				'update'	=> $now
			);
		}

		if(!empty($content['template']) and preg_match('@^static(.*)\.php$@', $content['template'])) {
			$this->aStats[$statId]['template'] = $content['template'];
		}

		foreach(array('title_htmltag', 'meta_description', 'meta_keywords') as $k) {
			if(array_key_exists($k, $content)) {
				$this->aStats[$statId][$k] = $content[$k];
			}
		}

		foreach(array('creation', 'update') as $k) {
			$this->aStats[$statId]['date_' . $k] = $dates[$k];
		}

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			eval($this->plxPlugins->callHook('plxAdminEditStatique'));
		}

		if($this->editStatiques(null, true)) {
			# Génération du nom du fichier de la page statique
			$filename = PLX_ROOT . $this->aConf['racine_statiques'] . $statId . '.' . $this->aStats[$statId]['url'] . '.php';
			# On écrit le fichier
			return (plxUtils::write($content['content'], $filename)) ? plxMsg::Info(L_SAVE_SUCCESSFUL) : plxMsg::Error(L_SAVE_ERR . ' ' . $filename);
		}
	}

	/**
	 *  Méthode qui retourne le prochain id d'un article
	 *
	 * @return	string	id d'un nouvel article sous la forme 0001
	 * @author	Stephane F.
	 **/
	public function nextIdArticle() {

		if($aKeys = array_keys($this->plxGlob_arts->aFiles)) {
			rsort($aKeys);
			return str_pad($aKeys['0']+1,4, '0', STR_PAD_LEFT);
		} else {
			return '0001';
		}
	}

	/**
	 * Méthode qui effectue une création ou mise a jour d'un article
	 *
	 * @param	content	données saisies de l'article
	 * @param	&id		retourne le numero de l'article
	 * @return	string
	 * @author	Stephane F., Florent MONTHEL
	 **/
	public function editArticle($content, &$id) {

		# Détermine le numero de fichier si besoin est
		if($id == '0000' OR $id == '')
			$id = $this->nextIdArticle();

		# Vérifie l'intégrité de l'identifiant
		if(!preg_match('/^_?\d{4}$/',$id)) {
			$id='';
			return L_ERR_INVALID_ARTICLE_IDENT;
		}

		# Génération de notre url d'article
		$tmpstr = (!empty($content['url'])) ? $content['url'] : $content['title'];
		$content['url'] = plxUtils::urlify($tmpstr);

		# URL vide après le passage de la fonction ;)
		if(empty($content['url'])) $content['url'] = L_DEFAULT_NEW_ARTICLE_URL;

		if(!empty($this->plxPlugins)) {
			# Hook plugins
			if(eval($this->plxPlugins->callHook('plxAdminEditArticle'))) return;
		}

		# Suppression des doublons dans les tags
		$tags = array_map('trim', explode(',', trim($content['tags'])));
		$tags_unique = array_unique($tags);
		$content['tags'] = implode(', ', $tags_unique);

		# Formate des dates de creation et de mise à jour
		if(!defined('PLX_INSTALLER')) {
			$date_creation = $content['date_creation_year'] . $content['date_creation_month'] . $content['date_creation_day'] . substr(str_replace(':', '', $content['date_creation_time']), 0, 4);
			$date_update = $content['date_update_year'] . $content['date_update_month'] . $content['date_update_day'] . substr(str_replace(':', '', $content['date_update_time']), 0, 4);
			$date_update = ($date_update == $content['date_update_old']) ? date('YmdHi') : $date_update;
			$date_publication = $content['date_publication_year'] . $content['date_publication_month'] . $content['date_publication_day'] . substr(str_replace(':', '', $content['date_publication_time']), 0, 4);
			if(!preg_match('/^\d{12}$/', $date_publication))  {
				$date_publication = date('YmdHi'); # Check de la date au cas ou...
			}
		} else {
			# Création 1er article à l'installation
			$now = date('YmdHi');
			$date_creation = $now;
			$date_update = $now;
			$date_publication = $now;
		}

		# Génération du fichier XML
		$meta_description = plxUtils::getValue($content['meta_description']);
		$meta_keywords = plxUtils::getValue($content['meta_keywords']);
		$title_htmltag = plxUtils::getValue($content['title_htmltag']);
		$thumbnail = plxUtils::getValue($content['thumbnail']);
		$thumbnail_alt = plxUtils::getValue($content['thumbnail_alt']);
		$thumbnail_title = plxUtils::getValue($content['thumbnail_title']);
		ob_start();
?>
<document>
		<title><?= plxUtils::cdataCheck(trim($content['title'])) ?></title>
		<allow_com><?= $content['allow_com'] ?></allow_com>
		<template><?= basename($content['template']) ?></template>
		<chapo><?= plxUtils::cdataCheck(trim($content['chapo'])) ?></chapo>
		<content><?= plxUtils::cdataCheck(trim($content['content'])) ?></content>
		<tags><?= plxUtils::cdataCheck(trim($content['tags'])) ?></tags>
		<meta_description><?= plxUtils::cdataCheck(trim($meta_description)) ?></meta_description>
		<meta_keywords><?= plxUtils::cdataCheck(trim($meta_keywords)) ?></meta_keywords>
		<title_htmltag><?= plxUtils::cdataCheck(trim($title_htmltag)) ?></title_htmltag>
		<thumbnail><?= trim($thumbnail) ?></thumbnail>
		<thumbnail_alt><?= plxUtils::cdataCheck(trim($thumbnail_alt)) ?></thumbnail_alt>
		<thumbnail_title><?= plxUtils::cdataCheck(trim($thumbnail_title)) ?></thumbnail_title>
		<date_creation><?= $date_creation ?></date_creation>
		<date_update><?= $date_update ?></date_update>
<?php
		if(!empty($this->plxPlugins)) {
			# Hook plugins
			$xml = '';
			eval($this->plxPlugins->callHook('plxAdminEditArticleXml'));
			if(!empty($xml)) { echo $xml; }
		}
?>
</document>
<?php
		if(!empty($this->plxGlob_arts)) {
			# Recherche du nom du fichier correspondant à l'id
			$oldArt = $this->plxGlob_arts->query('/^_?' . $id . '\.(?:.*)\.xml$/', '', 'sort', 0, 1, 'all');
		}

		if(!empty($content['publish']) OR !empty($content['draft'])) {
			# Si demande de publication
			$id = ltrim($id, '_');
		} elseif(!empty($content['moderate'])) {
			# Si demande de modération de l'article
			$id = '_' . ltrim($id, '_');
		}

		# On genère le nom de notre fichier
		if(empty($content['catId'])) { $content['catId'] = array('000'); } # Catégorie non classée
		$filename = PLX_ROOT.$this->aConf['racine_articles'].$id.'.'.implode(',', $content['catId']).'.'.trim($content['author']).'.'.$date_publication.'.'.$content['url'].'.xml';
		# On va mettre à jour notre fichier
		if(plxUtils::write(XML_HEADER . ob_get_clean(), $filename)) {
			# suppression ancien fichier si nécessaire
			if(!empty($oldArt)) {
				$oldfilename = PLX_ROOT . $this->aConf['racine_articles'] . $oldArt['0'];
				if($oldfilename != $filename AND file_exists($oldfilename)) {
					unlink($oldfilename);
				}
			}
			# mise à jour de la liste des tags
			$this->aTags[$id] = array(
				'tags'		=> trim($content['tags']),
				'date'		=> $date_publication,
				'active'	=> intval(!in_array('draft', $content['catId']))
			);
			$this->editTags();
			$msg = (empty($content['artId']) || $content['artId'] == '0000') ? L_ARTICLE_SAVE_SUCCESSFUL : L_ARTICLE_MODIFY_SUCCESSFUL;

			if(!empty($this->plxPlugins)) {
				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditArticleEnd'));
			}

			return plxMsg::Info($msg);
		} else {
			return plxMsg::Error(L_ARTICLE_SAVE_ERR);
		}
	}

	/**
	 * Méthode qui supprime un article et les commentaires associés
	 *
	 * @param	id	numero de l'article à supprimer
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function delArticle($id) {

		# Vérification de l'intégrité de l'identifiant
		if(!preg_match('/^_?[0-9]{4}$/',$id))
			return L_ERR_INVALID_ARTICLE_IDENT;
		# Variable d'état
		$resDelArt = $resDelCom = true;
		# Suppression de l'article
		if($globArt = $this->plxGlob_arts->query('/^'.$id.'.(.*).xml$/')) {
			unlink(PLX_ROOT.$this->aConf['racine_articles'].$globArt['0']);
			$resDelArt = !file_exists(PLX_ROOT.$this->aConf['racine_articles'].$globArt['0']);
		}
		# Suppression des commentaires
		if($globComs = $this->plxGlob_coms->query('/^_?'.str_replace('_','',$id).'.(.*).xml$/')) {
			$nb_coms=sizeof($globComs);
			for($i=0; $i<$nb_coms; $i++) {
				unlink(PLX_ROOT.$this->aConf['racine_commentaires'].$globComs[$i]);
				$resDelCom = (!file_exists(PLX_ROOT.$this->aConf['racine_commentaires'].$globComs[$i]) AND $resDelCom);
			}
		}

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxAdminDelArticle'))) return;

		# On renvoi le résultat
		if($resDelArt AND $resDelCom) {
			# mise à jour de la liste des tags
			if(isset($this->aTags[$id])) {
				unset($this->aTags[$id]);
				$this->editTags();
			}
			return plxMsg::Info(L_DELETE_SUCCESSFUL);
		}
		else
			return plxMsg::Error(L_ARTICLE_DELETE_ERR);
	}

	/**
	 * Méthode qui crée un nouveau commentaire pour l'article $artId
	 *
	 * @param	artId	identifiant de l'article en question
	 * @param	content	string contenu du nouveau commentaire
	 * @return	boolean
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function newCommentaire($artId, $content) {

		# On génère le contenu du commentaire
		$idx = $this->nextIdArtComment($artId);
		$time = time();
		$filename = $artId . '.' . $time . '-' . $idx . '.xml';
		$comment=array(
			'author'	=> plxUtils::strCheck($this->aUsers[$_SESSION['user']]['name']),
			'content'	=> strip_tags(trim($content['content']), '<a>,<strong>'),
			'site'		=> $this->racine,
			'ip'		=> plxUtils::getIp(),
			'type'		=> 'admin',
			'mail'		=> $this->aUsers[$_SESSION['user']]['email'],
			'parent'	=> $content['parent'],
			'filename'	=> $filename,
		);
		# On peut créer le commentaire
		if($this->addCommentaire($comment)) # Commentaire OK
			return true;
		else
			return false;
	}

	/**
	 * Méthode qui effectue une mise a jour d'un commentaire
	 *
	 * @param	content	données du commentaire à mettre à jour
	 * @param	id		identifiant du commentaire
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function editCommentaire($content, &$id) {

		# Vérification de la validité de la date de publication
		if(!plxDate::checkDate($content['date_publication_day'],$content['date_publication_month'],$content['date_publication_year'],$content['date_publication_time']))
			return plxMsg::Error(L_ERR_INVALID_PUBLISHING_DATE);

		$comment=array();
		# Génération du nom du fichier
		$comment['filename'] = $id.'.xml';
		if(!file_exists(PLX_ROOT.$this->aConf['racine_commentaires'].$comment['filename'])) # Commentaire inexistant
			return plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
		# Contrôle des saisies
		if(trim($content['mail'])!='' AND !plxUtils::checkMail(trim($content['mail'])))
			return plxMsg::Error(L_ERR_INVALID_EMAIL);
		if(trim($content['site'])!='' AND !plxUtils::checkSite($content['site']))
			return plxMsg::Error(L_ERR_INVALID_SITE);
		# On récupère les infos du commentaire
		$com = $this->parseCommentaire(PLX_ROOT.$this->aConf['racine_commentaires'].$comment['filename']);
		# Formatage des données
		if($com['type'] != 'admin') {
			$comment['author'] = plxUtils::strCheck(trim($content['author']));
			$comment['site'] = plxUtils::strCheck(trim($content['site']));
			$comment['content'] = plxUtils::strCheck(trim($content['content']));
		} else {
			$comment['author'] = trim($content['author']);
			$comment['site'] = trim($content['site']);
			$comment['content'] = strip_tags(trim($content['content']),'<a>,<strong>');
		}
		$comment['ip'] = $com['ip'];
		$comment['type'] = $com['type'];
		$comment['mail'] = $content['mail'];
		$comment['site'] = $content['site'];
		$comment['parent'] = $com['parent'];
		# Génération du nouveau nom du fichier
		$time = explode(':', $content['date_publication_time']);
		$newtimestamp = mktime($time[0], $time[1], 0, $content['date_publication_month'], $content['date_publication_day'], $content['date_publication_year']);
		$com = $this->comInfoFromFilename($id.'.xml');
		$newid = $com['comStatus'].$com['artId'].'.'.$newtimestamp.'-'.$com['comIdx'];
		$comment['filename'] = $newid.'.xml';
		# Suppression de l'ancien commentaire
		$this->delCommentaire($id);
		# Création du nouveau commentaire
		$id = $newid;
		if($this->addCommentaire($comment))
			return plxMsg::Info(L_COMMENT_SAVE_SUCCESSFUL);
		else
			return plxMsg::Error(L_COMMENT_UPDATE_ERR);
	}

	/**
	 * Méthode qui supprime un commentaire
	 *
	 * @param	id	identifiant du commentaire à supprimer
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function delCommentaire($id) {

		# Génération du nom du fichier
		$filename = PLX_ROOT.$this->aConf['racine_commentaires'].$id.'.xml';
		# Suppression du commentaire
		if(file_exists($filename)) {
			unlink($filename);
		}
		# On refait un test file_exists pour savoir si unlink à fonctionner
		if(!file_exists($filename))
			return plxMsg::Info(L_DELETE_SUCCESSFUL);
		else
			return plxMsg::Error(L_COMMENT_DELETE_ERR);
	}

	/**
	 * Méthode qui permet de modérer ou valider un commentaire
	 *
	 * @param	id	identifiant du commentaire à traiter (que l'on retourne)
	 * @param	mod	type de moderation (online ou offline)
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function modCommentaire(&$id, $mod) {

		$capture = '';

		# Génération du nom du fichier
		$oldfilename = PLX_ROOT.$this->aConf['racine_commentaires'].$id.'.xml';
		if(!file_exists($oldfilename)) # Commentaire inexistant
			return plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
		# Modérer ou valider ?
		if(preg_match('/([[:punct:]]?)[0-9]{4}.[0-9]{10}-[0-9]+$/',$id,$capture)) {
			$id=str_replace($capture[1],'',$id);
		}
		if($mod=='offline')
			$id = '_'.$id;
		# Génération du nouveau nom de fichier
		$newfilename = PLX_ROOT.$this->aConf['racine_commentaires'].$id.'.xml';
		# On renomme le fichier
		@rename($oldfilename,$newfilename);
		# Contrôle
		if(is_readable($newfilename)) {
			if($mod == 'online')
				return plxMsg::Info(L_COMMENT_VALIDATE_SUCCESSFUL);
			else
				return plxMsg::Info(L_COMMENT_MODERATE_SUCCESSFUL);
		} else {
			if($mod == 'online')
				return plxMsg::Error(L_COMMENT_VALIDATE_ERR);
			else
				return plxMsg::Error(L_COMMENT_MODERATE_ERR);
		}
	}

	/**
	 * Méthode qui sauvegarde la liste des tags dans fichier XML
	 * selon le contenu de la variable de classe $aTags
	 *
	 * @param	null
	 * @return	null
	 * @author	Stephane F
	 **/
	public function editTags() {

		# Génération du fichier XML
		ksort($this->aTags);
		ob_start();
?>
<document>
<?php
		foreach($this->aTags as $id => $tag) {
?>
	<article number="<?= $id ?>" date="<?= $tag['date'] ?>" active="<?= $tag['active'] ?>"><?= plxUtils::cdataCheck($tag['tags']) ?></article>
<?php
		}
?>
</document>
<?php
		# On écrit le fichier
		plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_TAGS'));

	}

	/**
	 * Méthode qui vérifie sur le site de PluXml la dernière version et la compare avec celle en local.
	 *
	 * @return	string	contenu innerHTML de la balise <p> contenant l'etat et le style du contrôle du numéro de version
	 * @author	Florent MONTHEL, Amaury GRAILLAT, Stephane F et J.P. Pourrez (aka bazooka07)
	 **/
	public function checkMaj() {

		$latest_version = 'L_PLUXML_UPDATE_ERR';
		$className = '';
		$this->update_link = sprintf('%s : <a href="%s">%s</a>', L_PLUXML_UPDATE_AVAILABLE, PLX_URL_REPO, PLX_URL_REPO);

		$http_response_header = '';
		# test avec allow_url_open ou file_get_contents ?
		if(ini_get('allow_url_fopen')) {
			$latest_version = @file_get_contents(PLX_URL_VERSION, false, null, 0, 16);
			if(
				empty($http_response_header) OR
				!preg_match('@^HTTP/[\d\.]+ 200@', $http_response_header[0]) OR
				empty($latest_version)
				) {
					$latest_version = 'UPDATE_UNAVAILABLE';
				}
		}
		# test avec curl
		elseif(function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_HEADER			=> false,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_URL				=> PLX_URL_VERSION
			));
			$latest_version = curl_exec($ch);
			$info = curl_getinfo($ch);
			if ($latest_version === false || $info['http_code'] != 200) {
				$latest_version = 'L_PLUXML_UPDATE_ERR';
			}
			curl_close($ch);
		}

		if($latest_version == 'UPDATE_UNAVAILABLE') {
			$msg = L_PLUXML_UPDATE_UNAVAILABLE;
			$className = 'red';
		}
		elseif($latest_version == 'L_PLUXML_UPDATE_ERR') {
			$msg = L_PLUXML_UPDATE_ERR;
			$className = 'red';
		}
		elseif(version_compare(PLX_VERSION, $latest_version, '>=')) {
			$msg = L_PLUXML_UPTODATE.' ('.PLX_VERSION.')';
			$className = 'green';
		}
		else {
			$msg = $this->update_link;
			$className = 'orange';
		}

		return sprintf('<p id="latest-version" class="alert %s">%s</p>', $className, $msg);

	}

}
