<?php if(!defined('PLX_ROOT')) {
	exit;
}

if($plxShow->plxMotor->plxRecord_coms) {
?>
		<h3 id="comments"><?= $plxShow->artNbCom(); ?></h3>
<?php
	# On boucle sur les commentaires
	while($plxShow->plxMotor->plxRecord_coms->loop()) {
?>
		<div id="<?php $plxShow->comId(); ?>" class="comment <?php $plxShow->comLevel(); ?>">
			<div id="com-<?php $plxShow->comIndex(); ?>">
				<small>
					<a class="nbcom" href="<?php $plxShow->ComUrl(); ?>" title="#<?= $plxShow->plxMotor->plxRecord_coms->i+1 ?>">#<?= $plxShow->plxMotor->plxRecord_coms->i+1 ?></a>&nbsp;
					<time datetime="<?php $plxShow->comDate('#num_year(4)-#num_month-#num_day #hour:#minute'); ?>"><?php $plxShow->comDate('#day #num_day #month #num_year(4) - #hour:#minute'); ?></time> -
					<?php $plxShow->comAuthor('link'); ?>
					<?php $plxShow->lang('SAID'); ?> :
				</small>
				<blockquote>
					<p class="content_com type-<?php $plxShow->comType(); ?>"><?php $plxShow->comContent(); ?></p>
				</blockquote>
			</div>
<?php
		if($plxShow->plxMotor->plxRecord_arts->f('allow_com') AND $plxShow->plxMotor->aConf['allow_com']) {
?>
			<a rel="nofollow" href="<?php $plxShow->artUrl(); ?>#form" onclick="replyCom('<?php $plxShow->comIndex() ?>')"><?php $plxShow->lang('REPLY'); ?></a>
<?php
		}
?>
		</div>
<?php
	} # Fin de la boucle sur les commentaires

}

if($plxShow->articleAllowComs()) {
	# les commentaires sont autorisés
	if(!$plxShow->comMessage('<p id="com_message" class="#com_class"><strong>#com_message</strong></p>')) {
		# on affiche le formulaire pour un nouveau commentaire ou en cas d'erreur
?>
	<h3><?php $plxShow->lang('WRITE_A_COMMENT') ?></h3>
	<form id="form" action="<?php $plxShow->artUrl(); ?>#form" method="post">
		<fieldset>
<?php
		if($plxShow->articleComLoginRequired()) {
			$lostPwd = $plxShow->plxMotor->aConf['lostpassword'];
?>
			<div class="grid subscribers-notice">
				<div class="col sml-12 <?= $lostPwd ? 'med-8' : '' ?>">
					<strong><?php $plxShow->lang('COMMENT_FOR_SUBSCRIBERS') ?></strong>
				</div>
<?php
			if ($lostPwd) {
?>
				<div class="col sml-12 med-4 med-text-right">
					<a href="core/admin/auth.php?action=lostpassword"><?= L_LOST_PASSWORD ?></a>
				</div>
<?php
			}
?>
			</div>
			<div class="grid">
				<div class="col sml-12 med-6">
					<input id="id_mail" name="login" type="text" size="20" value="<?php $plxShow->comGet('login',''); ?>" placeholder="<?= L_AUTH_LOGIN_FIELD ?>" required />
				</div>
				<div class="col sml-12 med-6">
					<input id="id_site" name="password" type="password" size="20" value="" placeholder="<?= L_AUTH_PASSWORD_FIELD ?>" required />
				</div>
			</div>
<?php
		} else {
?>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_name"><?php $plxShow->lang('NAME') ?>* :</label>
					<input id="id_name" name="name" type="text" size="20" value="<?php $plxShow->comGet('name',''); ?>" maxlength="30" required="required" />
				</div>
			</div>
			<div class="grid">
				<div class="col sml-12 lrg-6">
					<label for="id_mail"><?php $plxShow->lang('EMAIL') ?> :</label>
					<input id="id_mail" name="mail" type="text" size="20" value="<?php $plxShow->comGet('mail',''); ?>" />
				</div>
				<div class="col sml-12 lrg-6">
					<label for="id_site"><?php $plxShow->lang('WEBSITE') ?> :</label>
					<input id="id_site" name="site" type="text" size="20" value="<?php $plxShow->comGet('site',''); ?>" />
				</div>
			</div>
<?php
		}
?>
			<div class="grid">
				<div class="col sml-12">
					<div id="id_answer"></div>
					<label for="id_content" class="lab_com"><?php $plxShow->lang('COMMENT') ?>* :</label>
					<textarea id="id_content" name="content" cols="35" rows="6" required="required"><?php $plxShow->comGet('content',''); ?></textarea>
				</div>
			</div>
<?php
		if($plxShow->plxMotor->aConf['capcha']) {
			# contrôle anti-spam
?>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_rep"><strong><?= $plxShow->lang('ANTISPAM_WARNING') ?></strong>*</label>
					<?php $plxShow->capchaQ(); ?>
					<input id="id_rep" name="rep" type="text" size="2" maxlength="1" style="width: auto; display: inline;" required="required" />
				</div>
			</div>
<?php
		}
?>
			<div class="grid">
				<div class="col sml-12">
					<input type="hidden" id="id_parent" name="parent" value="<?php $plxShow->comGet('parent',''); ?>" />
					<input class="blue" type="submit" value="<?php $plxShow->lang('SEND') ?>" />
				</div>
			</div>
		</fieldset>
	</form>
<?php
	} # fin affichage formulaire
?>

<script>
function replyCom(idCom) {
	document.getElementById('id_answer').innerHTML='<?php $plxShow->lang('REPLY_TO'); ?> :';
	document.getElementById('id_answer').innerHTML+=document.getElementById('com-'+idCom).innerHTML;
	document.getElementById('id_answer').innerHTML+='<a rel="nofollow" href="<?php $plxShow->artUrl(); ?>#form" onclick="cancelCom()"><?php $plxShow->lang('CANCEL'); ?></a>';
	document.getElementById('id_answer').style.display='inline-block';
	document.getElementById('id_parent').value=idCom;
	document.getElementById('id_content').focus();
}
function cancelCom() {
	document.getElementById('id_answer').style.display='none';
	document.getElementById('id_parent').value='';
	document.getElementById('com_message').innerHTML='';
}
var parent = document.getElementById('id_parent').value;
if(parent!='') { replyCom(parent) }
</script>

	<?php $plxShow->comFeed('rss',$plxShow->artId(), '<p><a href="#feedUrl" title="#feedTitle" download>#feedName</a></p>'); ?>

<?php
} else {
?>
	<p><?php $plxShow->lang('COMMENTS_CLOSED') ?>.</p>
<?php
} # Fin du if sur l'autorisation des commentaires ?>
