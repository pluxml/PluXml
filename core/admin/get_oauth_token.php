<?php

# https://console.cloud.google.com/apis/credentials
# https://developers.google.com/identity/protocols/oauth2/

# https://developer.yahoo.com/oauth2/guide/

/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.5
 * @package PHPMailer
 * @see https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Get an OAuth2 token from an OAuth2 provider.
 * * Install this script on your server so that it's accessible
 * as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
 * e.g.: http://localhost/phpmailer/get_oauth_token.php
 * * Ensure dependencies are installed with 'composer install'
 * * Set up an app in your Google/Yahoo/Microsoft account
 * * Set the script address as the app's redirect URL
 * If no refresh token is obtained when running this file,
 * revoke access to your app and run the script again.
 */

namespace PHPMailer\PHPMailer;

/**
 * Aliases for League Provider Classes
 * Make sure you have added these to your composer.json and run `composer install`
 * Plenty to choose from here:
 * @see https://oauth2-client.thephpleague.com/providers/thirdparty/
 */
//@see https://github.com/thephpleague/oauth2-google
use League\OAuth2\Client\Provider\Google;
//@see https://packagist.org/packages/hayageek/oauth2-yahoo
use Hayageek\OAuth2\Client\Provider\Yahoo;
//@see https://github.com/stevenmaguire/oauth2-microsoft
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
//@see https://github.com/greew/oauth2-azure-provider
use Greew\OAuth2\Client\Provider\Azure;

include 'prepend.php';

if (!isset($_GET['code']) && !isset($_POST['provider'])) {
	include 'top.php';
?>
			<div class="inline-form action-bar">
				<h2><?= L_CONFIG_ADVANCED_SMTPOAUTH_GETTOKEN ?></h2>
				<p><a class="back" href="parametres_avances.php"><?= L_CONFIG_ADVANCED_DESC ?></a></p>
			</div>
			<form method="post" id="form_Oauth2_token">
				<div class="grid">
					<div class="col sml-5">
						<label for="id_provider"><?= L_GET_OAUTH_TOKEN_PROVIDER ?></label>
					</div>
					<div class="col sml-7">
						<select id="id_provider" name="provider" required>
							<option value="">...</option>
<?php
	$default = isset($plxAdmin->aConf['smtpOauth2_provider']) ? ucfirst($plxAdmin->aConf['smtpOauth2_provider']) : '';
	$aClassProviders = array(
		'League\\OAuth2\\Client\\Provider\\Google',
		'Hayageek\\OAuth2\\Client\\Provider\\Yahoo',
		'Stevenmaguire\\OAuth2\\Client\\Provider\\Microsoft',
		'Greew\\OAuth2\\Client\\Provider\\Azure',
	);
	foreach($aClassProviders as $aClass) {
		if(!class_exists($aClass)) {
			continue;
		}
		$k = preg_replace('#.*\\\(\w+)$#', '$1', $aClass);
		$selected = ($k == $default) ? ' selected' : '';
?>
							<option value="<?= $k ?>"<?= $selected ?>><?= $k ?></option>
<?php
	}
?>
						</select>
					</div>
				</div>
				<p><?= L_GET_OAUTH_TOKEN_DETAILS ?></p>
<?php
	foreach(array('clientId'=>'CLIENTID', 'clientSecret' => 'SECRETKEY', 'tenantId'=> 'TENANTID') as $k=>$v) {
		$id = 'smtpOauth2_' . $k;
		$value = !empty($plxAdmin->aConf[$id]) ? $plxAdmin->aConf[$id] : '';
		$caption = constant('L_CONFIG_ADVANCED_SMTPOAUTH_' . $v);
		$required = ($k != 'tenantId') ? ' required' : '';
?>
				<div class="grid">
					<div class="col med-5">
						<label for="id_<?= $k ?>"><?= $caption ?></label>
					</div>
					<div class="col med-7">
						<input id="id_<?= $k ?>" type="text" name="<?= $k ?>" value="<?= $value ?>"<?= $required ?>>
					</div>
				</div>

<?php
	}
?>
				<p><input type="submit"></p>
			</form>
			<script>
				(function () {
					'use strict';
					setMsg();
				})()
			</script>
		</main>
	</body>
</html>
<?php
	exit;
}

require '../vendor/autoload.php';

session_start();

$providerName = '';
$clientId = '';
$clientSecret = '';
$tenantId = '';

if (array_key_exists('provider', $_POST)) {
	$providerName = $_POST['provider'];
	$clientId = $_POST['clientId'];
	$clientSecret = $_POST['clientSecret'];
	$tenantId = $_POST['tenantId'];
	$_SESSION['provider'] = $providerName;
	$_SESSION['clientId'] = $clientId;
	$_SESSION['clientSecret'] = $clientSecret;
	$_SESSION['tenantId'] = $tenantId;
} elseif (array_key_exists('provider', $_SESSION)) {
	$providerName = $_SESSION['provider'];
	$clientId = $_SESSION['clientId'];
	$clientSecret = $_SESSION['clientSecret'];
	$tenantId = $_SESSION['tenantId'];
}

//If you don't want to use the built-in form, set your client id and secret here
//$clientId = 'RANDOMCHARS-----duv1n2.apps.googleusercontent.com';
//$clientSecret = 'RANDOMCHARS-----lGyjPcRtvP';

//If this automatic URL doesn't work, set it yourself manually to the URL of this script
$redirectUri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
//$redirectUri = 'http://localhost/PHPMailer/redirect';

$params = [
	'clientId' => $clientId,
	'clientSecret' => $clientSecret,
	'redirectUri' => $redirectUri,
	'accessType' => 'offline'
];

$options = [];
$provider = null;

switch ($providerName) {
	case 'Google':
		$provider = new Google($params);
		$options = [
			'scope' => [
				'https://mail.google.com/'
			]
		];
		break;
	case 'Yahoo':
		$provider = new Yahoo($params);
		break;
	case 'Microsoft':
		$provider = new Microsoft($params);
		$options = [
			'scope' => [
				'wl.imap',
				'wl.offline_access'
			]
		];
		break;
	case 'Azure':
		$params['tenantId'] = $tenantId;

		$provider = new Azure($params);
		$options = [
			'scope' => [
				'https://outlook.office.com/SMTP.Send',
				'offline_access'
			]
		];
		break;
}

if (null === $provider) {
	exit('Provider missing');
}

if (!isset($_GET['code'])) {
	//If we don't have an authorization code then get one
	$authUrl = $provider->getAuthorizationUrl($options);
	$_SESSION['oauth2state'] = $provider->getState();
	header('Location: ' . $authUrl);
	exit;
	//Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
	unset($_SESSION['oauth2state']);
	unset($_SESSION['provider']);
	exit('Invalid state');
} else {
	unset($_SESSION['provider']);
	//Try to get an access token (using the authorization code grant)
	$token = $provider->getAccessToken(
		'authorization_code',
		[
			'code' => $_GET['code']
		]
	);
	// Use this to interact with an API on the users behalf
	// Use this to get a new access token if the old one expires
	// echo 'Refresh Token: ', htmlspecialchars($token->getRefreshToken());
	$token = htmlspecialchars($token->getRefreshToken());

	if(!empty($token)) {
		$content =array(
			'smtpOauth2_emailAdress'	=> 'jpourrez@gmail.com',
			'smtpOauth2_refreshToken'	=> $token,
		);
		foreach(array('provider', 'clientId', 'clientSecret', 'tenantId',) as $k) {
			$content['smtpOauth2_' . $k] = $_SESSION[$k];
		}
		$plxAdmin->editConfiguration($plxAdmin->aConf, $content);
	} else {
		plxMsg::Error(L_GET_OAUTH_TOKEN_DENIED);
	}
	header('Location: parametres_avances.php');
}
