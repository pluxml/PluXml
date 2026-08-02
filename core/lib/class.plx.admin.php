<?php

/**
 * Classe plxAdmin responsable des modifications dans l'administration
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F et Pedro "P3ter" CADETE
 **/

const PLX_ADMIN = true;

class plxAdmin extends plxMotor {

	# Some functions of PHP are banned !
	const CRITICAL_FUNCTIONS_PHP_PATTERN = '#\b(exec|shell_exec|system|parse_ini_file|passthru|proc_open|popen|show_source|phpinfo)\b#';
	const TOKEN_LENGHT = 32;
	const TOKEN_PASSWORD_EXPIRY = 6; // in hours
	public $update_link = PLX_URL_REPO; // overwritten by self::checkMaj()

	/**
	 * Méthode qui se charger de créer le Singleton plxAdmin
	 *
	 * @return	self	return une instance de la classe plxAdmin
	 * @author	Stephane F
	 **/
	public static function getInstance() {
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
		$this->getTemplates(self::PLX_TEMPLATES); # for lost passwords

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
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F, J-Pierre Pourrez @bazooka07
	 **/
	public function getPage() {

		# Initialisation
		$pageName = basename($_SERVER['PHP_SELF'], '.php');
		$savePage = preg_match('~^(?:index|comments)$~', $pageName);
		# On check pour avoir le numero de page
		if(!empty($_GET['page']) AND is_numeric($_GET['page']) AND $_GET['page'] > 0) {
			$this->page = intval($_GET['page']);

			if($savePage) {
				# On sauvegarde
				$_SESSION['page'][$pageName] = $this->page;
			}
		} elseif($savePage) {
			if(!empty($_POST['sel_cat'])) {
				$this->page = 1;
			} elseif(!empty($_SESSION['page'][$pageName])) {
				$this->page = $_SESSION['page'][$pageName];
				return;
			} else {
				$this->page = 1;
			}
			# On sauvegarde
			$_SESSION['page'][$pageName] = $this->page;
		} else {
			$this->page = 1;
		}

	}

	/**
	 * Méthode qui édite le fichier XML de configuration selon le tableau $global et $content
	 *
	 * @param	global	tableau contenant toute la configuration PluXml
	 * @param	content	tableau contenant la configuration à modifier
	 * @return	string
	 * @author	Florent MONTHEL
	 **/
	public function editConfiguration($content) {

		$global = $this->aConf;

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditConfiguration'));

		$error = false;
		foreach($content as $k=>$v) {
			if(in_array($k, array('token','config_path')) or !array_key_exists($k, $global)) {
				# parametres à ne pas mettre dans le fichier
				continue;
			}

			# voir $config dans install.php
			switch($k) {
				# chaine de caractères
				case 'title' :
				case 'description' :
				case 'meta_description' :
				case 'meta_keywords' :
				case 'feed_footer' :
					$global[$k] = htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, PLX_CHARSET, false);
					break;

				# valeurs booléennes
				case 'allow_com' :
				case 'mod_com' :
				case 'mod_art' :
				case 'enable_rss' :
				case 'enable_rss_comment' :
				case 'capcha' :
				case 'lostpassword' :
				case 'urlrewriting' :
				case 'gzip' :
				case 'userfolders' :
				case 'display_empty_cat' :
				case 'thumbs' :
				case 'feed_chapo' :
					$global[$k] = ($v == '1') ? 1 : 0;
					break;

				# Nb articles : valeur entière positive et limmitée à 100 arbitrairement
				case 'bypage' :
				case 'bypage_archives' :
				case 'bypage_tags' :
				case 'bypage_admin' :
				case 'bypage_admin_coms' :
				case 'bypage_feed' :
					$v_int = intval($v);
					if($v_int > 0 and $v_int < 100) {
						$global[$k] = $v_int;
					} else {
						$error = true;
					}
					break;

				# set de valeurs
				case 'tri' :
				case 'tri_coms' :
					if(preg_match('#^(?:r?alpha|asc|desc|random)$#', $v)) {
						$global[$k] = $v;
					} else {
						$error = true;
					}
					break;

				case 'timezone' :
					if(plxTimezones::isValid($v)) {
						$global[$k] = $v;
					} else {
						$error = true;
					}
					break;

				# On vérifie que le dossier existe et l'absence de /../
				case 'medias' :
				case 'racine_articles' :
				case 'racine_commentaires' :
				case 'racine_statiques' :
				case 'racine_themes' :
				case 'racine_plugins' :
					$folder = realpath(PLX_ROOT . $v);
					if(preg_match('#^' . realpath(PLX_ROOT) . '/#', $folder) and is_dir($folder)) {
						$global[$k] = preg_replace('#/*$#', '/', $v);
					} else {
						$error = true;
					}
					break;
				case 'style' :
					$folder = realpath(PLX_ROOT . $this->aConf['racine_themes'] . $v);
					# Maybe we have a symbolic link
					# if(preg_match('#^' . realpath(PLX_ROOT) . '/#', $folder) and is_dir($folder)) {
					if(preg_match('#^' . realpath($_SERVER['DOCUMENT_ROOT']) . '/#', $folder) and is_dir($folder)) {
						$global[$k] = basename(rtrim($folder, '/'));
					} else {
						$error = true;
					}
					break;
				case 'clef' :
					if(empty(trim($v))) {
						$global[$k] = plxUtils::charAleatoire(15);
					}
					break;

				case 'images_l' :
				case 'images_h' :
				case 'miniatures_l' :
				case 'miniatures_h' :
					$v_int = intval($v);
					if($v_int > 0 and $v_int < 2500) {
						$global[$k] = $v_int;
					} else {
						$error = true;
					}
					break;

				case 'homestatic' :
					$w = trim($v);
					if(empty($w) or array_key_exists($w, $this->aStats)) {
						$global[$k] = $w;
					} else {
						$error = true;
					}
					break;
				case 'hometemplate' :
					if(preg_match('#^home(?:-\w[\w-]*)\.php$#', $v)) {
						$global[$k] = $v;
					} else {
						$error = true;
					}
					break;
				case  'default_lang' :
					if(preg_match('#^[a-z]{2}$#', $v) and plxUtils::lang_exists($v)) {
						$global[$k] = $v;
					} else {
						$error = true;
					}
					break;
				case 'custom_admincss_file' :
					$w = trim($v);
					if(strlen($w) == 0 or mime_content_type(realpath(PLX_ROOT . $w) == 'text/css')) {
						$global[$k] = $w;
					} else {
						$error = true;
					}
					break;
				case 'email_method' :
					if(in_array($v, array('sendmail', 'smtp', 'smtpoauth'))) {
						$global[$k] = $v;
					} else {
						$error = true;
					}
					break;
				case 'smtp_server' :
					$w = trim($v);
					if(strlen($w) == 0 or filter_var($w, FILTER_VALIDATE_DOMAIN)) {
						$global[$k] = $w;
					} else {
						$error = true;
					}
					break;
				case 'smtp_username' :
					if(preg_match('#^[\w-]+$#' , $v) or !empty(filter_var($v, FILTER_VALIDATE_EMAIL))) {
						$global[$k] = $v;
					} else {
						$error = true;
					}
					break;
				case 'smtp_password' :
				case 'smtpOauth2_clientId' :
				case 'smtpOauth2_clientSecret' :
				case 'smtpOauth2_refreshToken' :
					$global[$k] = plxUtils::strCheck($v);
					break;
				case 'smtp_port' :
					$v_int = intval($v);
					if($v_int > 0 and $v_int < 65536) {
						$global[$k] = $v;
					} else {
						$error = true;
					}
					break;
				case 'smtp_security' :
					if(in_array($v, array('', 'ssl', 'tls'))) {
						$global[$k] = $v;
					} else {
						$error = true;
					}
					break;
				case 'smtpOauth2_emailAdress' :
					if(!empty(filter_var($v, FILTER_VALIDATE_EMAIL))){
						$global[$k] = $v;
					}
					break;
				case 'version' :
					if($content['version'] == PLX_VERSION) {
						$global[$k] = PLX_VERSION;
					} else {
						$error = true;
					}
					break;
			}

			if($error) {
				return plxMsg::ERROR(L_UNKNOWN_ERROR . ' (' . $k . '=' . $v . ')');
			}
		}

		# Début du fichier XML
		ob_start();
?>
<document>
<?php
		foreach($global as $k=>$v) {
			if(in_array($k, array('racine', 'plugins'))) {
				continue;
			}

			if(is_integer($v)) {
				$value = intval($v);
			} elseif($k == 'feed_footer') {
				$value = '<![CDATA['  .plxUtils::cdataCheck($v) . ']]>';
			} else {
				$value = plxUtils::strCheck($v);
			}
?>
	<parametre name="<?= $k ?>"><?= $value ?></parametre>
<?php
		}
?>
</document>
<?php
		# On réinitialise la pagination au cas où modif de bypage_admin
		unset($_SESSION['page']);

		# On réactulise la langue
		$_SESSION['lang'] = $global['default_lang'];

		# Actions sur le fichier htaccess
		if(array_key_exists('urlrewriting', $content))
			if(!$this->htaccess($content['urlrewriting'], $global['racine'])) {
				ob_clean();
				return plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, '.htaccess'));
			}

		# Mise à jour du fichier parametres.xml
		if(!plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_PARAMETERS'))) {
			return plxMsg::Error(L_SAVE_ERR . ' ' . path('XMLFILE_PARAMETERS'));
		}

		# Si nouvel emplacement du dossier de configuration
		if(isset($content['config_path'])) {
			$newpath = trim($content['config_path']);
			if($newpath != PLX_CONFIG_PATH) {
				# relocalisation du dossier de configuration de PluXml
				if(!rename(PLX_ROOT.PLX_CONFIG_PATH,PLX_ROOT.$newpath))
					return plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, $newpath));
				# mise à jour du fichier de configuration config.php
				if(!plxUtils::write('<?php const PLX_CONFIG_PATH = \'' . $newpath . '\';' . PHP_EOL, PLX_ROOT.'config.php'))
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
	 * @author	Stéphane F, J.P. Pourrez @bazooka07
	 **/
	public function editProfil($content) {

		if(plxUtils::checkProfil($content) !== true) {
			return false;
		}

		$this->aUsers[$_SESSION['user']]['name'] = $content['fullname'];
		$this->aUsers[$_SESSION['user']]['infos'] = plxUtils::strCheck($content['content']);
		$this->aUsers[$_SESSION['user']]['email'] = $content['email'];
		$this->aUsers[$_SESSION['user']]['lang'] = $content['lang'];

		$_SESSION['admin_lang'] = $content['lang'];

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxAdminEditProfil'))) {
			return;
		}

		return $this->editUsers(null, true);
	}

	/**
	 * Méthode qui édite le mot de passe d'un utilisateur
	 *
	 * @param	content	tableau contenant le nouveau mot de passe de l'utilisateur
	 * @return	string
	 * @author	Stéphane F, PEdro "P3ter" CADETE, Jean-Pierre Pourrez @bazooka07
	 **/
	public function editPassword($content) {

		if(plxUtils::checkProfil($content) !== true) {
			return false;
		}

		if(isset($content['lostPasswordToken'])) {
			$token = $content['lostPasswordToken'];
			$users = array_filter($this->aUsers, function($infos) use($token) {
				return ($infos['password_token'] == $token);
			});
			if(count($users) != 1) {
				return false;
			}

			$user_id = array_keys($users)[0];
			$this->aUsers[$user_id]['password_token'] = '';
			$this->aUsers[$user_id]['password_token_expiry'] = '';
		} else {
			$user_id = $_SESSION['user'];
		}

		$salt = plxUtils::charAleatoire(10);
		$this->aUsers[$user_id]['salt'] = $salt;
		$this->aUsers[$user_id]['password'] = sha1($salt . md5($content['password']));
		return $this->editUsers(null, true);
	}

	/**
	* Create a token and send a link by e-mail using "email-lostpassword.xml" template
	*
	* @param loginOrMail user login or e-mail address
	* @return true of false
	* @throws \PHPMailer\PHPMailer\Exception
	* @author Pedro "P3ter" CADETE, J.P. Pourrez aka bazooka07
	**/
	public function sendLostPasswordEmail($loginOrMail) {

		if(!preg_match('#^(https?://.*/' . PAGE_LOGIN . ')\?action=lostpassword$#', $_SERVER['HTTP_REFERER'], $matches)) {
			return 'Bad HTTP_REFERER';
		}

		if (!empty($loginOrMail) and plxUtils::testMail(false)) {
			foreach($this->aUsers as $user_id => $user) {
				if(!$user['active'] or $user['delete'] or empty($user['email'])) {
					continue;
				}

				if($user['login'] == $loginOrMail OR $user['email'] == $loginOrMail) {
					# On a trouvé l'utilisateur
					if(empty($user['password_token_expiry']) or $user['password_token_expiry'] < date('YmdHis')) {
						// token and e-mail creation
						$mail = array();
						$lostPasswordToken = plxToken::getTokenPostMethod(self::TOKEN_LENGHT, false);
						$lostPasswordTokenExpiry = plxToken::generateTokenExperyDate(self::TOKEN_PASSWORD_EXPIRY);
						$templateName = 'email-lostpassword-'.PLX_SITE_LANG.'.xml';
						if(!array_key_exists($templateName, $this->aTemplates)) {
						    $templateName = 'email-lostpassword-' . DEFAULT_LANG .'.xml';
						}

						$path1 = $matches[1];
						$placeholdersValues = array(
							"##LOGIN##"			=> $user['login'],
							"##URL_PASSWORD##"	=> $path1 . '?action=changepassword&token=' . $lostPasswordToken,
							"##URL_EXPIRY##"	=> self::TOKEN_PASSWORD_EXPIRY,
						);

						if (($mail ['body'] = $this->aTemplates[$templateName]->getTemplateGeneratedContent($placeholdersValues)) != '1') {
							$mail['subject'] = $this->aTemplates[$templateName]->getTemplateEmailSubject();

							if(empty($this->aConf['email_method']) or $this->aConf['email_method'] == 'sendmail' or !method_exists(plxUtils::class, 'sendMailPhpMailer')) {
								# fonction mail() intrinsèque à PHP
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

							if ($success) {
								# Mail sent
								$this->aUsers[$user_id]['password_token'] = $lostPasswordToken;
								$this->aUsers[$user_id]['password_token_expiry'] = $lostPasswordTokenExpiry;
								$this->editUsers($user_id, true);
								return true;
							}

							return L_MAIL_NOT_AVAILABLE;
						}
					} else {
						$duration = (strtotime($user['password_token_expiry']) - time()) / 60; # float minutes
						return sprintf(L_LOST_PASSWORD_WAIT, $duration);
					}
					break;
				}
			}
			return L_USER_UNKNOWN;
		}

		return L_ERR_WRONG_PASSWORD;
	}

	/**
	 * Verify the lost password token validity
	 *
	 * @param	token	the token to verify
	 * @return	boolean	true if the token exist and is not expire
	 * @author	Pedro "P3ter" CADETE, Jean-Pierre Pourrez @bazooka07
	 */
	public function verifyLostPasswordToken($token) {
		if(preg_match('#^\w{' . self::TOKEN_LENGHT . '}$#', $token)) {
			$now = date('YmdHi');
			foreach($this->aUsers as $user_id => $user) {
				if ($user['password_token'] == $token) {
					return ($user['password_token_expiry'] >= $now);
				}
			}
		}

		return false;
	}

	public function resetPasswordToken($user_id) {
		$save = false;
		foreach(array('password_token', 'password_token_expiry',) as $k) {
			if(!empty($this->aUsers[$user_id][$k])) {
				$this->aUsers[$user_id][$k] = '';
				$save = true;
			}
		}
		if($save) {
			return $this->editUsers(null, true);
		}

		return true;
	}

	/**
	 * Méthode qui édite le fichier XML des utilisateurs
	 *
	 * @param	content	tableau les informations sur les utilisateurs
	 * @return	string
	 * @author	Stéphane F, Pedro "P3ter" CADETE, , J.P. Pourrez @bazooka07
	 **/
	public function editUsers($content, $action=false) {

		$save = $this->aUsers;

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxAdminEditUsersBegin'))) return;

		# suppression
		if(!empty($content['selection'])) {
			if($content['selection']=='delete' AND isset($content['idUser']) AND empty($content['update'])) {
				foreach($content['idUser'] as $user_id) {
					if(!preg_match('#^\d{3}$#', $user_id) or !array_key_exists($user_id, $this->aUsers) or $user_id == '001') {
						# $user_id invalide ou pas de suppression pour le 1er utilisateur (webmaster)
						continue;
					}

					$this->aUsers[$user_id]['delete'] = 1;
					$this->aUsers[$user_id]['active'] = 0;
					foreach(array(/* 'login', 'name', */ 'password', 'salt', 'infos', 'email', 'lang', 'password_token', 'password_token_expiry',) as $k) {
						# Suppression des données personnelles
						$this->aUsers[$user_id][$k] = '';
					}
					$action = true;
				}
			} else {
				return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_USERS'));
			}
		}

		# mise à jour de la liste des utilisateurs
		elseif(!empty($content['update'])) {
			# On récupère le dernier identifiant
			$a = array_keys($this->aUsers);
			rsort($a);
			$new_userid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);

			foreach($content['users'] as $user_id=>$user_infos) {
				$new_user = ($user_id == $new_userid);
				if($new_user and empty(trim($user_infos['fullname'])) and empty(trim($user_infos['fullname']))) {
					# Pas de nouvel utilisateur
					continue;
				}

				if(!array_key_exists($user_id, $this->aUsers) and !$new_user) {
					continue;
				}

				if(!$new_user and empty(trim($user_infos['password']))) {
					unset($user_infos['password']);
				}

				if(plxUtils::checkProfil($user_infos) !== true) {
					return false;
				}

				if($new_user) {
					# Nouvel utilisateur dans le tableau des utilisateurs
					$this->aUsers[$user_id] = array(
						'delete'				=> 0,
						'lang'					=> $this->aConf['default_lang'],
						'infos'					=> '',
						'password_token'		=> '',
						'password_token_expiry'	=> '',
					);
				}

				if(isset($user_infos['password'])) {
					if($new_user and empty($user_infos['password'])) {
						# Mot de passe obligatoire pour un nouvel utilisateur
						$this->aUsers = $save;
						return plxMsg::Error(L_ERR_INVALID_PASSWORD.' ('.L_CONFIG_USER.' <em>'.$username.'</em>)');
					}

					$salt = plxUtils::charAleatoire(10);
					$this->aUsers[$user_id]['salt'] = $salt;
					$this->aUsers[$user_id]['password'] = sha1($salt . md5($user_infos['password']));
				}

				if($_SESSION['user'] == $user_id) {
					$active = $this->aUsers[$user_id]['active'];
					$profil = $this->aUsers[$user_id]['profil'];
				} else {
					$active = ($content[$user_id.'_active'] == '1' ? 1 : 0);
					$profil = filter_var(
						$content[$user_id.'_profil'],
						FILTER_VALIDATE_INT,
						array(
							'options'	=> array(
								'default'	=> PROFIL_WRITER,
								'min'		=> 0,
								'max'		=> PROFIL_WRITER,
							),
						)
					);

					$this->aUsers[$user_id]['login'] = $user_info['login'];
					$this->aUsers[$user_id]['name'] = $user_info['fullname'];
					$this->aUsers[$user_id]['active'] = $active;
					$this->aUsers[$user_id]['profil'] = $profil;
				}

				$this->aUsers[$user_id]['login'] = $user_infos['login'];
				$this->aUsers[$user_id]['name'] = $user_infos['fullname'];
				$this->aUsers[$user_id]['active'] = ($user_id == '001') ? 1 : $user_infos['active'];
				$this->aUsers[$user_id]['profil'] = ($user_id == '001') ? PROFIL_ADMIN : $user_infos['profil'];
				$this->aUsers[$user_id]['email'] = $user_infos['email'];

				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditUsersUpdate'));
			}

			$action = true;
		}

		if($action!== true) {
			return plxMsg::Error(L_SAVE_ERR);
		}

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
				$this->aUsers = $save;
				return plxMsg::Error(L_ERR_USERNAME_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['name']));
			}

			if (empty($user['delete'])) {
				$users_name[] = $user['name'];

				# controle de l'unicité du login de l'utilisateur
				if(in_array($user['login'], $users_login)) {
					return plxMsg::Error(L_ERR_LOGIN_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['login']));
				}
				$users_login[] = $user['login'];

				# controle de l'unicité de l'adresse e-mail
				if(in_array($user['email'], $users_email)) {
					return plxMsg::Error(L_ERR_EMAIL_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['email']));
				}
				$users_email[] = $user['email'];
			}

			$infos = !empty(trim($user['infos'])) ? '<![CDATA[' . plxUtils::cdataCheck($user['infos'], true) . ']]>' : '';
?>
	<user number="<?= $user_id ?>" active="<?= $user['active'] ?>" profil="<?= $user['profil'] ?>" delete="<?= $user['delete'] ?>">
		<login><?= $user['login'] ?></login>
		<name><?= $user['name'] ?></name>
		<infos><?= $infos ?></infos>
		<password><?= $user['password'] ?></password>
		<salt><?= $user['salt'] ?></salt>
		<email><?= $user['email'] ?></email>
		<lang><?= $user['lang'] ?></lang>
		<password_token><?= $user['password_token'] ?></password_token>
		<password_token_expiry><?= $user['password_token_expiry'] ?></password_token_expiry>
<?php
			# Hook plugins
			eval($this->plxPlugins->callHook('plxAdminEditUsersXml'));
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

		# Echec à l'écriture du fichier
		$this->aUsers = $save;
		return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_USERS'));
	}

	/**
	 * Méthode qui sauvegarde le contenu des options d'un utilisateur
	 *
	 * @param	content	données à sauvegarder
	 * @return	string
	 * @author	Stephane F., J.P. Pourrez @bazooka07
	 **/
	public function editUser($content) {

		if(plxUtils::checkProfil($content) !== true) {
			return false;
		}

		$this->aUsers[$content['id']]['email'] = $content['email'];
		$this->aUsers[$content['id']]['infos'] = plxUtils::strCheck($content['content']);
		$this->aUsers[$content['id']]['lang'] = $content['lang'];

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditUser'));

		return $this->editUsers(null, true);
	}

	/**
	 *  Méthode qui retourne le prochain id d'une catégorie
	 *
	 * @return	string	id d'un nouvel article sous la forme 001
	 * @author	Stephane F., J.P. Pourrez "bazooka07"
	 **/
	public function nextIdCategory() {
		if(is_array($this->aCats) and count($this->aCats) > 0) {
			$catIds = array_keys($this->aCats);
			rsort($catIds);
			return str_pad(intval($catIds[0]) + 1, 3, '0', STR_PAD_LEFT);
		} else {
			return '001';
		}
	}

	/**
	 * Méthode qui édite le fichier XML des catégories selon le tableau $content
	 *
	 * @param	content	tableau multidimensionnel des catégories
	 * @param	action	permet de forcer la mise à jour du fichier
	 * @return	string
	 * @author	Stephane F, Pedro "P3ter" CADETE, sudwebdesign
	 **/
	public function editCategories($content, $action=false) {

		$save = $this->aCats;

		# suppression
		if(!empty($content['selection'])) {
			if($content['selection']=='delete' AND isset($content['idCategory']) AND empty($content['update'])) {
				foreach($content['idCategory'] as $cat_id) {
					if(!array_key_exists($cat_id, $this->aCats)) {
						# unknown $cat_id
						continue;
					}

					// change article category to the default category id
					foreach($this->plxGlob_arts->aFiles as $numart => $filename) {
						$filenameArray = explode('.', $filename);
						$filenameArrayCat = explode(',', $filenameArray[1]);
						if (in_array($cat_id, $filenameArrayCat)) {
							$key = array_search($cat_id, $filenameArrayCat);
							if(count(preg_grep('@\d{3}@', $filenameArrayCat)) > 1) {
								// this article has more than one category
								unset($filenameArrayCat[$key]);
							}
							else {
								$filenameArrayCat[$key] = '000';
							}
							$filenameArray[1] = implode(',', $filenameArrayCat);
							$filenameNew = implode('.', $filenameArray);
							$articles_folder = PLX_ROOT . $this->aConf['racine_articles'];
							rename($articles_folder . $filename, $articles_folder . $filenameNew);
						}
					}
					unset($this->aCats[$cat_id]);
					$action = true;
				}
			} else {
				return plxMsg::Error(L_SAVE_ERR . ' ' . path('XMLFILE_CATEGORIES'));
			}
		}
		# Ajout d'une nouvelle catégorie à partir de la page article
		elseif(!empty($content['new_category'])) {
			$cat_name = plxUtils::strCheck($content['new_catname']);
			if($cat_name != '') {
				$cat_id = $this->nextIdCategory();
				$this->aCats[$cat_id] = array(
					'name'				=> $cat_name,
					'url'				=> plxUtils::urlify($cat_name),
					'tri'				=> $this->aConf['tri'],
					'bypage'			=> $this->aConf['bypage'],
					'menu'				=> 'oui',
					'active'			=> 1,
					'homepage'			=> 1,
					'description'		=> '',
					'template'			=> 'categorie.php',
					'thumbnail'			=> '',
					'thumbnail_title'	=> '',
					'thumbnail_alt'		=> '',
					'title_htmltag'		=> '',
					'meta_description'	=> '',
					'meta_keywords'		=> '',
				);

				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditCategoriesNew'));
				$action = true;
			}
		}
		# mise à jour de la liste des catégories
		elseif(!empty($content['update'])) {
			foreach($content['catNum'] as $cat_id) {
				if(!preg_match('#^\d{3}$#', $cat_id)) {
					$this->aCats = $save;
					return plxMsg::Error(L_SAVE_ERR . ' ' . path('XMLFILE_CATEGORIES'));
				}

				$cat_name = plxUtils::strCheck($content[$cat_id . '_name']);
				if($cat_name != '') {
					if(!array_key_exists($cat_id, $this->aCats)) {
						# a new item has added in the table of categories. Values may be change by self::editCategorie(...) later
						$this->aCats[$cat_id] = array(
							'homepage'			=> 1,
							'description'		=> '',
							'template'			=> 'categorie.php',
							'thumbnail'			=> '',
							'thumbnail_title'	=> '',
							'thumbnail_alt'		=> '',
							'title_htmltag'		=> '',
							'meta_description'	=> '',
							'meta_keywords'		=> '',
						);
					}
					$tmpstr = (!empty($content[$cat_id . '_url'])) ? $content[$cat_id . '_url'] : $cat_name;
					$cat_url = plxUtils::urlify($tmpstr);
					if(empty($cat_url)) {
						$cat_url = L_DEFAULT_NEW_CATEGORY_URL . '-' . $cat_id;
					}
					$tri = $content[$cat_id . '_tri'];
					if(!preg_match('#^(?:r?alpha|asc|desc|random)$#', $tri)) {
						$tri = $this->aConf['tri'];
					}
					$bypage = intval($content[$cat_id . '_bypage']);
					$this->aCats[$cat_id]['name'] = $cat_name;
					$this->aCats[$cat_id]['url'] = $cat_url;
					$this->aCats[$cat_id]['tri'] = $tri;
					$this->aCats[$cat_id]['bypage'] = !empty($bypage) ? $bypage : $this->aConf['bypage'];
					$this->aCats[$cat_id]['menu'] = (strtolower($content[$cat_id.'_menu']) == 'oui') ? 'oui' : 'non';
					$this->aCats[$cat_id]['active'] = ($content[$cat_id . '_active'] == '1') ? '1' : '0';
					$this->aCats[$cat_id]['ordre'] = intval($content[$cat_id . '_ordre']);

					# Hook plugins
					eval($this->plxPlugins->callHook('plxAdminEditCategoriesUpdate'));
				}

				# On va trier les clés selon l'ordre choisi
				if(sizeof($this->aCats) > 1) {
					uasort($this->aCats, function($a, $b) {
						return intval($a['ordre']) - intval($b['ordre']);
					});
				}

				$action = true;
			}
		}

		if($action !== true) {
			return;
		}

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
			$cat['name'] = htmlentities(trim($cat['name']));
			if(empty($cat['name'])) {
				# Nom obligatoire
				continue;
			}

			if(in_array($cat['name'], $cats_name)) {
				$this->aCats = $save;
				return plxMsg::Error(L_ERR_CATEGORY_ALREADY_EXISTS.' : '.plxUtils::strCheck($cat['name']));
			} else {
				$cats_name[] = $cat['name'];
			}

			# controle de l'unicité de l'url de la catégorie
			if(in_array($cat['url'], $cats_url))
				return plxMsg::Error(L_ERR_URL_ALREADY_EXISTS.' : '.plxUtils::strCheck($cat['url']));
			else
				$cats_url[] = $cat['url'];
?>
	<categorie number="<?= $cat_id ?>" active="<?= $cat['active'] ?>" homepage="<?= $cat['homepage'] ?>" tri="<?= $cat['tri'] ?>" bypage="<?= $cat['bypage'] ?>" menu="<?= $cat['menu'] ?>" url="<?= $cat['url'] ?>" template="<?= basename($cat['template']) ?>">
		<name><?= $cat['name'] ?></name>
		<description><![CDATA[<?= plxUtils::cdataCheck($cat['description'], true) ?>]]></description>
		<meta_description><?= $cat['meta_description'] ?></meta_description>
		<meta_keywords><?= $cat['meta_keywords'] ?></meta_keywords>
		<title_htmltag><?= $cat['title_htmltag'] ?></title_htmltag>
		<thumbnail><?= $cat['thumbnail'] ?></thumbnail>
		<thumbnail_alt><?= $cat['thumbnail_alt'] ?></thumbnail_alt>
		<thumbnail_title><?= $cat['thumbnail_title'] ?></thumbnail_title>
<?php
				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditCategoriesXml'));
?>
	</categorie>
<?php
		}
?>
</document>
<?php
		# On écrit le fichier
		if(plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_CATEGORIES')))
			return plxMsg::Info(L_SAVE_SUCCESSFUL);
		else {
			$this->aCats = $save;
			return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_CATEGORIES'));
		}
	}

	/**
	 * Méthode qui sauvegarde le contenu des options d'une catégorie
	 *
	 * @param	content	données à sauvegarder
	 * @return	string
	 * @author	Stephane F., Jean-Pierre Pourrez @bazooka07
	 **/
	public function editCategorie($content) {
		$template = 'categorie.php';
		if(preg_match('#^categorie\b[\w-]*\.php$#', $content['template'])) {
			$template = $content['template'];
		}

		# Vérifier si le thumbnail existe comme self::editArticle()
		$filename = PLX_ROOT . $content['thumbnail'];
		if(!file_exists($filename) or exif_imagetype($filename) === false ) {
			$content['thumbnail'] = '';
			$content['thumbnail_alt'] = '';
		} else {
			$content['thumbnail_alt'] = trim($content['thumbnail_alt']);
			if(!empty($content['thumbnail_alt'])) {
				$content['thumbnail_alt'] = plxUtils::strCheck($content['thumbnail_alt']);
			} else {
				$content['thumbnail_alt'] = basename($content['thumbnail']);
			}
		}
		$this->aCats[$content['id']]['homepage'] = ($content['homepage'] === '1') ? 1 : 0;
		$this->aCats[$content['id']]['description'] = strip_tags($content['content'], self::ENABLED_HTML_TAGS_COMMENTS);
		$this->aCats[$content['id']]['template'] = $template;
		$this->aCats[$content['id']]['thumbnail'] = $content['thumbnail'];
		$this->aCats[$content['id']]['thumbnail_alt'] = $content['thumbnail_alt'];
		$this->aCats[$content['id']]['thumbnail_title'] = plxUtils::strCheck(trim($content['thumbnail_title']));
		$this->aCats[$content['id']]['title_htmltag'] = plxUtils::strCheck(trim($content['title_htmltag']));
		$this->aCats[$content['id']]['meta_description'] = plxUtils::strCheck(trim($content['meta_description']));
		$this->aCats[$content['id']]['meta_keywords'] = plxUtils::strCheck(trim($content['meta_keywords']));

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditCategorie'));

		# Mise à jour du fichier categories.xml
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

		if(!empty($content)) {
			# suppression
			if(!isset($content['update'])) {
				if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idStatic'])) {
					foreach($content['idStatic'] as $static_id) {
						if(!preg_match('#^\d{3}$#', $static_id)) {
							# $static_id invalide
							continue;
						}

						$filename = PLX_ROOT . $this->aConf['racine_statiques'] . $static_id . '.' . $this->aStats[$static_id]['url'] . '.php';
						if(is_file($filename))  {
							unlink($filename);
						}
						# si la page statique supprimée est la page d'accueil on met à jour le parametre
						if($static_id == $this->aConf['homestatic']) {
							$this->editConfiguration(array('homestatic' => ''));
						}

						if(isset($this->aStats[$static_id])) {
							unset($this->aStats[$static_id]);
							$action = true;
						}
					}
				}
			}
			# mise à jour de la liste des pages statiques
			elseif(!empty($content['update'])) {
				foreach($content['staticNum'] as $static_id) {
					if(!preg_match('#^\d{3}$#', $static_id)) {
						# $static_id invalide
						continue;
					}

					$stat_name = plxUtils::strCheck($content[$static_id . '_name']);
					if(empty($stat_name)) {
						continue;
					}

					$url = (!empty($content[$static_id.'_url'])) ? plxUtils::urlify($content[$static_id.'_url']) : '';
					$stat_url = (!empty($url)) ? $url : plxUtils::urlify($stat_name);
					if(empty($stat_url)) {
							$stat_url = L_DEFAULT_NEW_STATIC_URL . '-' . intval($static_id);
					}

					if(!array_key_exists($static_id, $this->aStats)) {
						# a new item has added in the table of categories. Values may be change by self::editStatique(...) later
						$this->aStats[$static_id] = array(
							'template'			=> 'static.php',
							'title_htmltag'		=> '',
							'meta_description'	=> '',
							'meta_keywords'		=> '',
							'date_creation'		=> date('YmdHi'),
							'date_update'		=> date('YmdHi'),
						);
					} elseif($this->aStats[$static_id]['url'] != $stat_url) {
						# On vérifie si on a besoin de renommer ou supprimer le fichier de la page statique
						$prefix = PLX_ROOT . $this->aConf['racine_statiques'] . $static_id . '.';
						$oldfilename = $prefix . $this->aStats[$static_id]['url'] . '.php';
						if(is_file($oldfilename)) {
							if(plxUtils::checkSite($stat_url, false)) {
								# lien externe => pas de fichier !
								unlink($oldfilename);
							} else {
								# lien interne
								$newfilename = $prefix . $stat_url . '.php';
								rename($oldfilename, $newfilename);
							}
						}
					}

					$this->aStats[$static_id]['group'] = plxUtils::strCheck($content[$static_id.'_group']);
					$this->aStats[$static_id]['name'] = $stat_name;
					$this->aStats[$static_id]['url'] = $stat_url;
					$this->aStats[$static_id]['active'] = ($content[$static_id . '_active'] == '1') ? 1 : 0;
					$this->aStats[$static_id]['menu'] = ($content[$static_id . '_menu'] == 'oui') ? 'oui' : 'non';
					$this->aStats[$static_id]['ordre'] = intval($content[$static_id . '_ordre']);

					# Hook plugins
					eval($this->plxPlugins->callHook('plxAdminEditStatiquesUpdate'));
				}

				# On va trier les clés selon l'ordre choisi
				if(sizeof($this->aStats) > 1) {
					uasort($this->aStats, function($a, $b) { return intval($a['ordre']) - intval($b['ordre']); } );
				}

				$action = true;
			}
		}

		if(!$action) {
			return;
		}

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
			if(in_array($static['name'], $statics_name)) {
				return plxMsg::Error(L_ERR_STATIC_ALREADY_EXISTS.' : '.plxUtils::strCheck($static['name']));
			}

			$statics_name[] = $static['name'];

			# controle de l'unicité de l'url de la page
			if(in_array($static['url'], $statics_url)) {
				$this->aStats = $save;
				return plxMsg::Error(L_ERR_URL_ALREADY_EXISTS.' : '.plxUtils::strCheck($static['url']));
			}

			$statics_url[] = $static['url'];
?>
	<statique number="<?= $static_id ?>" active="<?= $static['active'] ?>" menu="<?= $static['menu'] ?>" url="<?= $static['url'] ?>" template="<?= basename($static['template']) ?>">
		<group><?= $static['group'] ?></group>
		<name><?= $static['name'] ?></name>
		<meta_description><?= plxUtils::strCheck($static['meta_description']) ?></meta_description>
		<meta_keywords><?= plxUtils::strCheck($static['meta_keywords']) ?></meta_keywords>
		<title_htmltag><?= plxUtils::strCheck($static['title_htmltag']) ?></title_htmltag>
		<date_creation><?= $static['date_creation'] ?></date_creation>
		<date_update><?= $static['date_update'] ?></date_update>
<?php
			# Hook plugins
			eval($this->plxPlugins->callHook('plxAdminEditStatiquesXml'));
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
		return plxMsg::Error(L_SAVE_ERR . ' ' . path('XMLFILE_STATICS'));
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
			$content = file_get_contents($filename);
			if(is_string($content)) {
				# On retourne le contenu
				return $content;
			} else {
				return implode(PHP_EOL, array('<p>', "\t" . L_UNKNOWN_ERROR, '</p>'));
			}
		}
		return implode(PHP_EOL, array('<p>', "\t" . L_STATICS_NEW_PAGE, '</p>'));
	}

	/**
	 * Méthode qui sauvegarde le contenu d'une page statique
	 *
	 * @param	content	données à sauvegarder
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function editStatique($content) {

		$static_id = $content['id'];
		if(!preg_match('#^\d{3}$#', $static_id)) {
			return plxMsg::Error(L_SAVE_ERR . ' ' . path('XMLFILE_STATICS'));
		}

		# Vérifie si le code PHP de la page statique contient des fonctions critiques
		if(preg_match(self::CRITICAL_FUNCTIONS_PHP_PATTERN, $content['content'], $matches)) {
			error_log('use of ' . $matches[1] . ' from PHP banned in static page');
			return plxMsg::Error(L_PHP_ERROR_LOG);
		}

		# vérification du template
		$template = $content['template'];
		$this->aStats[$static_id]['template'] = preg_match('#^static[\w\.-]*\.php$#', $template) ? $template : 'static.php';
		# Valeurs échappées par plxUtils::strCheck() dans self::editStatiques()
		$this->aStats[$static_id]['title_htmltag'] = trim($content['title_htmltag']);
		$this->aStats[$static_id]['meta_description'] = trim($content['meta_description']);
		$this->aStats[$static_id]['meta_keywords'] = trim($content['meta_keywords']);

		# Formate des dates de creation et de mise à jour
		$now = date('YmdHi');
		$date_creation = trim($content['date_creation_year']).trim($content['date_creation_month']).trim($content['date_creation_day']).substr(str_replace(':','',trim($content['date_creation_time'])),0,4);
		if(!preg_match('#^\d{12}$#', $date_creation)) {
			$date_creation = $now;
		}
		$this->aStats[$static_id]['date_creation'] = $date_creation;
		$date_update_user = trim($content['date_update_year']).trim($content['date_update_month']).trim($content['date_update_day']).substr(str_replace(':','',trim($content['date_update_time'])),0,4);
		if(!preg_match('#^\d{12}$#', $date_update_user)) {
			$date_update_user = $now;
		}
		$date_update = $content['date_update'];
		$this->aStats[$static_id]['date_update'] = ($date_update == $date_update_user) ? $now : $date_update_user;

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditStatique'));

		# Mise à jour du fichier statiques.xml
		if($this->editStatiques(null, true)) {
			# Génération du nom du fichier de la page statique
			$filename = PLX_ROOT . $this->aConf['racine_statiques'] . $static_id . '.' . $this->aStats[$static_id]['url'] . '.php';
			# On écrit le fichier
			if(plxUtils::write($content['content'], $filename)) {
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			}

			return plxMsg::Error(L_SAVE_ERR . ' ' . $filename);
		}

		return plxMsg::Error(L_UNKNOWN_ERROR);
	}

	/**
	 *  Méthode qui retourne le prochain id d'un article
	 *
	 * @return	string	id d'un nouvel article sous la forme 0001
	 * @author	Stephane F., J.P. Pourrez "bazooka07"
	 **/
	public function nextIdArticle() {

		$aKeys = array_keys($this->plxGlob_arts->aFiles);
		if(is_array($aKeys) and count($aKeys) > 0) {
			rsort($aKeys);
			return str_pad(intval($aKeys['0']) + 1, 4, '0', STR_PAD_LEFT);
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
	 * @author	Stephane F., Florent MONTHEL, Jean-Pierre Pourrez @bazooka07
	 **/
	public function editArticle($content, &$id) {

		# Détermine le numero de fichier si besoin est
		if($id == '0000' OR $id == '') {
			$id = $this->nextIdArticle();
		} else {
			# Vérifie l'intégrité de l'identifiant
			if(!preg_match('/^_?\d{4}$/',$id)) {
				$id = '';
				return L_ERR_INVALID_ARTICLE_IDENT;
			}
		}

		# Génération de notre url d'article
		$tmpstr = (!empty($content['url'])) ? $content['url'] : $content['title'];

		# Remove non-alphanumeric characters
		$content['url'] = plxUtils::urlify($tmpstr);
		# URL vide après le passage de la fonction ;)
		if(empty($content['url'])) {
			$content['url'] = L_DEFAULT_NEW_ARTICLE_URL;
		}

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxAdminEditArticle'))) return;

		# Suppression des doublons et utilisation des entités HTML dans les tags
		$tags_unique = array_map(
			'\plxUtils::strCheck', # https://www.php.net/manual/fr/function.array-map.php#128742
			array_unique(
				array_map(
					'trim',
					explode(',', trim($content['tags']))
				)
			)
		);
		$content['tags'] = implode(', ', $tags_unique);

		# Formate des dates de creation et de mise à jour
		$now = date('YmdHi');
		$date_creation = $content['date_creation_year'].$content['date_creation_month'].$content['date_creation_day'].substr(str_replace(':','',$content['date_creation_time']),0,4);
		if(!preg_match('#^\d{12}$#', $date_creation)) {
			$date_creation = $now;
		}
		$date_update = $content['date_update_year'].$content['date_update_month'].$content['date_update_day'].substr(str_replace(':','',$content['date_update_time']),0,4);
		$date_update = (
			!preg_match('#^\d{12}$#', $date_update) or
			!preg_match('#^\d{12}$#', $content['date_update_old']) or
			$date_update == $content['date_update_old']
		) ? $now : $date_update;

		$meta_description = plxUtils::getValue($content['meta_description']);
		$meta_keywords = plxUtils::getValue($content['meta_keywords']);
		$title_htmltag = plxUtils::getValue($content['title_htmltag']);
		# Vérifier si le fichier existe
		$filename = PLX_ROOT . $content['thumbnail'];
		if(!file_exists($filename) or exif_imagetype($filename) === false ) {
			$content['thumbnail'] = '';
			$content['thumbnail_alt'] = '';
		} else {
			$content['thumbnail_alt'] = trim($content['thumbnail_alt']);
			if(!empty($content['thumbnail_alt'])) {
				$content['thumbnail_alt'] = plxUtils::strCheck($content['thumbnail_alt']);
			} else {
				$content['thumbnail_alt'] = basename($content['thumbnail']);
			}
		}

		# vérification du template
		if(!preg_match('#^article[\w\.-]*\.php$#', $content['template'])) {
			$content['template'] ='article.php';
		}

		# Vérifie si l'auteur existe
		if(!preg_match('#^\d{3}$#', $content['author']) or !array_key_exists($content['author'], $this->aUsers)) {
			$content['author'] = '000';
		}

		# Génération du contenu du fichier XML
		ob_start();
?>
<document>
	<title><?= plxUtils::strCheck(trim($content['title'])) ?></title>
	<allow_com><?= ($content['allow_com'] === '1') ? '1' : '0' ?></allow_com>
	<template><?= $content['template'] ?></template>
	<chapo><![CDATA[<?= plxUtils::cdataCheck(trim($content['chapo']), true) ?>]]></chapo>
	<content><![CDATA[<?= plxUtils::cdataCheck(trim($content['content']), true) ?>]]></content>
	<tags><?= $content['tags'] ?></tags>
	<meta_description><?= plxUtils::strCheck($meta_description) ?></meta_description>
	<meta_keywords><?= plxUtils::strCheck($meta_keywords) ?></meta_keywords>
	<title_htmltag><?= plxUtils::strCheck($title_htmltag) ?></title_htmltag>
	<thumbnail><?= $content['thumbnail'] ?></thumbnail>
	<thumbnail_alt><?= $content['thumbnail_alt'] ?></thumbnail_alt>
	<thumbnail_title><?= plxUtils::strCheck($content['thumbnail_title']) ?></thumbnail_title>
	<date_creation><?= $date_creation ?></date_creation>
	<date_update><?= $date_update ?></date_update>
<?php
		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditArticleXml'));
?>
</document>
<?php
		# Recherche du nom du fichier correspondant à l'id
		$oldArt = $this->plxGlob_arts->query('/^'.$id.'\.(.*)\.xml$/','','sort',0,1,'all');

		# Si demande de modération de l'article
		if(isset($content['moderate']))
			$id = '_'.str_replace('_','',$id);
		# Si demande de publication
		if(isset($content['publish']) OR isset($content['draft']))
			$id = str_replace('_','',$id);

		# On genère le nom de notre fichier
		$time = $content['date_publication_year'].$content['date_publication_month'].$content['date_publication_day'].substr(str_replace(':','',$content['date_publication_time']),0,4);
		if(!preg_match('/^\d{12}$/',$time)) {
			$time = $now; # Check de la date au cas ou...
		}
		$content['catId'] = array_filter($content['catId'], function($value) {
			return preg_match('#^(?:\d{3}|home)$#', $value);
		});
		if(empty($content['catId'])) {
			$content['catId'] = array('000'); # article non classé
		}

		# On va mettre à jour notre fichier
		$filename = PLX_ROOT . $this->aConf['racine_articles'] . implode('.', array(
			$id,
			implode(',', $content['catId']),
			trim($content['author']),
			$time,
			$content['url'],
			'xml',
		));
		if(plxUtils::write(XML_HEADER . ob_get_clean(), $filename)) {
			# suppression ancien fichier si nécessaire
			if($oldArt) {
				$oldfilename = PLX_ROOT.$this->aConf['racine_articles'].$oldArt['0'];
				if($oldfilename!=$filename AND file_exists($oldfilename))
					unlink($oldfilename);
			}

			# mise à jour de la liste des tags
			$tags = trim($content['tags']);
			if(strlen($tags) > 0) {
				$this->aTags[$id] = array(
					'tags' => $tags,
					'date' => $time,
					'active' => in_array('draft', $content['catId']) ? 0 : 1,
				);
				$this->editTags();
			} elseif(isset($this->aTags[$id])) {
				unset($this->aTags[$id]);
				$this->editTags();
			}

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
	 * @author	Stephane F., Florent MONTHEL, J-Pierre Pourrez @bazooka07
	 **/
	public function delArticle($id) {

		# Vérification de l'intégrité de l'identifiant
		if(!preg_match('/^_?\d{4}$/',$id))
			return L_ERR_INVALID_ARTICLE_IDENT;

		# Récuperation de l'id de l'utilisateur ( voir index.php )
		$userId = ($_SESSION['profil'] <= PROFIL_MODERATOR) ? '\d{3}' : $_SESSION['user'];

		# Variable d'état
		$resDelArt = $resDelCom = true;
		# Suppression de l'article
		$cats = '[^\.]+';
		if($globArt = $this->plxGlob_arts->query('/^' . $id . '\.' . $cats . '\.' . $userId . '\.\d{12}\.(.*)\.xml$/')) {
			unlink(PLX_ROOT.$this->aConf['racine_articles'].$globArt['0']);
			$resDelArt = !file_exists(PLX_ROOT.$this->aConf['racine_articles'].$globArt['0']);
		} else {
			# l'article n'existe pas ou
			# le profil d'utilisateur n'est pas inférieur à PROFIL_EDITOR ou
			# l'article n'appartient pas à l'utilisateur.
			return plxMsg::Error(L_ARTICLE_DELETE_ERR . ' (id=' . $id . ')');
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
			return plxMsg::Info(L_ARTICLE_DELETE_SUCCESSFUL);
		}
		else
			return plxMsg::Error(L_ARTICLE_DELETE_ERR);
	}

	/**
	 * Méthode qui modifie l'état de brouillon d'un article
	 *
	 * @param	id	numero de l'article
	 * @param	$draft true or  false pour basculer en brouillon ou pas
	 * @return	string
	 * @author	J.P. Pourrez "bazooka07"
	 **/
	public function draftToggle($id, $draft=true) {
		# Vérification de l'intégrité de l'identifiant
		if(!preg_match('/^_?\d{4}$/', $id)) {
			return L_ERR_INVALID_ARTICLE_IDENT;
		}

		if($globArt = $this->plxGlob_arts->query('/^'.$id.'\..*\.xml$/')) {
			$tmp = $this->artInfoFromFilename($globArt[0]);

			if($draft xor (preg_match('#\bdraft\b#', $tmp['catId']) or $tmp['artId'][0] == '_')) {
				$tmp['catId'] = ($draft ? 'draft,' . $tmp['catId'] : preg_replace('#^draft,?#', '', $tmp['catId']));
				$tmp['artId'] = ltrim($tmp['artId'], '_');
				$newName = implode('.', $tmp) . '.xml';
				$artsFolder = PLX_ROOT . $this->aConf['racine_articles'];
				rename($artsFolder . $globArt['0'], $artsFolder . $newName);
				return L_ARTICLE_MODIFY_SUCCESSFUL;
			}

			return L_ARTICLE_SAVE_ERR;
		}

		return L_ERR_UNKNOWN_ARTICLE;
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

		if(!preg_match('#^\d{4}$#', $artId)) {
			return plxMsg::Error(L_ERR_UNKNOWN_ARTICLE);
		}
		# On génère le contenu du commentaire
		$idx = $this->nextIdArtComment($artId);
		$filename = $artId . '.' . time() . '-' . $idx . '.xml';

		$message = strip_tags(trim($content['content']), self::ENABLED_HTML_TAGS_COMMENTS);
		if(empty($message)) {
			return plxMsg::Error(L_NEWCOMMENT_FIELDS_REQUIRED);
		}

		$userId = $_SESSION['user'];
		$comment = array(
			'type'		=> 'admin',
			'author'	=> $this->aUsers[$userId]['name'],
			'content'	=> strip_tags(trim($content['content']), self::ENABLED_HTML_TAGS_COMMENTS),
			'parent'	=> filter_var($content['parent'], FILTER_VALIDATE_INT, array('options' => array('default' => ''))),
			'ip'		=> plxUtils::getIp(),
			'mail'		=> $this->aUsers[$userId]['email'],
			'site'		=> $this->racine,
			'filename'	=> $filename,
		);

		# On peut créer le commentaire
		return $this->addCommentaire($comment);
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
		# Génération du nom du fichier
		$fullpath = PLX_ROOT . $this->aConf['racine_commentaires'] . $id . '.xml';
		if(!file_exists($fullpath)) {
			# Commentaire inexistant
			return plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
		}
		# Vérification de la validité de la date de publication
		if(!plxDate::checkDate($content['date_publication_day'],$content['date_publication_month'],$content['date_publication_year'],$content['date_publication_time'])) {
			return plxMsg::Error(L_ERR_INVALID_PUBLISHING_DATE);
		}
		$time = explode(':', $content['date_publication_time']);
		$newtimestamp = mktime($time[0], $time[1], 0, $content['date_publication_month'], $content['date_publication_day'], $content['date_publication_year']);
		if($newtimestamp === false or $newtimestamp < 100000000 or $newtimestamp > 9999999999) {
			# 9 ou 10 chiffres seulement ( 1973-03-03T09:46:40 et 2286-11-20T17:46:39 respectivement)
			return plxMsg::Error(L_ERR_INVALID_PUBLISHING_DATE);
		}
		unset($time);

		# Contrôle des saisies
		$mail = trim($content['mail']);
		if(!empty($mail) and !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
			return plxMsg::Error(L_ERR_INVALID_EMAIL);
		}
		$site = trim($content['site']);
		if(!empty($site) AND !filter_var($site, FILTER_VALIDATE_URL)) {
			return plxMsg::Error(L_ERR_INVALID_SITE);
		}
		$author = trim($content['author']);
		$message = strip_tags(trim($content['content']), self::ENABLED_HTML_TAGS_COMMENTS);
		if(empty($author) or empty($message)) {
			return plxMsg::Error(L_NEWCOMMENT_FIELDS_REQUIRED);
		}

		# On récupère les infos du commentaire
		$com = $this->parseCommentaire($fullpath);

		# Génération du nouveau nom du fichier
		$comInfos = $this->comInfoFromFilename($id . '.xml');
		$newid = $comInfos['comStatus'] . $comInfos['artId'] . '.' . $newtimestamp . '-' . $comInfos['comIdx'];

		# Formatage des données
		$comment = array(
			'type'		=> $com['type'],
			'author'	=> plxUtils::strCheck($author),
			'content'	=> $message,
			'parent'	=> $com['parent'],
			'ip'		=> $com['ip'],
			'mail'		=> $mail,
			'site'		=> $site,
			'filename'	=> $newid . '.xml',
		);

		# Création du nouveau commentaire
		if($this->addCommentaire($comment)) {
			# Suppression de l'ancien commentaire
			unlink($fullpath);
			$id = $newid;
			return plxMsg::Info(L_COMMENT_SAVE_SUCCESSFUL);
		}

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
			return plxMsg::Info(L_COMMENT_DELETE_SUCCESSFUL);
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
		if(preg_match('/([[:punct:]]?)\d{4}\.\d{9,10}-\d+$/',$id,$capture)) {
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
		$href = PLX_URL_REPO;
		$this->update_link = sprintf('%s : <a href="%s">%s</a>', L_PLUXML_UPDATE_AVAILABLE, PLX_URL_REPO, PLX_URL_REPO);

		if(function_exists('curl_init')) {
			# test avec curl et le dépôt Github de PluXml.
			# Ne marche pas avec le site https://www.pluxml.org si protocole Http utilisé
			$title = 'curl';
			$ch = curl_init(PLX_URL_LAST_RELEASE_GITHUB);
			curl_setopt_array($ch, array(
				CURLOPT_HEADER => false,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => false,
				# CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13',
				CURLOPT_USERAGENT => 'Curl ' . curl_version()['version'],
			));
			$response = curl_exec($ch);
			$error = curl_errno($ch);
			// $status = curl_getinfo($ch);
			if (PHP_VERSION_ID >= 80000) {
				unset($ch);
			} else {
				curl_close($ch);
			}
			if($error === 0 and is_string($response)) {
				$datas = json_decode($response, true);
				if(!empty($datas)) {
					$latest_version = preg_replace('#\D*(\d+\.\d+(?:\.\d+)?).*#', '$1', $datas['tag_name']);
					$href = $datas['html_url'];
				}
			}
		} elseif(ini_get('allow_url_fopen')) {
			$title = 'file_get_content';
			$latest_version = @file_get_contents(PLX_URL_VERSION, false, null, 0, 16);
			if(function_exists('http_get_last_response_headers')) {
				# pour PHP >= 8.4 - Polyfill à utiliser à l'intérieur d'une fonction !
				$http_response_header = http_get_last_response_headers();
			}
			if(
				empty($http_response_header) OR
				!preg_match('@^HTTP/[\d\.]+ 200@', $http_response_header[0]) OR
				empty($latest_version)
				) {
					$latest_version = 'UPDATE_UNAVAILABLE';
				}
		}

		$this->update_link = sprintf('%s : <a href="%s" target="blank">%s</a>', preg_replace('#!\s*#', '!<br>', L_PLUXML_UPDATE_AVAILABLE), $href, $href);
		$className = '';
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

		return sprintf('<p id="latest-version" class="alert %s" title="%s">%s</p>', $className, $title, $msg);

	}

	/**
	 * Vérifie s'il y a besoin de demander un token pour utiliser le serveur SMTP (OAuth2)
	 *
	 * @author Jean-Pierre Pourrez @bazooka07
	 **/
	public function o_auth_token_required($data=null) {
		if(empty($data)) {
			$data = $this->aConf;
		}
		return (
			$data['email_method'] == 'smtpoauth' and
			!empty($data['smtpOauth2_emailAdress']) and
			empty(trim($data['smtpOauth2_refreshToken']))
		);
	}

}
