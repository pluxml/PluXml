<?php

/**
 * Classe plxAdmin responsable des modifications dans l'administration
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE, Jean-Pierre Pourrez "bazooka07"
 **/

const PLX_ADMIN = true;
const HTACCESS_FILE = PLX_ROOT . '.htaccess';
const PATTERN_NAME = '#^\w[\w\s.-]*\w$#u'; # for preg_match()

class plxAdmin extends plxMotor {

	const VERSION_PATTERN = '#^\d{1,2}\.\d{1,3}(?:\.\d{1,3})?$#';
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
		$this->tri = 'desc';
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
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function getPage() {

		# Initialisation
		$pageName = basename($_SERVER['PHP_SELF'], '.php');
		$savePage = preg_match('#admin/(?:index|comments)\.php$#', $_SERVER['PHP_SELF']);
		# On teste pour avoir le numero de page
		if(!empty($_GET['page']) AND is_numeric($_GET['page']) AND $_GET['page'] > 0) {
			$this->page = $_GET['page'];
		}
		elseif($savePage) {
			if(!empty($_POST['sel_cat'])) {
				$this->page = 1;
			} else {
				$this->page = !empty($_SESSION['page'][$pageName]) ? intval($_SESSION['page'][$pageName]) : 1;
			}
		}
		# On sauvegarde
		if($savePage) {
			$_SESSION['page'][$pageName] = $this->page;
		}
	}

	/**
	 * Méthode qui édite le fichier XML de configuration selon le tableau $plxConfig et $content
	 *
	 * @param	plxConfig	tableau contenant toute la configuration PluXml
	 * @param	content	tableau contenant les champs de la configuration à modifier
	 * @return	string
	 * @author	Florent MONTHEL, J.P. Pourrez "bazooka07"
	 **/
	public function editConfiguration($plxConfig, $content) {

		# Sauvegarde de la valeur initiale
		$urlrewriting = $plxConfig['urlrewriting'];

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditConfiguration'));

		# Ne pas sauvegarder ces champs dans parametres.xml
		$excludes = array('racine', 'token','config_path',);

		# On recense les champs avec des valeurs libres saisies par l'utilisateur. A encadrer par <![CDATA[..]]>
		$cdata = array(
			'title',
			'description',
			'meta_description',
			'meta_keywords',
			'feed_footer',
			'custom_admincss_file',
			'smtp_username',
			'smtp_password',
			'smtpOauth2_clientId',
			'smtpOauth2_clientSecret',
		);

		$parametreChanged = false;
		foreach($content as $k=>$v) {
			if(
				in_array($k, $excludes) or
				(isset($plxConfig[$k]) and $plxConfig[$k] === $v)
			) {
				# Pas de sauvegarde pour ce paramètre
				# ou Aucun changement pour ce champ
				continue;
			}

			if(preg_match('#^(?:medias|racine_(?:article|comment\w*|statique|theme|plugin)s|)$#', $k)) {
				# contrôle de la validité des dossiers racines et medias
				if(!preg_match('#^\w[\w\s/\\-]*/?$#', $v) or !is_dir(PLX_ROOT . $v)) { # Pas de . dans le nom des dossiers !
					continue;
				} else {
					# Chemin validé pour un dossier
					if (substr($v, -1) !== '/') {
						$v .= '/';
					}
				}

			} elseif($k == 'custom_admincss_file') {
				# fichier personnel CSS pour le back-office
				if(!preg_match('#\.css$#', $v) or !file_exists(PLX_ROOT . $v)) {
					# valeur invalide
					continue;
				}

			} elseif(preg_match('#^(?:bypage|images|miniatures|usersFolders)#', $k)) {
				/*
				 * Uniquement une valeur numérique pour ces champs :
				 * bypage,
				 * bypage_admin,
				 * bypage_admin_coms,
				 * bypage_archives,
				 * bypage_feed,
				 * bypage_tags,
				 * images_l,
				 * images_h,
				 * miniatures_l,
				 * miniatures_h,
				 * usersFolders
				 * */
				if(!is_numeric($v)) {
					continue;
				} else {
					$v = intval($v);
				}

			} elseif(preg_match('#^(?:allow|capcha|display|enable|feed_chapo|gzip|lostpassword|mod_|thumbs|urlrewriting|userfolder)#', $k)) {
				/*
				 * Uniquement une valeur booléenne 0 ou 1 pour ces champs :
				 * allow_com
				 * capcha
				 * display_empty_cat
				 * enable_rss
				 * enable_rss_comment
				 * feed_chapo
				 * gzip
				 * lostpassword
				 * mod_art
				 * mod_com
				 * thumbs
				 * urlrewriting
				 * userfolders
				 * */
				if(!preg_match('#^\s*(\d)\s*$#', $v, $matches)) {
					continue;
				} else {
					$v = intval($matches[1]);
				}

			} elseif(preg_match('#^tri#', $k)) {
				# Champs pour les tris
				if(!preg_match('#^\s*(r?alpha|asc|desc|random)\s*$#', $v, $matches)) {
					continue;
				} else {
					$v = $matches[1];
				}

			} elseif($k == 'style') {
				# contrôle le dossier du thème
				if(
					!preg_match('#^\w[\w\s-]*$#', $v) or
					!is_dir(PLX_ROOT . $this->aConf['racine_themes'] . $v)
				) {
					continue;
				}

			} elseif($k == 'homestatic') {
				if(
					!empty($v) and
					!array_key_exists($v, $this->aStats)
				) {
					continue;
				}

			} elseif($k == 'hometemplate') {
				# Un thème doit avoir au moins un fichier home*.php. Voir plxThemes::getThemes()
				if(
					!preg_match('#^home[\w\s-]*\.php$#', $v) or
					!file_exists(PLX_ROOT . $this->aConf['racine_themes'] . $this->aConf['style'] . '/' . $v)
				) {
					continue;
				}

			} elseif($k == 'default_lang') {
				if(!array_key_exists($v, plxUtils::getLangs())) {
					continue;
				}

			} elseif($k == 'email_method') {
				if(!array_key_exists($v, EMAIL_METHODS)) {
					continue;
				}

			} elseif($k == 'timezone') {
				if(!array_key_exists($v, plxTimezones::timezones())) {
					continue;
				}
			}

			if(!isset($plxConfig[$k]) or $plxConfig[$k] != $v) {
				# Valeur à sauvegarder
				$plxConfig[$k] = $v;
				$parametreChanged = true;
			}
		} # End for "foreach($content as $k=>$v)"

		# On teste la clef
		if(empty($plxConfig['clef']) or !preg_match('#^\w{15}$#', $plxConfig['clef'])) {
			$plxConfig['clef'] = plxUtils::charAleatoire(15);
			$parametreChanged = true;
		}

		# On enregistre les modifications
		if($parametreChanged) {
			# On force la valeur par sécurité
			# $plxConfig['version'] = PLX_VERSION;
			$plxConfig['version'] = PLX_VERSION_DATA;

			# On réinitialise la pagination au cas où modif de bypage_admin
			unset($_SESSION['page']);

			# On réactualise la langue
			$_SESSION['lang'] = $plxConfig['default_lang'];

			# Actions sur le fichier .htaccess si le mode de ré-écriture a changé
			if(
				array_key_exists('urlrewriting', $content) and
				$plxConfig['urlrewriting'] != $urlrewriting
			) {
				if(!$this->htaccess($plxConfig['urlrewriting'], $plxConfig['racine'])) {
					return plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, '.htaccess'));
				}
			}

			# Début du fichier XML
			ob_start();
?>
<document>
<?php
			foreach($plxConfig as $k=>$v) {
				if(in_array($k, $excludes)) {
					continue;
				}

				if (empty($v) or !in_array($k, $cdata)) {
					# <!CDATA[..]]> est inutile :  valeur numerique, champs uniquement avec caractères alphanumérique
					$content = plxUtils::strCheck($v);
				} elseif(in_array($k, array('description', 'feed_footer'))) {
					# On tolère quelques balises par défaut : <i>, <em>, <a>, <sup>, <span>,
					$content = plxUtils::strCheck($v, true);
				} else {
					# Aucune balise HTML tolérée
					$content = plxUtils::strCheck($v, true, null);
				}
?>
	<parametre name="<?= $k ?>"><?= $content ?></parametre>
<?php
			}
?>
</document>
<?php
			# Mise à jour du fichier parametres.xml
			if(!plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_PARAMETERS')))
				return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_PARAMETERS'));
		}

		# Si nouvel emplacement du dossier de configuration
		if(isset($content['config_path'])) {
			$newpath=trim($content['config_path']);
			if(substr($newpath, -1) !== '/') {
				$newpath .= '/';
			}
			if($newpath != PLX_CONFIG_PATH) {
				# relocalisation du dossier de configuration de PluXml
				if(!preg_match('#^\w[\w\s/\\-]*/$#', $newpath) or !rename(PLX_ROOT.PLX_CONFIG_PATH,PLX_ROOT.$newpath)) {
					return plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, $newpath));
				}

				# mise à jour du fichier de configuration config.php
				$buffer = <<< EOT
const PLX_CONFIG_PATH = '$newpath';
EOT;
				if(!plxUtils::write('<?php' . PHP_EOL . $buffer . PHP_EOL . PHP_EOL, PLX_ROOT . 'config.php')) {
					return plxMsg::Error(L_SAVE_ERR.' config.php');
				}
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

		if(!defined('HTACCESS_FILE')) {
			return;
		}

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

		$htaccess = is_file(HTACCESS_FILE) ? file_get_contents(HTACCESS_FILE) : '';

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
		if($htaccess=='' AND is_file(HTACCESS_FILE)) {
			return unlink(HTACCESS_FILE);
		} else {
			return plxUtils::write($htaccess, HTACCESS_FILE);
		}

	}

	/**
	 * Méthode qui crée le fichier robots.txt à la racine du site
	 *
	 * @return	void
	 * @author	Jean-Pierre Pourrez "bzooka07"
	 **/
	public function EditRobots() {
		# https://developers.google.com/search/docs/crawling-indexing/robots/robots_txt
		$block_begin = '# BEGIN -- Pluxml';
		$block_end = '# END -- Pluxml';
		$path = preg_replace('@\bcore/(?:admin|lib)$@', '', dirname($_SERVER['SCRIPT_NAME']));
		$today = date('Y-m-d H:i');
		$plxContents = <<< EOT
$block_begin
# $today

User-agent: *
Disallow: {$path}config.php$
Disallow: {$path}install.php$
Disallow: {$path}sitemap.php$
Disallow: {$path}update$
Disallow: {$path}core$
Disallow: {$path}readme$
Disallow: {$path}{$this->aConf['racine_plugins']}$
Disallow: {$path}{$this->aConf['racine_articles']}$
Disallow: {$path}{$this->aConf['racine_commentaires']}$
Disallow: {$path}{$this->aConf['racine_statiques']}$
Disallow: {$path}{$this->aConf['racine_themes']}*.php
Disallow: {$path}{$this->aConf['medias']}download$
Allow: {$path}{$this->aConf['medias']}

Sitemap: {$this->racine}sitemap.php

$block_end

EOT;

		$filename = $_SERVER['DOCUMENT_ROOT'] . '/robots.txt';
		if(file_exists($filename)) {
			$contents = file_get_contents($filename);
			$pattern = '@^(.*?)(?:^' . $block_begin . '.*' . $block_end . ')(.*)$@ims';
			if(preg_match($pattern, $contents, $matches)) {
				$contents = $matches[1] . $plxContents . PHP_EOL . trim($matches[2]);
			} else {
				$contents .= PHP_EOL . $plxContents;
			}
			$success = plxUtils::write($contents, $filename);
		} else {
			$success = plxUtils::write($plxContents, $filename);
		}

		if($success) {
			plxMsg::Info(L_SAVE_FILE_SUCCESSFULLY);
		} else {
			plxMsg::Error(sprintf(L_WRITE_NOT_ACCESS, $filename));
		}
	}

	/**
	 * Méthode qui controle l'accès à une page en fonction du profil de l'utilisateur connecté
	 *
	 * @param	profil		profil(s) autorisé(s) type integer ou array
	 * @param	redirect	si VRAI redirige sur la page index.php en cas de mauvais profil(s)
	 * @return	boolean ou exit
	 * @author	Stephane F, Jean-Pierre Pourrez @bazooka07
	 **/
	public function checkProfil($profil, $redirect=true) {
		$success = is_array($profil) ? in_array($_SESSION['profil'], $profil) : ($_SESSION['profil'] == intval($profil));
		if(!$redirect) {
			return $success;
		}

		if(!$success) {
			plxMsg::Error(L_NO_ENTRY);
			header('Location: index.php');
			exit;
		}

		return true;
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

			if(!array_key_exists($content['lang'], plxUtils::getLangs())) {
				return plxMsg::Error(L_UNKNOWN_ERROR);
			}

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

		if(trim($content['password1'])=='' OR trim($content['password1'])!=trim($content['password2'])) {
			return plxMsg::Error(L_ERR_PASSWORD_EMPTY_CONFIRMATION);
		}

		$action = false;
		$token = '';
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
		} elseif(empty($_SESSION['user']) or !array_key_exists($_SESSION['user'], $this->aUsers)) {
			return plxMsg::Error(L_UNKNOWN_ERROR);
		} else {
			$salt = $this->aUsers[$_SESSION['user']]['salt'];
			$this->aUsers[$_SESSION['user']]['password'] = sha1($salt.md5($content['password1']));
			$action = true;
		}

		return $this->editUsers(null, $action);

	}

	/**
	* Create a token and send a link by e-mail using "email-lostpassword.xml" template
	*
	* @param loginOrMail user login or e-mail address
	* @return string token to password reset
	* @throws \PHPMailer\PHPMailer\Exception
	* @author Pedro "P3ter" CADETE, J.P. Pourrez aka bazooka07
	**/
	public function sendLostPasswordEmail($loginOrMail) {

		if (!empty($loginOrMail) and plxUtils::testMail(false)) {
			foreach($this->aUsers as $user_id => $user) {
				if(!$user['active'] or $user['delete'] or empty($user['email'])) { continue; }

				if($user['login'] == $loginOrMail OR $user['email'] == $loginOrMail) {
					// token and e-mail creation
					$mail = array();
					$tokenExpiry = 24;
					$lostPasswordToken = plxToken::getTokenPostMethod(32, false);
					$lostPasswordTokenExpiry = plxToken::generateTokenExperyDate($tokenExpiry);
					$templateName = 'email-lostpassword-'.PLX_SITE_LANG.'.xml';
					if(!array_key_exists($templateName, $this->aTemplates)) {
						break;
					}

					$placeholdersValues = array(
						"##LOGIN##"			=> $user['login'],
						"##URL_PASSWORD##"	=> $this->aConf['racine'].'core/admin/auth.php?action=changepassword&token='.$lostPasswordToken,
						"##URL_EXPIRY##"	=> $tokenExpiry
					);
					if (($mail ['body'] = $this->aTemplates[$templateName]->getTemplateGeneratedContent($placeholdersValues)) != '1') {
						$mail['subject'] = $this->aTemplates[$templateName]->getTemplateEmailSubject();

                        if($this->isPHPMailerDisabled()) {
							# PHP native mail() function
							$success = plxUtils::sendMail('', '', $user['email'], $mail['subject'], $mail['body']);
                        } else {
							# PHPMailer library
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
							return true;
						}
					}
					break;
				}
			}
		}

		# Echec unknown user or fails for sending mail
		return false;
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

	public function log_connexion($user_id) {
		foreach(array('password_token', 'password_token_expiry',) as $k) {
			if(!empty($this->aUsers[$user_id][$k])) {
				$this->aUsers[$user_id][$k] = '';
			}
		}

		$this->aUsers[$user_id]['last_connexion'] = $this->aUsers[$user_id]['connected_on'];
		$this->aUsers[$user_id]['connected_on'] = date('YmdHi');

		if($this->aUsers[$user_id]['profil'] === '0') {
			# Si administrateur conecté, on contrôle l'effacement des données personnelles des utilisateurs supprimés
			$plxAdmin->_deletedUsersControl();
		}

		return $this->editUsers(null, true);
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

		if(isset($content['update'])) {
			# mise à jour de la liste des utilisateurs
			foreach($content['users'] as $user_id=>$user_infos) {
				$username = trim($user_infos['name']);
				$login = trim($user_infos['login']);

				if(empty($username) or empty($login)) {
					continue;
				}

				# contrôle validité name et login
				foreach(array('name', 'login') as $f) {
					$value = $user_infos[$f];
					if(!preg_match(PATTERN_NAME, $value)) {
						return plxMsg::Error(L_INVALID_VALUE . ' : <em>' . $value .  '</em>');
					}
				}

				$new_user = !array_key_exists($user_id, $this->aUsers);
				# controle du mot de passe
				if(trim($user_infos['password']) != '') {
					# Nouveau mot de passe
					$salt = plxUtils::charAleatoire(10);
					$password = sha1($salt . md5($content[$user_id.'_password']));
				} elseif($new_user) {
					# Obligatoire pour un nouvel utilisateur
					$this->aUsers = $save;
					return plxMsg::Error(L_ERR_PASSWORD_EMPTY . ' (' . L_CONFIG_USER . ' <em>' . $username . '</em>)');
				}
				else {
					# On récupère l'ancien mot de passe
					$salt = $this->aUsers[$user_id]['salt'];
					$password = $this->aUsers[$user_id]['password'];
				}

				# controle de l'adresse email
				$email = trim($user_infos['email']);
				if($new_user AND empty($email))
					return plxMsg::Error(L_ERR_INVALID_EMAIL);
				if(!empty($email) AND !plxUtils::checkMail($email))
					return plxMsg::Error(L_ERR_INVALID_EMAIL);

				$this->aUsers[$user_id]['login'] = $login;
				$this->aUsers[$user_id]['name'] = $username;
				if($user_id == '001') {
					$this->aUsers[$user_id]['active'] = 1;
					$this->aUsers[$user_id]['profil'] = PROFIL_ADMIN;
				} else {
					$this->aUsers[$user_id]['active'] = ($_SESSION['user'] == $user_id) ? $this->aUsers[$user_id]['active'] : $user_infos['active'];
					$this->aUsers[$user_id]['profil'] = ($_SESSION['user'] == $user_id) ? $this->aUsers[$user_id]['profil'] : $user_infos['profil'];
				}
				$this->aUsers[$user_id]['password'] = $password;
				$this->aUsers[$user_id]['salt'] = $salt;
				$this->aUsers[$user_id]['email'] = $email;
				$default_values = array(
					'delete'				=> 0,
					'lang'					=> $this->aConf['default_lang'],
					'infos'					=> '',
					'password_token'		=> '',
					'password_token_expiry' => '',
				);
				foreach($default_values as $k=>$default) {
					if(!isset($this->aUsers[$user_id][$k])) {
						$this->aUsers[$user_id][$k] = $default;
					}
				}

				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditUsersUpdate'));
				$action = true;
			}
		} elseif(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idUser'])) {
			# suppression
			foreach($content['idUser'] as $user_id) {
				if($user_id == '001') {
					continue;
				}

				$this->aUsers[$user_id]['delete'] = 1;
				# On supprime les données personelles sensibles
				foreach(self::VALUES_USER as $field) {
					if(in_array($field, array('login' , 'name', 'last_connexion'))) {
						continue;
					}
					$this->aUsers[$user_id][$field] = '';
				}
				$action = true;
			}
		}

		# sauvegarde
		if($action) {
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
				else if ($user['delete'] == 0) {
					$users_name[] = $user['name'];
				}
				# controle de l'unicité du login de l'utilisateur
				if(in_array($user['login'], $users_login)) {
					ob_end_clean();
					return plxMsg::Error(L_ERR_LOGIN_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['login']));
				}
				else if ($user['delete'] == 0) {
					$users_login[] = $user['login'];
				}
				# controle de l'unicité de l'adresse e-mail
				if(in_array($user['email'], $users_email)) {
					ob_end_clean();
					return plxMsg::Error(L_ERR_EMAIL_ALREADY_EXISTS.' : '.plxUtils::strCheck($user['email']));
				}
				else if ($user['delete'] == 0) {
					$users_email[] = $user['email'];
				}
?>
	<user number="<?= $user_id ?>" active="<?= $user['active'] ?>" profil="<?= $user['profil'] ?>" delete="<?= $user['delete'] ?>">
<?php
		foreach(self::VALUES_USER as $k) {
?>
		<<?= $k ?>><?= in_array($k, array('login', 'name', 'infos',)) ? plxUtils::strCheck($user[$k]) : $user[$k] ?></<?= $k ?>>
<?php
		}
?>
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
			} else {
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
		if(trim($content['email'])!='' AND !plxUtils::checkMail(trim($content['email']))) {
			return plxMsg::Error(L_ERR_INVALID_EMAIL);
		}

		# controle de la langue sélectionnée
		if(!array_key_exists($content['lang'], plxUtils::getLangs())) {
			return plxMsg::Error(L_UNKNOWN_ERROR);
		}

		$this->aUsers[$content['id']]['email'] = $content['email'];
		$this->aUsers[$content['id']]['infos'] = trim($content['content']);
		$this->aUsers[$content['id']]['lang'] = $content['lang'];

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditUser'));

		return $this->editUsers(null,true);
	}

	/**
	 * Checks if no personal datas for deleted users ( RGPD )
	 *
	 * @author Jean-Pierre Pourrez @bazooka07
	 **/
	 private function _deletedUsersControl() {
		foreach($this->aUsers as $user_id=>$user_infos) {
			if(empty($user_infos['delete']) or $user_id == '001') {
				continue;
			}

			# On contrôle la suppression des données personelles sensibles
			foreach(self::VALUES_USER as $field) {
				if(in_array($field, array('login' , 'name', 'last_connexion'))) {
					continue;
				}

				if(!empty($this->aUsers[$user_id][$field])) {
					$this->aUsers[$user_id][$field] = '';
				}
			}
		}
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
	 * @param	action	permet de forcer la mise àjour du fichier
	 * @return	string
	 * @author	Stephane F, Pedro "P3ter" CADETE, sudwebdesign
	 **/
	public function editCategories($content, $action=false) {

		$save = $this->aCats;

		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete') {
			$idCategory = $content['idCategory'];
			if(empty($idCategory)) {
				# Aucune catégorie sélectionnée
				return;
			}

			$pattern = '#^_?\d{4}\.(?:pin,|home,|\d{3},)*' . implode(',', $idCategory) . '(?:,\d{3})*\.#';
			$aArts = $this->plxGlob_arts->query($pattern, 'art');
			if(!empty($aArts)) {
				$root = PLX_ROOT.$this->aConf['racine_articles'];
				foreach($aArts as $filename) {
					$filenameNew = preg_replace_callback(
						'#^(_?\d{4}\.)((?:pin,|home,)*\d{3}(?:,\{3})*)(\..*)#',
						function($matches) use($idCategory) {
							$catIds = array_filter(
								explode(',', $matches[2]),
								function($value) use($idCategory) {
									return !in_array($value, $idCategory);
								}
							);

							if(empty($catIds) or $catIds == array('pin')) {
								$catIds[] = '000';
							}

							return $matches[1] . implode(',', $catIds) . $matches[3];
						},
						$filename
					);

					rename($root . $filename, $root . $filenameNew);
				}
			}

			foreach($content['idCategory'] as $cat_id) {
				unset($this->aCats[$cat_id]);
			}
			$action = true;
		}
		# Ajout d'une nouvelle catégorie à partir de la page article
		elseif(!empty($content['new_category'])) {
			# Test pour autoriser uniquement les caractères alphanumériques
			$cat_name = $content['new_catname'];
			if(!preg_match(PATTERN_NAME, $cat_name)) {
				return plxMsg::Error(L_INVALID_VALUE . ' : ' . $cat_name);
			}

			$cat_id = $this->nextIdCategory();
			$this->aCats[$cat_id] = array(
				'name'				=> $cat_name,
				'url'				=> plxUtils::urlify($cat_name),
				'template'			=> 'categorie.php',
				'tri'				=> $this->aConf['tri'],
				'bypage'			=> $this->aConf['bypage'],
				'menu'				=> 'oui',
				'active'			=> 1,
			);

			# Hook plugins
			eval($this->plxPlugins->callHook('plxAdminEditCategoriesNew'));
			$action = true;
		}
		# mise à jour de la liste des catégories
		elseif(!empty($content['update'])) {
			# pas de nouvelle catégorie
			$i = count($content['catNum']) - 1;
			$lastId = $content['catNum'][$i];
			if(empty(trim($content[$lastId . '_name']))) {
				unset($content['catNum'][$i]);
			}

			foreach($content['catNum'] as $cat_id) {
				# Test pour autoriser uniquement les caractères alphanumériques
				$cat_name = $content[$cat_id.'_name'];
				if(!preg_match(PATTERN_NAME, $cat_name)) {
					return plxMsg::Error(L_INVALID_VALUE . ' : ' . $cat_name);
				}

				$tmpstr = (!empty($content[$cat_id.'_url'])) ? $content[$cat_id.'_url'] : $cat_name;
				$cat_url = plxUtils::urlify($tmpstr);
				if(empty($cat_url)) {
					$cat_url = L_DEFAULT_NEW_CATEGORY_URL;
				}

				# valeurs fournies par $content[]
				$this->aCats[$cat_id]['name'] = $cat_name;
				$this->aCats[$cat_id]['url'] = $cat_url;
				$this->aCats[$cat_id]['template'] = $content[$cat_id.'_template'];
				$this->aCats[$cat_id]['active'] = $content[$cat_id.'_active'];
				$this->aCats[$cat_id]['tri'] = $content[$cat_id.'_tri'];
				$this->aCats[$cat_id]['bypage'] = intval($content[$cat_id.'_bypage']);
				$this->aCats[$cat_id]['ordre'] = intval($content[$cat_id.'_ordre']);
				$this->aCats[$cat_id]['menu'] = $content[$cat_id.'_menu'];

				# Hook plugins
				eval($this->plxPlugins->callHook('plxAdminEditCategoriesUpdate'));

				$action = true;
			}

			# On va trier les clés selon l'ordre choisi
			if(sizeof($this->aCats) > 1) uasort($this->aCats, function($a, $b) { return $a['ordre'] - $b['ordre']; } );
		}

		# sauvegarde
		if($action) {
			# controles !
			$uniq_cats = array(
				'name' => array(),
				'url' => array(),
			);
			foreach($this->aCats as $cat_id => $cat) {
				$value = $cat['name'];
				if(!preg_match(PATTERN_NAME, $value)) {
					return plxMsg::Error(L_INVALID_VALUE . ' : ' . $value);
				}

				# controle de l'unicité du nom et de l'url de la categorie
				foreach($uniq_cats as $f => $values) {
					if(in_array($cat[$f], $values)) {
						$this->aCats = $save;
						return plxMsg::Error(L_ERR_CATEGORY_ALREADY_EXISTS.' : '.plxUtils::strCheck($cat['name']));
					} else {
						$uniq_cats[$f][] = $cat[$f];
					}
				}
			}
			unset($uniq_cats);

			# On génére le fichier XML
			ob_start();
?>
<document>
<?php
			$extraFields = array(
				# 'homepage'],
				'description',
				'thumbnail',
				'thumbnail_title',
				'thumbnail_alt',
				'title_htmltag',
				'meta_description',
				'meta_keywords',
			);
			foreach($this->aCats as $cat_id => $cat) {
				foreach($extraFields as $field) {
					if(!isset($cat[$field])) {
						$cat[$field] = '';
					}
				}
?>
	<categorie number="<?= $cat_id ?>" active="<?= $cat['active'] ?>" homepage="<?= isset($cat['homepage']) ? $cat['homepage'] : '1' ?>" tri="<?= $cat['tri'] ?>" bypage="<?= $cat['bypage'] ?>" menu="<?= $cat['menu'] ?>" url="<?= $cat['url'] ?>" template="<?= basename($cat['template']) ?>">
		<name><?= $cat['name'] ?></name>
		<description><?= plxUtils::strCheck($cat['description'], true) ?></description>
		<meta_description><?= plxUtils::strCheck($cat['meta_description'], true, null) ?></meta_description>
		<meta_keywords><?= plxUtils::strCheck($cat['meta_keywords'], true, null) ?></meta_keywords>
		<title_htmltag><?= plxUtils::strCheck($cat['title_htmltag'], true, null) ?></title_htmltag>
		<thumbnail><?= plxUtils::strCheck($cat['thumbnail'], true, null) ?></thumbnail>
		<thumbnail_alt><?= plxUtils::strCheck($cat['thumbnail_alt'], true) ?></thumbnail_alt>
		<thumbnail_title><?= plxUtils::strCheck($cat['thumbnail_title'], true) ?></thumbnail_title>
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
			if(plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_CATEGORIES'))) {
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			} else {
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
		return $this->editCategories(null, true);
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
		if(!empty($content['selection']) AND $content['selection']=='delete') {
			if(empty($content['idStatic'])) {
				# Aucune page statique à supprimer
				return;
			}

			foreach($content['idStatic'] as $static_id) {
				$filename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$this->aStats[$static_id]['url'].'.php';
				if(is_file($filename)) {
					unlink($filename);
				}

				# si la page statique supprimée est la page d'accueil, on met à jour le paramétrage
				if($static_id == $this->aConf['homestatic']) {
					$this->aConf['homestatic'] = '';
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
				# La page statique doit avoir un titre
				if($stat_name != '') {
					$url = (!empty($content[$static_id.'_url'])) ? plxUtils::urlify($content[$static_id.'_url']) : '';
					$stat_url = (!empty($url)) ? $url : plxUtils::urlify($stat_name);
					if($stat_url=='') $stat_url = L_DEFAULT_NEW_STATIC_URL;

					# On vérifie si on a besoin de renommer le fichier de la page statique
					if(isset($this->aStats[$static_id]) AND $this->aStats[$static_id]['url']!=$stat_url) {
						$oldfilename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$this->aStats[$static_id]['url'].'.php';
						$newfilename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$stat_url.'.php';
						if(is_file($oldfilename)) rename($oldfilename, $newfilename);
					}

					# valeurs fournies par $content
					$this->aStats[$static_id]['group'] = trim($content[$static_id . '_group']);
					$this->aStats[$static_id]['name'] = $stat_name;
					$this->aStats[$static_id]['url'] = plxUtils::checkSite($url) ? $url : $stat_url;
					$this->aStats[$static_id]['template'] = $content[$static_id . '_template'];;
					$this->aStats[$static_id]['active'] = $content[$static_id . '_active'];
					$this->aStats[$static_id]['ordre'] = intval($content[$static_id . '_ordre']);
					$this->aStats[$static_id]['menu'] = $content[$static_id . '_menu'];

					# Hook plugins
					eval($this->plxPlugins->callHook('plxAdminEditStatiquesUpdate'));
					$action = true;
				}
			}
			# On va trier les clés selon l'ordre choisi
			if(sizeof($this->aStats) > 1) {
				uasort($this->aStats, function($a, $b) { return intval($a['ordre']) - intval($b['ordre']); } );
			}
		}
		# sauvegarde
		if($action) {
			# On contrôle l'unicité des titres et des urls des pages
			$statics_name = array();
			$statics_url = array();
			foreach($this->aStats as $static_id => $static) {
				if(
					in_array($static['name'], $statics_name) or
					in_array($static['url'], $statics_url)
				) {
					$this->aStats = $save;
					return plxMsg::Error(L_ERR_STATIC_ALREADY_EXISTS.' : '.plxUtils::strCheck($static['name']));
				} else {
					$statics_name[] = $static['name'];
					$statics_url[] = $static['url'];
				}
			}

			# On génére le fichier XML
			ob_start();
?>
<document>
<?php
			$today = date('YmdHi');
			foreach($this->aStats as $static_id => $static) {
				# Lors de la création d'une page statique dans le tableau statiques.php,
				# les valeurs 'title_htmltag', 'meta_description', 'meta_keywords' de ne sont pas renseignées.
?>
	<statique number="<?= $static_id ?>" active="<?= $static['active'] ?>" menu="<?= $static['menu'] ?>" url="<?= $static['url'] ?>" template="<?= basename($static['template']) ?>">
		<group><?= plxUtils::strCheck($static['group']) ?></group>
		<name><?= plxUtils::strCheck($static['name']) ?></name>
		<meta_description><?= !empty($static['meta_description']) ? plxUtils::strCheck($static['meta_description'], true) : '' ?></meta_description>
		<meta_keywords><?= !empty($static['meta_keywords']) ? plxUtils::strCheck($static['meta_keywords']) : '' ?></meta_keywords>
		<title_htmltag><?= !empty($static['title_htmltag']) ? plxUtils::strCheck($static['title_htmltag']) : '' ?></title_htmltag>
		<date_creation><?= !empty($static['date_creation']) ? $static['date_creation'] : $today ?></date_creation>
		<date_update><?= !empty($static['date_update']) ? $static['date_update'] : $today ?></date_update>
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
			} else {
				$this->aStats = $save;
				return plxMsg::Error(L_SAVE_ERR.' '.path('XMLFILE_STATICS'));
			}
		}
	}

	/**
	 * Méthode qui lit le fichier d'une page statique
	 *
	 * @param	num		numero du fichier de la page statique
	 * @return	string	contenu de la page ou chaine vide
	 * @author	Stephane F., J.P. Pourrez @bazooka07
	 **/
	public function getFileStatique($num) {

		# Emplacement de la page
		if(array_key_exists($num, $this->aStats)) {
			$filename = PLX_ROOT . $this->aConf['racine_statiques'] . $num . '.' . $this->aStats[ $num ]['url'] . '.php';
			if(file_exists($filename) AND filesize($filename) > 0) {
				$content = file_get_contents($filename);
				if(is_string($content)) {
					# On retourne le contenu
					return $content;
				} else {
					return implode(PHP_EOL, array('<p>', "\t" . L_UNKNOWN_ERROR, '</p>'));
				}
			}
		}

		return implode(PHP_EOL, array('<p>', "\t" . L_STATICS_NEW_PAGE, '</p>'));
	}

	/**
	 * Méthode qui retourne la liste des templates avec le même préfixe pour le thème courant
	 *
	 * @param	string $prefix préfixe du nom des templates : article, static, categorie, home, tag, archive, ...
	 * @return	array	liste des templates
	 * @author	Jean-Pierre Pourrez @bazooka07
	 **/
	public function getTemplatesTheme($prefix='static') {
		$glob = plxGlob::getInstance(PLX_ROOT . $this->aConf['racine_themes'] . $this->aConf['style'], false, true, '#^' . $prefix . '(?:-[\w-]+)?\.php$#');
		if (empty($glob->aFiles)) {
			return array('' => L_NONE1);
		}

		$aTemplates = array();
		foreach($glob->aFiles as $v) {
			$aTemplates[$v] = basename($v, '.php');
		}
		uasort($aTemplates, function($a0, $b0) {
			$mask = '#-full-width$#i';
			$a1 = preg_replace($mask, '', $a0);
			$b1 = preg_replace($mask, '', $b0);
			if($a1 != $b1) {
				return strcmp($a1, $b1);
			}

			return strlen($a0) - strlen($b0);
		});
		return $aTemplates;
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
		$id = $content['id'];
		if (!preg_match('#^\d{3}$#', $id) or !file_exists(PLX_ROOT.$this->aConf['racine_themes'] . $this->aConf['style'] . '/' . basename($content['template']))) {
			return plxMsg::Error(L_UNKNOWN_ERROR);
		}

		$this->aStats[$id]['template'] = basename($content['template']);
		$this->aStats[$id]['title_htmltag'] = trim($content['title_htmltag']);
		$this->aStats[$id]['meta_description'] = trim($content['meta_description']);
		$this->aStats[$id]['meta_keywords'] = trim($content['meta_keywords']);
		$this->aStats[$id]['date_creation'] = trim($content['date_creation_year']).trim($content['date_creation_month']).trim($content['date_creation_day']).substr(str_replace(':','',trim($content['date_creation_time'])),0,4);
		$date_update = $content['date_update'];
		$date_update_user = trim($content['date_update_year']).trim($content['date_update_month']).trim($content['date_update_day']).substr(str_replace(':','',trim($content['date_update_time'])),0,4);
		$date_pattern = '#^\d{12}$#';
		$date_update = (
			preg_match($date_pattern, $date_update) and
			preg_match($date_pattern, $date_update_user) and
			$date_update != $date_update_user
		) ? $date_update_user : date('YmdHi');
		$this->aStats[$id]['date_update'] = $date_update;

		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditStatique'));

		if($this->editStatiques(null,true)) {
			# Génération du nom du fichier de la page statique
			$filename = PLX_ROOT . $this->aConf['racine_statiques'] . $id . '.' . $this->aStats[ $id ]['url'] . '.php';
			# On écrit le fichier
			if(plxUtils::write(plxUtils::sanitizePhp($content['content']), $filename))
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			else
				return plxMsg::Error(L_SAVE_ERR.' '.$filename);
		}
	}

	/**
	 *  Méthode qui retourne le prochain id d'un article
	 *
	 * @return	string	id d'un nouvel article alignés sur 4 digits
	 * @author	Stephane F., J.P. Pourrez "bazooka07"
	 **/
	public function nextIdArticle() {

		$aKeys = array_keys($this->plxGlob_arts->aFiles);
		if(is_array($aKeys) and count($aKeys) > 0) {
			rsort($aKeys);
			$lastId = intval($aKeys['0']);
			if($lastId == 9999) {
				# On va rechercher des dents creuses dans la numérotation des articles
				sort($aKeys);
				foreach($aKeys as $key=>$value) {
					if(str_pad($key + 1, 4, '0', STR_PAD_LEFT) != $value) {
						$lastId = $key;
						break;
					}
				}
			}
			$lastId++;
			return str_pad($lastId, 4, '0', STR_PAD_LEFT);
		}

		return '0001';
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
		$newArticle = false;
		if($id == '0000' OR $id == '') {
			$id = $this->nextIdArticle();
			$newArticle = true;
		} elseif(!preg_match('#^_?\d{4}$#', $id)) {
			# identifiant incorrect
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
		$dates = array();
		foreach(array('creation', 'update', 'publication') as $context) {
			$dates[$context] = '';
			foreach(array('year', 'month', 'day') as $part) {
				$fieldName = 'date_' . $context . '_' . $part;
				if(!isset($content[$fieldName])) {
					break;
				}
				$dates[$context] .= $content[$fieldName];
			}
			if(strlen($dates[$context]) != 8) {
				$dates[$context] = date('YmdHi');
			} else {
				$dates[$context] .= substr(str_replace(':', '', $content['date_' . $context . '_time']), 0, 4);
			}
		}
		if(isset($content['date_update_old']) and $dates['update'] == $content['date_update_old']) {
			$dates['update'] = date('YmdHi');
		}
		# Génération du fichier XML
		if(empty($content['template']) or !file_exists(PLX_ROOT . $this->aConf['racine_themes'] . $this->aConf['style'] . '/' . basename($content['template']))) {
			$content['template']= 'article.php';
		}
		if(!in_array($content['allow_com'], array('0', '1', '2'))) {
			$content['allow_com'] = '0';
		}
		$meta_description = plxUtils::getValue($content['meta_description']);
		$meta_keywords = plxUtils::getValue($content['meta_keywords']);
		$title_htmltag = plxUtils::getValue($content['title_htmltag']);
		$thumbnail = plxUtils::getValue($content['thumbnail']);
		$thumbnail_alt = plxUtils::getValue($content['thumbnail_alt']);
		$thumbnail_title = plxUtils::getValue($content['thumbnail_title']);
		ob_start();
?>
<document>
	<title><?= plxUtils::strCheck(trim($content['title']), true) ?></title>
	<allow_com><?= intval($content['allow_com']) ?></allow_com>
	<template><?= basename($content['template']) ?></template>
	<chapo><![CDATA[<?= plxUtils::sanitizePhpTags(trim($content['chapo'])) ?>]]></chapo>
	<content><![CDATA[<?= plxUtils::sanitizePhpTags(trim($content['content'])) ?>]]></content>
	<tags><?= plxUtils::strCheck(trim($content['tags']), true) ?></tags>
	<meta_description><?= plxUtils::strCheck(trim($meta_description)) ?></meta_description>
	<meta_keywords><?= plxUtils::strCheck(trim($meta_keywords)) ?></meta_keywords>
	<title_htmltag><?= plxUtils::strCheck(trim($title_htmltag)) ?></title_htmltag>
	<thumbnail><?= plxUtils::strCheck(trim($thumbnail)) ?></thumbnail>
	<thumbnail_alt><?= plxUtils::strCheck(trim($thumbnail_alt), true) ?></thumbnail_alt>
	<thumbnail_title><?= plxUtils::strCheck(trim($thumbnail_title), true) ?></thumbnail_title>
	<date_creation><?= $dates['creation'] ?></date_creation>
	<date_update><?= $dates['update'] ?></date_update>
<?php
		# Hook plugins
		eval($this->plxPlugins->callHook('plxAdminEditArticleXml'));
?>
</document>
<?php
		# Recherche du nom du fichier correspondant à l'id
		$oldArt = $this->plxGlob_arts->query('/^'.$id.'.(.*).xml$/','','sort',0,1,'all');

		# Si demande de modération de l'article ou publication/brouillon
		$id = preg_replace('#^_?#', isset($content['moderate']) ? '_' : '' , $id);

		# On genère le nom de notre fichier
		if(!preg_match('/^\d{12}$/', $dates['publication'])) {
			$dates['publication'] = date('YmdHi'); # Check de la date au cas ou...
		}
		if(empty($content['catId'])) {
			$content['catId']=array('000'); # Catégorie non classée
		} else {
			if(empty(array_diff($content['catId'], ['draft', 'pin']))) {
				$content['catId'][] = '000'; # Catégorie non classée mais draft ou pin
			}
			# on trie les catégories
			uasort($content['catId'], function($a, $b) {
				foreach(array('draft', 'pin', 'home') as $v) {
					if($a == $v) { return -1; }
					if($b == $v) { return 1; }
				}

				return strcmp($a, $b);
			});
		}
		$filename = PLX_ROOT.$this->aConf['racine_articles'].$id.'.'.implode(',', $content['catId']).'.'.trim($content['author']).'.'.$dates['publication'].'.'.$content['url'].'.xml';

		# On va mettre à jour notre fichier
		if(plxUtils::write(XML_HEADER . ob_get_clean(), $filename)) {
			# suppression ancien fichier si nécessaire
			if($oldArt) {
				$oldfilename = PLX_ROOT.$this->aConf['racine_articles'].$oldArt['0'];
				if($oldfilename!=$filename AND file_exists($oldfilename))
					unlink($oldfilename);
			}

			# mise à jour de la liste des tags
			$this->aTags[$id] = array(
				'tags'		=> trim($content['tags']),
				'date'		=> $dates['publication'],
				'active'	=> intval(!in_array('draft', $content['catId'])),
			);
			$this->editTags();

			$msg = $newArticle ? L_ARTICLE_SAVE_SUCCESSFUL : L_ARTICLE_MODIFY_SUCCESSFUL;

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
			return plxMsg::Info(L_ARTICLE_DELETE_SUCCESSFUL);
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

		$idx = $this->nextIdArtComment($artId);
		$time = time();

		# On peut créer le commentaire
		if($this->addCommentaire(array(
			'author' => $this->aUsers[$_SESSION['user']]['name'],
			'content' => $content['content'],
			'site' => $this->racine,
			'ip' => plxUtils::getIp(),
			'type' => 'admin',
			'mail' => $this->aUsers[$_SESSION['user']]['email'],
			'parent' => $content['parent'],
			'filename' => $artId.'.'.$time.'-'.$idx.'.xml',
		))) # Commentaire OK
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

		# Génération du nom du fichier
		$filename = $id . '.xml';
		if(!file_exists(PLX_ROOT.$this->aConf['racine_commentaires'].$filename))
			# Commentaire inexistant
			return plxMsg::Error(L_ERR_UNKNOWN_COMMENT);

		# Contrôle des saisies
		if(trim($content['mail'])!='' AND !plxUtils::checkMail(trim($content['mail'])))
			return plxMsg::Error(L_ERR_INVALID_EMAIL);

		if(trim($content['site'])!='' AND !plxUtils::checkSite($content['site']))
			return plxMsg::Error(L_ERR_INVALID_SITE);

		# On récupère les infos du commentaire
		$com = $this->parseCommentaire(PLX_ROOT.$this->aConf['racine_commentaires'] . $filename);

		$comment = array(
			'filename'	=> $filename,
			'author'	=> $content['author'],
			'site'		=> $content['site'],
			'content'	=> $content['content'],
			'mail'		=> $content['mail'],
			'site'		=> $content['site'],
			'ip'		=> $com['ip'],
			'type'		=> $com['type'],
			'parent'	=> $com['parent'],
		);

		# Génération du nouveau nom du fichier
		$time = explode(':', $content['date_publication_time']);
		$newtimestamp = mktime($time[0], $time[1], 0, $content['date_publication_month'], $content['date_publication_day'], $content['date_publication_year']);
		$com = $this->comInfoFromFilename($id.'.xml');
		$newid = $com['comStatus'].$com['artId'].'.'.$newtimestamp.'-'.$com['comIdx'];
		$comment['filename'] = $newid.'.xml';

		# Suppression de l'ancien commentaire
		if($this->delCommentaire($id)) {
			unset($_SESSION['info']); # on supprime message d'information du commentaire original supprimé

			# Création du nouveau commentaire
			$id = $newid;
			if($this->addCommentaire($comment))
				return plxMsg::Info(L_COMMENT_SAVE_SUCCESSFUL);
			else
				return plxMsg::Error(L_COMMENT_UPDATE_ERR);
		}
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
		if(file_exists($filename) and unlink($filename)) {
			return plxMsg::Info(L_COMMENT_DELETE_SUCCESSFUL);
		}

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
		ob_start();
?>
<document>
<?php
		foreach($this->aTags as $id => $tag) {
			# On force la valeur en minuscules pour éviter des problèmes de tri dans plxShow::tagList()
?>
	<article number="<?= $id ?>" date="<?= $tag['date'] ?>" active="<?= $tag['active'] ?>"><?= plxUtils::strCheck(strtolower($tag['tags'])) ?></article>
<?php
		}
?>
</document>
<?php

		# On écrit le fichier
		return plxUtils::write(XML_HEADER . ob_get_clean(), path('XMLFILE_TAGS'));

	}

	/**
	 * Méthode qui vérifie sur le site de PluXml la dernière version et la compare avec celle en local.
	 *
	 * @return	string	contenu innerHTML de la balise <p> contenant l'etat et le style du contrôle du numéro de version
	 * @author	Florent MONTHEL, Amaury GRAILLAT, Stephane F et J.P. Pourrez (aka bazooka07)
	 **/
	public function checkMaj() {

		$latest_version = 'ERR';
		# test avec curl
		if(function_exists('curl_init')) {
			$ch = curl_init(PLX_URL_VERSION);
			curl_setopt_array($ch, array(
				CURLOPT_HEADER => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTPHEADER => array(
					'Accept: text/plain',
					'User-Agent: PluXml/' . PLX_VERSION,
				),
				CURLOPT_MAXREDIRS => 5,
				CURLOPT_MAXFILESIZE_LARGE => 1024, # Taille du fichier + entêtes HHTP
			));
			$latest_version = curl_exec($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if(
				$latest_version === false or
				$http_code != 200 or
				!is_string($latest_version) or
				!preg_match(self::VERSION_PATTERN, $latest_version)
			) {
				$latest_version = 'ERR';
			}
			curl_close($ch);
		}
		# test avec allow_url_open et file_get_contents ?
		elseif(get_cfg_var('allow_url_fopen')) {
			$latest_version = @file_get_contents(PLX_URL_VERSION, false, null, 0, 16);
			if(
				empty($latest_version) or
				!is_string($latest_version) or
				!preg_match(self::VERSION_PATTERN, $latest_version)
			) {
				$latest_version = 'UNAVAILABLE';
			}
		}

		$className = 'red';
		$dataInfos = '';
		if(in_array($latest_version, array(
			'UNAVAILABLE',
			'ERR',
		))) {
			$msg = constant('L_PLUXML_UPDATE_' . $latest_version);
			# Pour tester avec le navigateur web
			$infos = json_encode(array(
				'urlRepo' => PLX_URL_REPO,
				'urlVersion' => PLX_URL_VERSION,
				'currentVersion' => PLX_VERSION,
				'available' => L_PLUXML_UPDATE_AVAILABLE,
				'uptodate' => L_PLUXML_UPTODATE.' ('.PLX_VERSION.')',
			));
			$dataInfos = "data-infos='" . $infos . "'";
		}
		elseif(version_compare(PLX_VERSION, $latest_version, ">=")) {
			# Dernière mise à jour utilisée. Rien de nouveau !
			$msg = L_PLUXML_UPTODATE.' ('.PLX_VERSION.')';
			$className = 'green';
		}
		else {
			# Une mise à jour est disponible
			$this->update_link = sprintf('%s : <a href="%s">%s</a>', L_PLUXML_UPDATE_AVAILABLE, PLX_URL_REPO, PLX_URL_REPO);
			$msg = $this->update_link;
			$className = 'orange';
		}
		ob_start();
?>
<p id="latest-version" class="alert <?= $className ?>" <?= $dataInfos ?>><?= $msg ?></p>
<?php
		return ob_get_clean();

	}

}
