<?php

/**
 * Edition d'un commentaire
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentPrepend'));

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# Interdire de l'accès à la page si les commentaires sont désactivés
if(!$plxAdmin->aConf['allow_com']) {
    header('Location: index.php');
    exit;
}

# validation de l'id de l'article si passé en parametre
if(isset($_GET['a']) AND !preg_match('/^_?[0-9]{4}$/',$_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	header('Location: index.php');
	exit;
}

# On édite, supprime ou valide notre commentaire
if(!empty($_POST) AND !empty($_POST['comId'])) {

	# validation du numero de commentaire
	if(!preg_match('/[[:punct:]]?[0-9]{4}.[0-9]{10}-[0-9]+$/', $_POST['comId'])) {
		plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
		header('Location: comments.php');
		exit;
	}

	# Suppression, on redirige
	if(isset($_POST['delete'])) {
		$plxAdmin->delCommentaire($_POST['comId']);
		header('Location: comments.php');
		exit;
	}
	# Commentaire en ligne
	if(isset($_POST['online'])) {
		$plxAdmin->editCommentaire($_POST,$_POST['comId']);
		$plxAdmin->modCommentaire($_POST['comId'],'online');
		header('Location: comment.php?c='.$_POST['comId'].(!empty($_GET['a'])?'&a='.$_GET['a']:''));
		exit;
	}
	# Commentaire hors-ligne
	if(isset($_POST['offline'])) {
		$plxAdmin->editCommentaire($_POST,$_POST['comId']);
		$plxAdmin->modCommentaire($_POST['comId'],'offline');
		header('Location: comment.php?c='.$_POST['comId'].(!empty($_GET['a'])?'&a='.$_GET['a']:''));
		exit;
	}
	# Répondre au commentaire
	if(isset($_POST['answer'])) {
		header('Location: comment_new.php?c='.$_POST['comId']).(!empty($_GET['a'])?'&a='.$_GET['a']:'');
		exit;
	}
	# Edition
	$plxAdmin->editCommentaire($_POST,$_POST['comId']);
	header('Location: comment.php?c='.$_POST['comId'].(!empty($_GET['a'])?'&a='.$_GET['a']:''));
	exit;
}

# On va récupérer les infos sur le commentaire
if(!$plxAdmin->getCommentaires('/^'.plxUtils::nullbyteRemove($_GET['c']).'.xml$/','',0,1,'all')) {
	# Commentaire inexistant, on redirige
	plxMsg::Error(L_ERR_UNKNOWN_COMMENT);
	header('Location: comments.php');
	exit;
}

# On va récupérer les infos sur l'article
$artId = $plxAdmin->plxRecord_coms->f('article');
# On va rechercher notre article
if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$artId.'.(.+).xml$/','','sort',0,1)) == false) {
	# On indique que le commentaire est attaché à aucun article
	$article = '<strong>'.L_COMMENT_ORPHAN.'</strong>';
	# Statut du commentaire
	$statut = '<strong>'.L_COMMENT_ORPHAN_STATUS.'</strong>';
} else {
	$result = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	# On génère notre lien
	$article = '<a href="'.$plxAdmin->aConf['racine'].'index.php?article'.intval($result['numero']).'/'.$result['url'].'" title="'.L_COMMENT_ARTICLE_LINKED_TITLE.'">';
	$article .= plxUtils::strCheck($result['title']);
	$article .= '</a>';
}

# Statut du commentaire
$com=$plxAdmin->comInfoFromFilename($_GET['c'].'.xml');
if($com['comStatus']=='_')
	$statut = '<strong>'.L_COMMENT_OFFLINE.'</strong>';
elseif($com['comStatus']=='')
	$statut = '<a href="'.PLX_ROOT.'?article'.intval($plxAdmin->plxRecord_coms->f('article')).'/#c'.$plxAdmin->plxRecord_coms->f('numero').'" title="'.L_COMMENT_ONLINE_TITLE.'">'.L_COMMENT_ONLINE.'</a>';
else
	$statut = '';

# Date du commentaire
$date = plxDate::date2Array($plxAdmin->plxRecord_coms->f('date'));

# On inclut le header
include(dirname(__FILE__).'/top.php');

?>

<?php if(!empty($_GET['a'])) : ?>
	<p class="back"><a href="comments.php?a=<?php echo $_GET['a'] ?>"><?php echo L_BACK_TO_ARTICLE_COMMENTS ?></a></p>
<?php else : ?>
	<p class="back"><a href="comments.php"><?php echo L_BACK_TO_COMMENTS ?></a></p>
<?php endif; ?>

<h2><?php echo L_COMMENT_EDITING ?></h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentTop')) # Hook Plugins ?>

<ul>
	<li><?php echo L_COMMENT_IP_FIELD ?> : <?php echo $plxAdmin->plxRecord_coms->f('ip'); ?></li>
	<li><?php echo L_COMMENT_STATUS_FIELD ?> : <?php echo $statut; ?></li>
	<li><?php echo L_COMMENT_TYPE_FIELD ?> : <strong><?php echo $plxAdmin->plxRecord_coms->f('type'); ?></strong></li>
	<li><?php echo L_COMMENT_LINKED_ARTICLE_FIELD ?> : <?php echo $article; ?></li>
</ul>
<form action="comment.php<?php echo (!empty($_GET['a'])?'?a='.plxUtils::strCheck($_GET['a']):'') ?>" method="post" id="form_comment">
	<fieldset>
		<?php plxUtils::printInput('comId',$_GET['c'],'hidden'); ?>

		<p><label><?php echo L_COMMENT_DATE_FIELD ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('day',$date['day'],'text','2-2',false,'fld1'); ?>
		<?php plxUtils::printInput('month',$date['month'],'text','2-2',false,'fld1'); ?>
		<?php plxUtils::printInput('year',$date['year'],'text','2-4',false,'fld2'); ?>
		<?php plxUtils::printInput('time',$date['time'],'text','2-5',false,'fld2'); ?>
		<a href="javascript:void(0)" onclick="dateNow(<?php echo date('Z') ?>); return false;" title="<?php L_NOW; ?>"><img src="theme/images/date.png" alt="" /></a>

		<p><label for="id_author"><?php echo L_COMMENT_AUTHOR_FIELD ?> :</label></p>
		<?php plxUtils::printInput('author',plxUtils::strCheck($plxAdmin->plxRecord_coms->f('author')),'text','40-255') ?>
		<p><label for="id_site"><?php echo L_COMMENT_SITE_FIELD ?></label></p>
		<?php
			$site = plxUtils::strCheck($plxAdmin->plxRecord_coms->f('site'));
			plxUtils::printInput('site',$site,'text','40-255');
			if($site != '')	echo '<a href="'.$site.'">'.$site.'</a>';
		?>
		<p><label for="id_mail"><?php echo L_COMMENT_EMAIL_FIELD ?> :</label></p>
		<?php plxUtils::printInput('mail',plxUtils::strCheck($plxAdmin->plxRecord_coms->f('mail')),'text','40-255') ?>
		<?php if($plxAdmin->plxRecord_coms->f('mail') != '') : ?>
		<?php echo '<a href="mailto:'.$plxAdmin->plxRecord_coms->f('mail').'">'.$plxAdmin->plxRecord_coms->f('mail').'</a>' ?>
		<?php endif; ?>
		<p id="p_content"><label for="id_content"><?php echo L_COMMENT_ARTICLE_FIELD ?> :</label></p>
		<?php if($plxAdmin->plxRecord_coms->f('type') == 'admin') : ?>
			<?php plxUtils::printArea('content',plxUtils::strCheck($plxAdmin->plxRecord_coms->f('content')), 60, 7); ?>
		<?php else : ?>
			<?php plxUtils::printArea('content',$plxAdmin->plxRecord_coms->f('content'), 60, 7); ?>
		<?php endif; ?>
		<?php eval($plxAdmin->plxPlugins->callHook('AdminComment')) # Hook Plugins ?>
		<p class="center">
			<?php echo plxToken::getTokenPostMethod() ?>
			<input class="button delete" type="submit" name="delete" value="<?php echo L_DELETE ?>" onclick="Check=confirm('<?php echo L_COMMENT_DELETE_CONFIRM ?>');if(Check==false) return false;"/>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<?php if($com['comStatus']=='') : ?>
				<input class="button submit" type="submit" name="offline" value="<?php echo L_COMMENT_OFFLINE_BUTTON ?>" />
				<input class="button submit" type="submit" name="answer" value="<?php echo L_COMMENT_ANSWER_BUTTON ?>" />
			<?php else : ?>
				<input class="button submit" type="submit" name="online" value="<?php echo L_COMMENT_PUBLISH_BUTTON ?>" />
			<?php endif; ?>
			<input class="button update" type="submit" name="update" value="<?php echo L_COMMENT_UPDATE_BUTTON ?>" />
		</p>
	</fieldset>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
