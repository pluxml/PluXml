<?php

/**
 * Classe plxAdmin responsable des modifications dans l'administration
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F et Pedro "P3ter" CADETE
 **/

const PLX_ADMIN = true;

class plxAdmin extends plxMotor {

	private static $instance = null;
	public $update_link = PLX_URL_REPO; // overwritten by self::checmMaj()

	/**
	 * Méthode qui se charger de créer le Singleton plxAdmin
	 *
	 * @return	self	return une instance de la classe plxAdmin
	 * @author	Stephane F
	 **/
	public static function getInstance(){
		if (!isset(self::$instance))
			self::$instance = new plxAdmin(path('XMLFILE_PARAMETERS'));
		return self::$instance;
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

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminConstruct'));
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
		$savePage = preg_match('/admin\/(index|comments).php/', $_SERVER['PHP_SELF']);
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
	 * @param	global	tableau contenant toute la configuration PluXml
	 * @param	content	tableau contenant la configuration à modifier
	 * @return	string
	 * @author	Florent MONTHEL
	 **/
	public function editConfiguration($global,$content) {

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditConfiguration'));

		foreach($content as $k=>$v) {
			if(!in_array($k,array('token','config_path'))) # parametres à ne pas mettre dans le fichier
				$global[$k] = $v;
		}
		# On teste la clef
		if(empty($global['clef'])) $global['clef'] = plxUtils::charAleatoire(15);

		# Début du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		foreach($global as $k=>$v) {
			if($k!='racine') {
				if(is_numeric($v))
					$xml .= "\t<parametre name=\"$k\">".$v."</parametre>\n";
				else
					$xml .= "\t<parametre name=\"$k\"><![CDATA[".plxUtils::cdataCheck($v)."]]></parametre>\n";
			}
		}
		$xml .= "</document>";

		# On réinitialise la pagination au cas où modif de bypage_admin
		unset($_SESSION['page']);

		# On réactulise la langue
		$_SESSION['lang'] = $global['default_lang'];

		# Actions sur le fichier htaccess
		if(isset($content['urlrewriting']))
			if(!$this->htaccess($content['urlrewriting'], $global['racine']))
				return plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, '.htaccess'));

		# Mise à jour du fichier parametres.xml
		if(!plxUtils::write($xml,path('XMLFILE_PARAMETERS')))
			return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_PARAMETERS'));

		# Si nouvel emplacement du dossier de configuration
		if(isset($content['config_path'])) {
			$newpath=trim($content['config_path']);
			if($newpath!=PLX_CONFIG_PATH) {
				# relocalisation du dossier de configuration de PluXml
				if(!rename(PLX_ROOT.PLX_CONFIG_PATH,PLX_ROOT.$newpath))
					return plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, $newpath));
				# mise à jour du fichier de configuration config.php
				if(!plxUtils::write("<?php define('PLX_CONFIG_PATH', '".$newpath."') ?>", PLX_ROOT.'config.php'))
					return plxMsg::Error(L_SAVE_ERR.' config.php');
			}
		}

		return plxMsg::Info(L_SAVE_SUCCESSFUL);

	}

	/**
	 * Méthode qui crée le fichier .htaccess en cas de réécriture d'urls
	 *
	 * @param	action	création (add) ou suppression (remove)
	 * @param	url		url du site
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
	 * @param	profil		profil(s) autorisé(s)
	 * @param	redirect	si VRAI redirige sur la page index.php en cas de mauvais profil(s)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function checkProfil($profil, $redirect=true) {
		$args = func_get_args();
		if($redirect===true or $redirect===false) $args=$args[0];
		if($redirect) {
			if(is_array($args)) {
				if(!in_array($_SESSION['profil'], $args)) {
					plxMsg::Error(L_NO_ENTRY);
					header('Location: index.php');
					exit;
				}
			} else {
				if($_SESSION['profil']!=$profil) {
					plxMsg::Error(L_NO_ENTRY);
					header('Location: index.php');
					exit;
				}
			}
		} else {
			if(is_array($args))
				return in_array($_SESSION['profil'], $args);
			else
				return $_SESSION['profil']==$profil;
		}
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
		/*
		$mail = array();
		$tokenExpiry = 24;
		$lostPasswordToken = plxToken::getTokenPostMethod('', false);
		$lostPasswordTokenExpiry = plxToken::generateTokenExperyDate($tokenExpiry);
		$templateName = 'email-lostpassword-'.PLX_SITE_LANG.'.xml';
		$error = false;
		* */
		if (!empty($loginOrMail) and plxUtils::testMail(false)) {
			foreach($this->aUsers as $user_id => $user) {
				if(!$user['active'] or $user['delete'] or empty($user['email'])) { continue; }

				if($user['login'] == $loginOrMail OR $user['email'] == $loginOrMail) {
					// Attention à l'unicité des logins !!!
					// token and e-mail creation
					$mail = array();
					$tokenExpiry = 24;
					$lostPasswordToken = plxToken::generateToken();
					$lostPasswordTokenExpiry = plxToken::generateTokenExperyDate($tokenExpiry);
					$templateName = 'email-lostpassword-'.PLX_SITE_LANG.'.xml';

					$placeholdersValues = array(
						"##LOGIN##"			=> $user['login'],
						"##URL_PASSWORD##"	=> $this->aConf['racine'].'core/admin/auth.php?action=changepassword&token='.$lostPasswordToken,
						"##URL_EXPIRY##"	=> $tokenExpiry
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

		$valid = false;

		foreach($this->aUsers as $user_id => $user) {
			if ($user['password_token'] == $token  AND $user['password_token_expiry'] >= date('YmdHi')) {
				$valid = true;
			}
		}
		return $valid;
	}

	/**
	 * Méthode qui édite le fichier XML des utilisateurs
	 *
	 * @param	content	tableau les informations sur les utilisateurs
	 * @return	string
	 * @author	Stéphane F, Pedro "P3ter" CADETE
	 **/
	public function editUsers($content, $action=false) {

		$save = $this->aUsers;

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxAdminEditUsersBegin'))) return;

		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idUser']) AND empty($content['update'])) {
			foreach($content['idUser'] as $user_id) {
				if($content['selection']=='delete' AND $user_id!='001') {
					$this->aUsers[$user_id]['delete']=1;
					$action = true;
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
						$this->aUsers = $save;
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

					$this->aUsers[$user_id]['login'] = trim($content[$user_id.'_login']);
					$this->aUsers[$user_id]['name'] = trim($content[$user_id.'_name']);
					$this->aUsers[$user_id]['active'] = ($_SESSION['user']==$user_id?$this->aUsers[$user_id]['active']:$content[$user_id.'_active']);
					$this->aUsers[$user_id]['profil'] = ($_SESSION['user']==$user_id?$this->aUsers[$user_id]['profil']:$content[$user_id.'_profil']);
					$this->aUsers[$user_id]['password'] = $password;
					$this->aUsers[$user_id]['salt'] = $salt;
					$this->aUsers[$user_id]['email'] = $email;

					$this->aUsers[$user_id]['delete'] = (isset($this->aUsers[$user_id]['delete'])?$this->aUsers[$user_id]['delete']:0);
					$this->aUsers[$user_id]['lang'] = (isset($this->aUsers[$user_id]['lang'])?$this->aUsers[$user_id]['lang']:$this->aConf['default_lang']);
					$this->aUsers[$user_id]['infos'] = (isset($this->aUsers[$user_id]['infos'])?$this->aUsers[$user_id]['infos']:'');

					$this->aUsers[$user_id]['password_token'] = (isset($this->aUsers[$user_id]['_password_token'])?$this->aUsers[$user_id]['_password_token']:'');
					$this->aUsers[$user_id]['password_token_expiry'] = (isset($this->aUsers[$user_id]['_password_token_expiry'])?$this->aUsers[$user_id]['_password_token_expiry']:'');
					# Hook plugins
					eval($this->plxPlugins->callHook('plxAdminEditUsersUpdate'));
					$action = true;
				}
			}
		}
		# sauvegarde
		if($action) {
			$users_name = array();
			$users_login = array();
			$users_email = array();

			# On génére le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";

			foreach($this->aUsers as $user_id => $user) {
				# controle de l'unicité du nom de l'utilisateur
			    if(in_array($user['name'], $users_name)) {
					$this->aUsers = $save;
					return plxMsg::Error(L_ERR_USERNAME_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['name']));
				}
				else if ($user['delete'] == 0) {
					$users_name[] = $user['name'];
				}
				# controle de l'unicité du login de l'utilisateur
				if(in_array($user['login'], $users_login)) {
					return plxMsg::Error(L_ERR_LOGIN_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['login']));
				}
				else if ($user['delete'] == 0) {
					$users_login[] = $user['login'];
				}
				# controle de l'unicité de l'adresse e-mail
				if(in_array($user['email'], $users_email)) {
					return plxMsg::Error(L_ERR_EMAIL_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['email']));
				}
				else if ($user['delete'] == 0) {
					$users_email[] = $user['email'];
				}
				$xml .= "\t".'<user number="'.$user_id.'" active="'.$user['active'].'" profil="'.$user['profil'].'" delete="'.$user['delete'].'">'."\n";
				$xml .= "\t\t".'<login><![CDATA['.plxUtils::cdataCheck($user['login']).']]></login>'."\n";
				$xml .= "\t\t".'<name><![CDATA['.plxUtils::cdataCheck($user['name']).']]></name>'."\n";
				$xml .= "\t\t".'<infos><![CDATA['.plxUtils::cdataCheck($user['infos']).']]></infos>'."\n";
				$xml .= "\t\t".'<password><![CDATA['.plxUtils::cdataCheck($user['password']).']]></password>'."\n";
				$xml .= "\t\t".'<salt><![CDATA['.plxUtils::cdataCheck($user['salt']).']]></salt>'."\n";
				$xml .= "\t\t".'<email><![CDATA['.plxUtils::cdataCheck($user['email']).']]></email>'."\n";
				$xml .= "\t\t".'<lang><![CDATA['.plxUtils::cdataCheck($user['lang']).']]></lang>'."\n";
				$xml .= "\t\t".'<password_token><![CDATA['.plxUtils::cdataCheck($user['password_token']).']]></password_token>'."\n";
				$xml .= "\t\t".'<password_token_expiry><![CDATA['.plxUtils::cdataCheck($user['password_token_expiry']).']]></password_token_expiry>'."\n";
				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditUsersXml'));
				$xml .= "\t</user>\n";
			}
			$xml .= "</document>";

			# On écrit le fichier
			if(plxUtils::write($xml,path('XMLFILE_USERS')))
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
				else {
					$this->aUsers = $save;
					return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_USERS'));
				}
		}
		else {
			return plxMsg::Error(L_SAVE_ERR);
		}
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
		if(is_array($this->aCats)) {
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
	 * @param	action	permet de forcer la mise àjour du fichier
	 * @return	string
	 * @author	Stephane F, Pedro "P3ter" CADETE, sudwebdesign
	 **/
	public function editCategories($content, $action=false) {

		$save = $this->aCats;

		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idCategory']) AND empty($content['update'])) {
			foreach($content['idCategory'] as $cat_id) {
				// change article category to the default category id
				foreach($this->plxGlob_arts->aFiles as $numart => $filename) {
					$filenameArray = explode(".", $filename);
					$filenameArrayCat = explode(",", $filenameArray[1]);
					if (in_array($cat_id, $filenameArrayCat)) {
						$key = array_search($cat_id, $filenameArrayCat);
						if(count(preg_grep('[0-9]{3}', $filenameArrayCat)) > 1) {
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
				$action = true;
			}
		}
		# Ajout d'une nouvelle catégorie à partir de la page article
		elseif(!empty($content['new_category'])) {
			$cat_name = $content['new_catname'];
			if($cat_name!='') {
				$cat_id = $this->nextIdCategory();
				$this->aCats[$cat_id]['name'] = $cat_name;
				$this->aCats[$cat_id]['url'] = plxUtils::urlify($cat_name);
				$this->aCats[$cat_id]['tri'] = $this->aConf['tri'];
				$this->aCats[$cat_id]['bypage'] = $this->aConf['bypage'];
				$this->aCats[$cat_id]['menu'] = 'oui';
				$this->aCats[$cat_id]['active'] = 1;
				$this->aCats[$cat_id]['homepage'] = 1;
				$this->aCats[$cat_id]['description'] = '';
				$this->aCats[$cat_id]['template'] = 'categorie.php';
				$this->aCats[$cat_id]['thumbnail'] = '';
				$this->aCats[$cat_id]['thumbnail_title'] = '';
				$this->aCats[$cat_id]['thumbnail_alt'] = '';
				$this->aCats[$cat_id]['title_htmltag'] = '';
				$this->aCats[$cat_id]['meta_description'] = '';
				$this->aCats[$cat_id]['meta_keywords'] = '';
				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditCategoriesNew'));
				$action = true;
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
					$this->aCats[$cat_id]['name'] = $cat_name;
					$this->aCats[$cat_id]['url'] = $cat_url;
					$this->aCats[$cat_id]['tri'] = $content[$cat_id.'_tri'];
					$this->aCats[$cat_id]['bypage'] = intval($content[$cat_id.'_bypage']);
					$this->aCats[$cat_id]['menu'] = $content[$cat_id.'_menu'];
					$this->aCats[$cat_id]['active'] = $content[$cat_id.'_active'];
					$this->aCats[$cat_id]['ordre'] = intval($content[$cat_id.'_ordre']);
					$this->aCats[$cat_id]['homepage'] = (isset($this->aCats[$cat_id]['homepage'])?$this->aCats[$cat_id]['homepage']:1);
					$this->aCats[$cat_id]['description'] = (isset($this->aCats[$cat_id]['description'])?$this->aCats[$cat_id]['description']:'');
					$this->aCats[$cat_id]['template'] = (isset($this->aCats[$cat_id]['template'])?$this->aCats[$cat_id]['template']:'categorie.php');
					$this->aCats[$cat_id]['thumbnail'] = (isset($this->aCats[$cat_id]['thumbnail'])?$this->aCats[$cat_id]['thumbnail']:'');
					$this->aCats[$cat_id]['thumbnail_title'] = (isset($this->aCats[$cat_id]['thumbnail_title'])?$this->aCats[$cat_id]['thumbnail_title']:'');
					$this->aCats[$cat_id]['thumbnail_alt'] = (isset($this->aCats[$cat_id]['thumbnail_alt'])?$this->aCats[$cat_id]['thumbnail_alt']:'');
					$this->aCats[$cat_id]['title_htmltag'] = (isset($this->aCats[$cat_id]['title_htmltag'])?$this->aCats[$cat_id]['title_htmltag']:'');
					$this->aCats[$cat_id]['meta_description'] = (isset($this->aCats[$cat_id]['meta_description'])?$this->aCats[$cat_id]['meta_description']:'');
					$this->aCats[$cat_id]['meta_keywords'] = (isset($this->aCats[$cat_id]['meta_keywords'])?$this->aCats[$cat_id]['meta_keywords']:'');
					# Hook plugins
					eval($this->plxPlugins->callHook('plxAdminEditCategoriesUpdate'));
					$action = true;
				}
			}
			# On va trier les clés selon l'ordre choisi
			if(sizeof($this->aCats)>0) uasort($this->aCats, function($a, $b){return $a["ordre"]>$b["ordre"];});
		}
		# sauvegarde
		if($action) {
			$cats_name = array();
			$cats_url = array();
			# On génére le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($this->aCats as $cat_id => $cat) {

				# controle de l'unicité du nom de la categorie
				if(in_array($cat['name'], $cats_name)) {
					$this->aCats = $save;
					return plxMsg::Error(L_ERR_CATEGORY_ALREADY_EXISTS.' : '.plxUtils::strCheck($cat['name']));
				}
				else
					$cats_name[] = $cat['name'];

				# controle de l'unicité de l'url de la catégorie
				if(in_array($cat['url'], $cats_url))
					return plxMsg::Error(L_ERR_URL_ALREADY_EXISTS.' : '.plxUtils::strCheck($cat['url']));
				else
					$cats_url[] = $cat['url'];

				$xml .= "\t<categorie number=\"".$cat_id."\" active=\"".$cat['active']."\" homepage=\"".$cat['homepage']."\" tri=\"".$cat['tri']."\" bypage=\"".$cat['bypage']."\" menu=\"".$cat['menu']."\" url=\"".$cat['url']."\" template=\"".basename($cat['template'])."\">";
				$xml .= "<name><![CDATA[".plxUtils::cdataCheck($cat['name'])."]]></name>";
				$xml .= "<description><![CDATA[".plxUtils::cdataCheck($cat['description'])."]]></description>";
				$xml .= "<meta_description><![CDATA[".plxUtils::cdataCheck($cat['meta_description'])."]]></meta_description>";
				$xml .= "<meta_keywords><![CDATA[".plxUtils::cdataCheck($cat['meta_keywords'])."]]></meta_keywords>";
				$xml .= "<title_htmltag><![CDATA[".plxUtils::cdataCheck($cat['title_htmltag'])."]]></title_htmltag>";
				$xml .= "<thumbnail><![CDATA[".plxUtils::cdataCheck($cat['thumbnail'])."]]></thumbnail>";
				$xml .= "<thumbnail_alt><![CDATA[".plxUtils::cdataCheck($cat['thumbnail_alt'])."]]></thumbnail_alt>";
				$xml .= "<thumbnail_title><![CDATA[".plxUtils::cdataCheck($cat['thumbnail_title'])."]]></thumbnail_title>";
				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditCategoriesXml'));
				$xml .= "</categorie>\n";
			}
			$xml .= "</document>";
			# On écrit le fichier
			if(plxUtils::write($xml,path('XMLFILE_CATEGORIES')))
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			else {
				$this->aCats = $save;
				return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_CATEGORIES'));
			}
		}
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
	 * @param	action	permet de forcer la mise àjour du fichier
	 * @return	string
	 * @author	Stephane F.
	 **/
	public function editStatiques($content, $action=false) {

		$save = $this->aStats;

		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idStatic']) AND empty($content['update'])) {
			foreach($content['idStatic'] as $static_id) {
				$filename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$this->aStats[$static_id]['url'].'.php';
				if(is_file($filename)) unlink($filename);
				# si la page statique supprimée est la page d'accueil on met à jour le parametre
				if($static_id==$this->aConf['homestatic']) {
					$this->aConf['homestatic']='';
					$this->editConfiguration($this->aConf,$this->aConf);
				}
				unset($this->aStats[$static_id]);
				$action = true;
			}
		}
		# mise à jour de la liste des pages statiques
		elseif(!empty($content['update'])) {
			foreach($content['staticNum'] as $static_id) {
				$stat_name = $content[$static_id.'_name'];
				if($stat_name!='') {
					$url = (!empty($content[$static_id.'_url'])) ? plxUtils::urlify($content[$static_id.'_url']) : '';
					$stat_url = (!empty($url)) ? $url : plxUtils::urlify($stat_name);
					if($stat_url=='') $stat_url = L_DEFAULT_NEW_STATIC_URL;
					# On vérifie si on a besoin de renommer le fichier de la page statique
					if(isset($this->aStats[$static_id]) AND $this->aStats[$static_id]['url']!=$stat_url) {
						$oldfilename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$this->aStats[$static_id]['url'].'.php';
						$newfilename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$stat_url.'.php';
						if(is_file($oldfilename)) rename($oldfilename, $newfilename);
					}
					$this->aStats[$static_id]['group'] = trim($content[$static_id.'_group']);
					$this->aStats[$static_id]['name'] = $stat_name;
					$this->aStats[$static_id]['url'] = plxUtils::checkSite($url)?$url:$stat_url;
					$this->aStats[$static_id]['active'] = $content[$static_id.'_active'];
					$this->aStats[$static_id]['menu'] = $content[$static_id.'_menu'];
					$this->aStats[$static_id]['ordre'] = intval($content[$static_id.'_ordre']);
					$this->aStats[$static_id]['template'] = (isset($this->aStats[$static_id]['template'])?$this->aStats[$static_id]['template']:'static.php');
					$this->aStats[$static_id]['title_htmltag'] = (isset($this->aStats[$static_id]['title_htmltag'])?$this->aStats[$static_id]['title_htmltag']:'');
					$this->aStats[$static_id]['meta_description'] = (isset($this->aStats[$static_id]['meta_description'])?$this->aStats[$static_id]['meta_description']:'');
					$this->aStats[$static_id]['meta_keywords'] = (isset($this->aStats[$static_id]['meta_keywords'])?$this->aStats[$static_id]['meta_keywords']:'');
					if(plxUtils::getValue($this->aStats[$static_id]['date_creation'])=='') {
						$this->aStats[$static_id]['date_creation'] = date('YmdHi');
						$this->aStats[$static_id]['date_update'] = date('YmdHi');
					}
					# Hook plugins
					eval($this->plxPlugins->callHook('plxAdminEditStatiquesUpdate'));
					$action = true;
				}
			}
			# On va trier les clés selon l'ordre choisi
			if(sizeof($this->aStats)>0)
				uasort($this->aStats, function($a, $b){return $a["ordre"]>$b["ordre"];});
		}
		# sauvegarde
		if($action) {
			$statics_name = array();
			$statics_url = array();
			# On génére le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
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

				$xml .= "\t<statique number=\"".$static_id."\" active=\"".$static['active']."\" menu=\"".$static['menu']."\" url=\"".$static['url']."\" template=\"".basename($static['template'])."\">";
				$xml .= "<group><![CDATA[".plxUtils::cdataCheck($static['group'])."]]></group>";
				$xml .= "<name><![CDATA[".plxUtils::cdataCheck($static['name'])."]]></name>";
				$xml .= "<meta_description><![CDATA[".plxUtils::cdataCheck($static['meta_description'])."]]></meta_description>";
				$xml .= "<meta_keywords><![CDATA[".plxUtils::cdataCheck($static['meta_keywords'])."]]></meta_keywords>";
				$xml .= "<title_htmltag><![CDATA[".plxUtils::cdataCheck($static['title_htmltag'])."]]></title_htmltag>";
				$xml .= "<date_creation><![CDATA[".plxUtils::cdataCheck($static['date_creation'])."]]></date_creation>";
				$xml .= "<date_update><![CDATA[".plxUtils::cdataCheck($static['date_update'])."]]></date_update>";
				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditStatiquesXml')); # Hook Plugins
				$xml .=	"</statique>\n";
			}
			$xml .= "</document>";
			# On écrit le fichier si une action valide a été faite
			if(plxUtils::write($xml,path('XMLFILE_STATICS')))
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			else {
				$this->aStats = $save;
				return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_STATICS'));
			}
		}
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
		$this->aStats[$content['id']]['template'] = $content['template'];
		$this->aStats[$content['id']]['title_htmltag'] = trim($content['title_htmltag']);
		$this->aStats[$content['id']]['meta_description'] = trim($content['meta_description']);
		$this->aStats[$content['id']]['meta_keywords'] = trim($content['meta_keywords']);
		$this->aStats[$content['id']]['date_creation'] = trim($content['date_creation_year']).trim($content['date_creation_month']).trim($content['date_creation_day']).substr(str_replace(':','',trim($content['date_creation_time'])),0,4);
		$date_update = $content['date_update'];
		$date_update_user = trim($content['date_update_year']).trim($content['date_update_month']).trim($content['date_update_day']).substr(str_replace(':','',trim($content['date_update_time'])),0,4);
		$date_update = ($date_update==$date_update_user) ? date('YmdHi') : $date_update_user;
		$this->aStats[$content['id']]['date_update'] = $date_update;

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditStatique'));
		if($this->editStatiques(null,true)) {
			# Génération du nom du fichier de la page statique
			$filename = PLX_ROOT.$this->aConf['racine_statiques'].$content['id'].'.'.$this->aStats[ $content['id'] ]['url'].'.php';
			# On écrit le fichier
			if(plxUtils::write($content['content'],$filename))
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			else
				return plxMsg::Error(L_SAVE_ERR.' '.$filename);
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
		if(!preg_match('/^_?[0-9]{4}$/',$id)) {
			$id='';
			return L_ERR_INVALID_ARTICLE_IDENT;
		}

		# Génération de notre url d'article
		$tmpstr = (!empty($content['url'])) ? $content['url'] : $content['title'];
		$content['url'] = plxUtils::urlify($tmpstr);

		# URL vide après le passage de la fonction ;)
		if($content['url'] == '') $content['url'] = L_DEFAULT_NEW_ARTICLE_URL;

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxAdminEditArticle'))) return;

		# Suppression des doublons dans les tags
		$tags = array_map('trim', explode(',', trim($content['tags'])));
		$tags_unique = array_unique($tags);
		$content['tags'] = implode(', ', $tags_unique);

		# Formate des dates de creation et de mise à jour
		$date_creation = $content['date_creation_year'].$content['date_creation_month'].$content['date_creation_day'].substr(str_replace(':','',$content['date_creation_time']),0,4);
		$date_update = $content['date_update_year'].$content['date_update_month'].$content['date_update_day'].substr(str_replace(':','',$content['date_update_time']),0,4);
		$date_update = $date_update==$content['date_update_old'] ? date('YmdHi') : $date_update;
		# Génération du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		$xml .= "\t".'<title><![CDATA['.plxUtils::cdataCheck(trim($content['title'])).']]></title>'."\n";
		$xml .= "\t".'<allow_com>'.$content['allow_com'].'</allow_com>'."\n";
		$xml .= "\t".'<template><![CDATA['.basename($content['template']).']]></template>'."\n";
		$xml .= "\t".'<chapo><![CDATA['.plxUtils::cdataCheck(trim($content['chapo'])).']]></chapo>'."\n";
		$xml .= "\t".'<content><![CDATA['.plxUtils::cdataCheck(trim($content['content'])).']]></content>'."\n";
		$xml .= "\t".'<tags><![CDATA['.plxUtils::cdataCheck(trim($content['tags'])).']]></tags>'."\n";
		$meta_description = plxUtils::getValue($content['meta_description']);
		$xml .= "\t".'<meta_description><![CDATA['.plxUtils::cdataCheck(trim($meta_description)).']]></meta_description>'."\n";
		$meta_keywords = plxUtils::getValue($content['meta_keywords']);
		$xml .= "\t".'<meta_keywords><![CDATA['.plxUtils::cdataCheck(trim($meta_keywords)).']]></meta_keywords>'."\n";
		$title_htmltag = plxUtils::getValue($content['title_htmltag']);
		$xml .= "\t".'<title_htmltag><![CDATA['.plxUtils::cdataCheck(trim($title_htmltag)).']]></title_htmltag>'."\n";
		$thumbnail = plxUtils::getValue($content['thumbnail']);
		$xml .= "\t".'<thumbnail><![CDATA['.plxUtils::cdataCheck(trim($thumbnail)).']]></thumbnail>'."\n";
		$thumbnail_alt = plxUtils::getValue($content['thumbnail_alt']);
		$xml .= "\t".'<thumbnail_alt><![CDATA['.plxUtils::cdataCheck(trim($thumbnail_alt)).']]></thumbnail_alt>'."\n";
		$thumbnail_title = plxUtils::getValue($content['thumbnail_title']);
		$xml .= "\t".'<thumbnail_title><![CDATA['.plxUtils::cdataCheck(trim($thumbnail_title)).']]></thumbnail_title>'."\n";
		$xml .= "\t".'<date_creation><![CDATA['.plxUtils::cdataCheck($date_creation).']]></date_creation>'."\n";
		$xml .= "\t".'<date_update><![CDATA['.plxUtils::cdataCheck($date_update).']]></date_update>'."\n";
		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditArticleXml'));
		$xml .= "</document>\n";
		# Recherche du nom du fichier correspondant à l'id
		$oldArt = $this->plxGlob_arts->query('/^'.$id.'.(.*).xml$/','','sort',0,1,'all');

		# Si demande de modération de l'article
		if(isset($content['moderate']))
			$id = '_'.str_replace('_','',$id);
		# Si demande de publication
		if(isset($content['publish']) OR isset($content['draft']))
			$id = str_replace('_','',$id);

		# On genère le nom de notre fichier
		$time = $content['date_publication_year'].$content['date_publication_month'].$content['date_publication_day'].substr(str_replace(':','',$content['date_publication_time']),0,4);
		if(!preg_match('/^[0-9]{12}$/',$time)) $time = date('YmdHi'); # Check de la date au cas ou...
		if(empty($content['catId'])) $content['catId']=array('000'); # Catégorie non classée
		$filename = PLX_ROOT.$this->aConf['racine_articles'].$id.'.'.implode(',', $content['catId']).'.'.trim($content['author']).'.'.$time.'.'.$content['url'].'.xml';
		# On va mettre à jour notre fichier
		if(plxUtils::write($xml,$filename)) {
			# suppression ancien fichier si nécessaire
			if($oldArt) {
				$oldfilename = PLX_ROOT.$this->aConf['racine_articles'].$oldArt['0'];
				if($oldfilename!=$filename AND file_exists($oldfilename))
					unlink($oldfilename);
			}
			# mise à jour de la liste des tags
			$this->aTags[$id] = array('tags'=>trim($content['tags']), 'date'=>$time, 'active'=>intval(!in_array('draft', $content['catId'])));
			$this->editTags();
			$msg = ($content['artId'] == '0000' OR $content['artId'] == '') ? L_ARTICLE_SAVE_SUCCESSFUL : L_ARTICLE_MODIFY_SUCCESSFUL;
			# Hook plugins
			eval($this->plxPlugins->callHook('plxAdminEditArticleEnd'));
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
	public function newCommentaire($artId,$content) {

		# On génère le contenu du commentaire
		$comment=array();
		$comment['author'] = plxUtils::strCheck($this->aUsers[$_SESSION['user']]['name']);
		$comment['content'] = strip_tags(trim($content['content']),'<a>,<strong>');
		$comment['site'] = $this->racine;
		$comment['ip'] = plxUtils::getIp();
		$comment['type'] = 'admin';
		$comment['mail'] = $this->aUsers[$_SESSION['user']]['email'];
		$comment['parent'] = $content['parent'];
		$idx = $this->nextIdArtComment($artId);
		$time = time();
		$comment['filename'] = $artId.'.'.$time.'-'.$idx.'.xml';
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
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		foreach($this->aTags as $id => $tag) {
			$xml .= "\t".'<article number="'.$id.'" date="'.$tag['date'].'" active="'.$tag['active'].'"><![CDATA['.plxUtils::cdataCheck($tag['tags']).']]></article>'."\n";
		}
		$xml .= "</document>";

		# On écrit le fichier
		plxUtils::write($xml, path('XMLFILE_TAGS'));

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
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, PLX_URL_VERSION);
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
		elseif(version_compare(PLX_VERSION, $latest_version, ">=")) {
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
