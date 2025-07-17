<?php

/**
 * Page d'authentification
 *
 * @package PLX
 * @author    Stephane F, Florent MONTHEL, Pedro "P3ter" CADETE
 **/

# Constante pour retrouver la page d'authentification
const PLX_AUTHPAGE = true;

include 'prepend.php';

$admin_url = preg_replace('#/auth\.php$#', '/', $_SERVER['SCRIPT_NAME']);
$redirect = $admin_url;

# Déconnexion (paramètre url : ?d=1)
if(!empty($_GET['d'])) {
	if($_GET['d'] == '1') {
		# Maybe a comment is posted by a subscriber
		if(!empty($_SERVER['HTTP_REFERER'])) {
			if(!empty($plxAdmin->aConf['urlrewriting'])) {
				$pattern = '#^' . preg_quote(plxUtils::getRacine() . L_ARTICLE_URL) . '/([\w-]+)$#';
				if(preg_match($pattern, $_SERVER['HTTP_REFERER'], $matches)) {
					$mask = '#\.' . $matches[1] . '\.xml$#';
				}
			} else {
				$pattern = '#^' . preg_quote(plxUtils::getRacine() . 'index.php?' . L_ARTICLE_URL) . '(\d+)/([\w-]+)$#';
				if(preg_match($pattern, $_SERVER['HTTP_REFERER'], $matches)) {
					$mask = '#^' . str_pad( $matches[1], 4, '0', STR_PAD_LEFT) . '\..*\.\d{3}\.\d{12}\.' . $matches[2] . '\.xml$#';
				}
			}

			if(!empty($mask)) {
				# Deconnexion from site
				$parts = parse_url(plxUtils::getRacine());
				$redirect = $parts['path'];
				if(PLX_SITE_LANG == $plxAdmin->aConf['default_lang']) {
					# subscriber's language is same as language of site
					$globArts = $plxAdmin->plxGlob_arts->query($mask);
					if(!empty($globArts)) {
						$redirect = $_SERVER['HTTP_REFERER'];
					}
				}
			}
		}
	}

	# PHP Script is stopped by :
	log_out($redirect);
}

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Protection anti brute force
const MAX_LOGIN_COUNT = 3; # nombre de tentative de connexion autorisé dans la limite de temps autorisé
const MAX_LOGIN_TIME = 5 * 60; # temps d'attente limite si nombre de tentative de connexion atteint (en minutes)

# Initialiser les messages d'alerte
$msg = '';
$css = '';

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminAuthPrepend'));

if(!isset($_GET['action'])) {
	# Identifier une erreur de connexion
	if(empty($_SESSION['maxtry'])) {
		# initialisation de la variable qui compte les tentatives de connexion
		$_SESSION['maxtry'] = [
			'counter' => MAX_LOGIN_COUNT,
			'timer' => time() + MAX_LOGIN_TIME,
		];
	} else {
		if($_SESSION['maxtry']['counter'] <= 1) {
			if (time() < $_SESSION['maxtry']['timer']) {
				# écriture dans les logs du dépassement des 3 tentatives successives de connexion
				@error_log('PluXml: Max login failed. IP : ' . plxUtils::getIp());
				# message à affiche sur le mire de
				$msg = sprintf(L_ERR_MAXLOGIN, round(($_SESSION['maxtry']['timer'] - time()) / 60));
				$css = 'alert red';
			} else {
				# on réinitialise le control brute force quand le temps d'attente limite est atteint
				$_SESSION['maxtry'] = [
					'counter' => MAX_LOGIN_COUNT,
					'timer' => time()  + MAX_LOGIN_TIME,
				];
			}
		}
	}

	if(empty($css)) {
		# On filtre $_GET['p'] si besoin. Nécessaire pour le paramètre action dans le formulaire
		if (!empty($_GET['p'])) {
			$racine = parse_url($plxAdmin->racine);
			$get_p = parse_url(urldecode($_GET['p']));
			$failure = (!$get_p or (isset($get_p['host']) and $racine['host'] != $get_p['host']));
			if (!$failure and !empty($get_p['path'])) {
				if(preg_match('#^' . $admin_url . '([\w-]+\.php)?$#', $get_p['path']) and file_exists($_SERVER['DOCUMENT_ROOT'] . $get_p['path'])) {
					# url de redirection
					$redirect = $get_p['path'];
				} elseif(preg_match('#^' . $racine['path'] . 'index\.php$#', $get_p['path']) and file_exists($_SERVER['DOCUMENT_ROOT'] . $get_p['path'])) {
					# login for subscribers - No url_rewriting
					$redirect = $get_p['path'];
					$fromSite = true;

					# filtrage des parametres de l'url
					if (isset($get_p['query'])) {
						$param = strtok($get_p['query'], '=');
						if($param != 'd') {
							$redirect .= '?' . $get_p['query'];
						}
					}
				} else {
					# login for subscribers
					if(!empty($plxAdmin->aConf['urlrewriting'])) {
						$pattern = '#^' . preg_quote($racine['path'] . L_ARTICLE_URL) . '/([\w-]+)$#';
						if(preg_match($pattern, $get_p['path'], $matches)) {
							$mask = '#\.' . $matches[1] . '\.xml$#';
							$globArts = $plxAdmin->plxGlob_arts->query($mask);
							if(!empty($globArts)) {
								$redirect = $get_p['path'];
								$fromSite = true;
							}
						}
					}
				}
			}
		}

		# Authentification
		if ($_SESSION['maxtry']['counter'] >= 0 and !empty($_POST['login']) and !empty($_POST['password'])) {
			# décrémente la variable de session qui compte les tentatives de connexion
			$_SESSION['maxtry']['counter']--;

			$users = array_filter(
				$plxAdmin->aUsers,
				function($value) {
					return (!$value['delete'] and !empty($value['password']) and $value['active'] and $value['login'] == $_POST['login']);
				}
			);
			if(!empty($users)) {
				foreach($users as $userid => $user) {
					if(sha1($user['salt'] . md5($_POST['password'])) === $user['password']) {
						# Login successfull !

						$_SESSION['user'] = $userid;
						$_SESSION['profil'] = $user['profil'];
						$_SESSION['hash'] = plxUtils::charAleatoire(10);
						$_SESSION['domain'] = SESSION_DOMAIN;
						$_SESSION['ip'] = $_SERVER['REMOTE_ADDR']; // for security
						# on définit $_SESSION['admin_lang'] pour stocker la langue à utiliser la 1ere fois dans le chargement des plugins une fois connecté à l'admin
						# ordre des traitements:
						# page administration : chargement fichier prepend.php
						# => creation instance plxAdmin : chargement des plugins, chargement des prefs utilisateurs
						# => chargement des langues en fonction du profil de l'utilisateur connecté déterminé précédemment
						if(!$fromSite) {
							$_SESSION['admin_lang'] = $user['lang'];
						}
						$plxAdmin->log_connexion($userid);

						unset($_SESSION['maxtry']);
						if(
							$redirect == $admin_url and
							$plxAdmin->nbArticles('all', ($_SESSION['profil'] < PROFIL_WRITER) ? '\d{3}' : $_SESSION['user']) == 0
						) {
							# premier article
							$redirect .= ($_SESSION['profil'] > PROFIL_WRITER)  ? 'profil.php' : 'article.php';
						}

						header('Location: ' . $redirect);
						exit;
					}
				}
			}

			$msg = L_ERR_WRONG_PASSWORD . ' (' . $_SESSION['maxtry']['counter']. ')';
			$css = 'alert red';
		}
	}

	# Password change
	if(empty($css) and $plxAdmin->aConf['lostpassword']) {
		if (!empty($_POST['lostpassword_id'])) {
			# Send lost password e-mail
			if ($plxAdmin->sendLostPasswordEmail($_POST['lostpassword_id'])) {
				$msg = L_LOST_PASSWORD_SUCCESS;
				$css = 'alert green';
			} else {
				@error_log('Lost password error. ID : ' . $_POST['lostpassword_id'] . ' IP : ' . plxUtils::getIp());
				$msg = L_UNKNOWN_ERROR;
				$css = 'alert red';
			}
		} elseif(!empty($_POST['editpassword'])) {
			# Change password
			unset($_SESSION['error']);
			unset($_SESSION['info']);
			$plxAdmin->editPassword($_POST);
			if (!empty($msg = isset($_SESSION['error'][0]) ? $_SESSION['error'][0] : '')) {
				$css = 'alert red';
			} else {
				if (!empty($msg = isset($_SESSION['info'][0]) ? $_SESSION['info'][0] : '')) {
					$css = 'alert green';
				}
			}
			unset($_SESSION['error']);
			unset($_SESSION['info']);
		}
	}
}

# Construction de la page HTML
plxUtils::cleanHeaders();
?>
<!DOCTYPE html>
<html lang="<?= $plxAdmin->aConf['default_lang'] ?>">
<head>
	<meta name="robots" content="noindex, nofollow"/>
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<meta charset="<?= strtolower(PLX_CHARSET) ?>" />
	<title>PluXml - <?= L_AUTH_PAGE_TITLE ?></title>
<?php
	plxUtils::printLinkCss();

	# Hook Plugins
	eval($plxAdmin->plxPlugins->callHook('AdminAuthEndHead'));

	$lostPasswordUrl = 'auth.php?p=' . $admin_url;
?>
</head>
<body id="auth">
<main class="container">
	<section class="grid">
		<div class="logo"></div>
		<div class="auth col sml-11 sml-centered med-5 lrg-3">
<?php
			# Hook plugins
			eval($plxAdmin->plxPlugins->callHook('AdminAuthBegin'));

			switch (isset($_GET['action']) ? $_GET['action'] : false) {
				case 'lostpassword': # Affichage du formulaire d'envoi du mail de changement de mot de passe
					# Hook plugins
					eval($plxAdmin->plxPlugins->callHook('AdminAuthTopLostPassword'));
?>
					<form action="<?= $_SERVER['PHP_SELF'] ?><?= !empty($redirect) ? '?p=' . plxUtils::strCheck(urlencode($redirect)) : '' ?>"
						  method="post" id="form_auth">
						<fieldset>
							<?= plxToken::getTokenPostMethod() ?>
							<h1 class="h5 text-center"><strong><?= L_LOST_PASSWORD ?></strong></h1>
							<div class="grid">
								<div class="col sml-12">
									<i class="ico icon-user"></i>
									<?php plxUtils::printInput('lostpassword_id', (!empty($_POST['lostpassword_id'])) ? plxUtils::strCheck($_POST['lostpassword_id']) : '', 'text', '10-255', false, 'full-width', L_AUTH_LOST_FIELD, 'autofocus'); ?>
								</div>
							</div>
							<div class="grid">
								<div class="col sml-12">
									<small><a href="<?= $lostPasswordUrl ?>"><?= L_LOST_PASSWORD_LOGIN ?></a></small>
								</div>
							</div>
<?php
					# Hook plugins
					eval($plxAdmin->plxPlugins->callHook('AdminAuthLostPassword'));
?>
							<div class="grid">
								<div class="col sml-12 text-center">
									<input class="blue" type="submit" value="<?= L_SUBMIT_BUTTON ?>"/>
								</div>
							</div>
						</fieldset>
					</form>
<?php
					break; # End of : case 'lostpassword'
				case 'changepassword': # Affichage du formulaire de changement de mot passe
					$lostPasswordToken = filter_has_var(INPUT_GET, 'token')? $_GET['token']: false;# Fix Warning: Undefined array key "token"
					if ($lostPasswordToken and $plxAdmin->verifyLostPasswordToken($lostPasswordToken)) {
						# Hook plugins
						eval($plxAdmin->plxPlugins->callHook('AdminAuthTopChangePassword'));
?>
						<form action="<?= $_SERVER['PHP_SELF'] ?><?= !empty($redirect) ? '?p=' . plxUtils::strCheck(urlencode($redirect)) : '' ?>"
							  method="post" id="form_auth">
							<fieldset>
								<?= plxToken::getTokenPostMethod() ?>
								<input name="lostPasswordToken" value="<?= $lostPasswordToken ?>" type="hidden"/>
								<h1 class="h5 text-center"><strong><?= L_PROFIL_CHANGE_PASSWORD ?></strong></h1>
<?php plxUtils::printInputPasswords('full-width'); ?>
								<div class="grid">
									<div class="col sml-12">
										<small><a href="<?= $lostPasswordUrl ?>"><?= L_LOST_PASSWORD_LOGIN ?></a></small>
									</div>
								</div>
<?php
						# Hook plugins
						eval($plxAdmin->plxPlugins->callHook('AdminAuthChangePassword'));
?>
								<div class="grid">
									<div class="col sml-12 text-center">
										<input type="submit" name="editpassword"
											   value="<?= L_PROFIL_UPDATE_PASSWORD ?>"/>
									</div>
								</div>
							</fieldset>
						</form>
<?php
					} else {
						# Hook plugins
						eval($plxAdmin->plxPlugins->callHook('AdminAuthTopChangePasswordError'));
?>
						<h1 class="h5 text-center"><strong><?= L_PROFIL_CHANGE_PASSWORD ?></strong></h1>
						<div class="alert red">
							<?= L_LOST_PASSWORD_ERROR ?>
						</div>
						<small><a href="<?= $lostPasswordUrl ?>"><?= L_LOST_PASSWORD_LOGIN ?></a></small>
<?php
						# Hook plugins
						eval($plxAdmin->plxPlugins->callHook('AdminAuthChangePasswordError'));
					}
					break; # End of : case 'changepassword'
				default: # Affichage du formulaire de connexion
?>
<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthTop')) # Hook plugins
?>
					<form id="form_auth" method="post" >
						<fieldset>
							<?= plxToken::getTokenPostMethod() ?>
							<h1 class="h5 text-center"><strong><?= L_LOGIN_PAGE ?></strong></h1>
<?php
					if(!empty($msg)) {
						plxUtils::showMsg($msg, $css);
					}
?>
							<div class="grid">
								<div class="col sml-12">
									<i class="ico icon-user"></i>
									<?php plxUtils::printInput('login', (!empty($_POST['login'])) ? plxUtils::strCheck($_POST['login']) : '', 'text', '10-255', false, 'full-width', L_AUTH_LOGIN_FIELD, 'autofocus'); ?>
								</div>
							</div>
							<div class="grid">
								<div class="col sml-12">
									<i class="ico icon-lock"></i>
									<?php plxUtils::printInput('password', '', 'password', '10-255', false, 'full-width', L_AUTH_PASSWORD_FIELD); ?>
								</div>
							</div>
<?php
							if ($plxAdmin->aConf['lostpassword']) {
?>
								<div class="grid">
									<div class="col sml-12">
										<small><a href="?action=lostpassword"><?= L_LOST_PASSWORD ?></a></small>
									</div>
								</div>
<?php
							}

							# Hook Plugins
							eval($plxAdmin->plxPlugins->callHook('AdminAuth'));
?>
							<div class="grid">
								<div class="col sml-12 text-center">
									<input class="blue" type="submit" value="<?= L_SUBMIT_BUTTON ?>"/>
								</div>
							</div>
						</fieldset>
					</form>
<?php
			} # End of : switch
?>
			<p class="text-center">
				<small><a class="back" href="<?= PLX_ROOT; ?>"><?= L_BACK_TO_SITE ?></a> - <?= L_POWERED_BY ?></small>
			</p>
		</div>

	</section>
</main>
<?php eval($plxAdmin->plxPlugins->callHook('AdminAuthEndBody')); # Hook Plugins ?>
	<script src="js/visual.js?v=<?= PLX_VERSION ?>"></script>
</body>
</html>
