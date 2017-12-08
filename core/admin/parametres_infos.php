<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<div class="inline-form action-bar">
	<h2><?php echo L_CONFIG_INFOS_TITLE ?></h2>
	<p><strong><?php echo L_PLUXML_CHECK_VERSION ?></strong></p>
	<p><?php echo $plxAdmin->checkMaj(); ?></p>
</div>

<p><?php echo L_CONFIG_INFOS_DESCRIPTION ?></p>

<p><strong><?php echo L_PLUXML_VERSION; ?> <?php echo PLX_VERSION; ?> (<?php echo L_INFO_CHARSET ?> <?php echo PLX_CHARSET ?>)</strong></p>
<ul class="unstyled-list">
	<li><?php echo L_INFO_PHP_VERSION; ?> : <?php echo phpversion(); ?></li>
	<?php if (!empty($_SERVER['SERVER_SOFTWARE'])) { ?>
	<li><?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
	<?php } ?>
</ul>
<ul class="unstyled-list">
	<?php plxUtils::testWrite(PLX_ROOT) ?>
	<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH); ?>
	<?php plxUtils::testWrite(PLX_ROOT.PLX_CONFIG_PATH.'plugins/'); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_articles']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_commentaires']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_statiques']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['medias']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_plugins']); ?>
	<?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_themes']); ?>	
	<?php plxUtils::testModReWrite() ?>
	<?php plxUtils::testLibGD() ?>
	<?php plxUtils::testLibXml() ?>
	<?php plxUtils::testMail() ?>
</ul>
<p><?php echo L_CONFIG_INFOS_NB_CATS ?> <?php echo sizeof($plxAdmin->aCats); ?></p>
<p><?php echo L_CONFIG_INFOS_NB_STATICS ?> <?php echo sizeof($plxAdmin->aStats); ?></p>
<p><?php echo L_CONFIG_INFOS_WRITER ?> <?php echo $plxAdmin->aUsers[$_SESSION['user']]['name'] ?></p>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsInfos')) ?>
<script type="text/javascript">
function check_maj(response) {
	'use strict';

	const node = document.querySelector('span.latest-version.error[data-update]');
	const data = JSON.parse(node.getAttribute('data-update'));
	const version = data.version;
	var release;
	if(typeof response === 'string') {
		release = response;
	} else if(typeof response === 'object' && typeof response.data.tag_name !== 'undefined') {
		/* https://developer.github.com/v3/repos/releases/#get-the-latest-release */
		release = response.data.tag_name;
	} else {
		return;
	}

	function compareVersion(v1, v2) {
	    var v1parts = v1.split('.'), v2parts = v2.split('.');
	    var maxLen = Math.max(v1parts.length, v2parts.length);
	    var result = 0;
	    for(var i = 0; i < maxLen; i++) {
	        var part1 = parseInt(v1parts[i], 10) || 0;
	        var part2 = parseInt(v2parts[i], 10) || 0;
	        if(part1 > part2) {
	            result = 1;
	            break;
			} else if(part1 < part2) {
	            return -1;
	            break;
			}
	    }
	    return result;
	}

	node.classList.remove('error');
	if(compareVersion(release, version) > 0) {
		node.innerHTML = data.available;
		node.classList.add('blink');
	} else {
		node.textContent = data.uptodate;
		node.classList.add('success');
	}
}

(function() {
	'use strict';

	const node = document.querySelector('span.latest-version.error[data-update]');
	if(node != null) {
		/* https://developer.github.com/v3/#json-p-callbacks */
		const URL = 'https://api.github.com/repos/pluxml/PluXml/releases/latest';
		// const URL = 'http://kazimentou.fr/divers/PluXml-download';
		const script = document.createElement('SCRIPT');
		script.type = 'text/javascript';
		script.src = URL + '?callback=check_maj';
		document.head.appendChild(script);
	}
})();
</script>
<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>