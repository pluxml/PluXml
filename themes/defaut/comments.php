<?php if(!defined('PLX_ROOT')) {
	exit;
}

if($plxShow->plxMotor->plxRecord_coms) {
	# On a des commentaires pour cet article
?>
		<h2 id="comments">
			<span><?= $plxShow->artNbCom('L_NO_COMMENT', '#nb L_COMMENT', '#nb L_COMMENTS', false); ?></span>&nbsp;
			<?php $plxShow->comFeed('rss',$plxShow->artId(), '<a class="rss" href="#feedUrl" title="#feedTitle"></a>' . PHP_EOL); ?>
		</h2>
		<div id="comments-container">
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
		if($plxShow->articleAllowComs()) {
?>
				<a rel="nofollow" href="#form"><?php $plxShow->lang('REPLY'); ?></a>
<?php
		}
?>
			</div>
<?php
	} # Fin de la boucle sur les commentaires
?>
		</div>
<?php
}

if(!$plxShow->articleAllowComs(true)) {
	# Les commentaires sont interdits ou fermés
?>
	<p><?php $plxShow->lang('COMMENTS_CLOSED') ?>.</p>
<?php
} else {
	# les commentaires sont autorisés
	$plxShow->comMessage('<p id="com_message" class="#com_class"><strong>#com_message</strong></p>');

	# on affiche le formulaire pour un nouveau commentaire ou en cas d'erreur

	$forSubScribersOnly = $plxShow->articleComLoginRequired();
	if($forSubScribersOnly and !isset($_SESSION['user'])) {
?>
	<div class="grid subscribers-notice">
		<div class="col sml-12 med-8">
			<strong><?php $plxShow->lang('COMMENT_FOR_SUBSCRIBERS') ?></strong>
		</div>
		<div class="col sml-12 med-4">
			<a class="button" href="<?php $plxShow->urlRewrite('core/admin/auth.php'); ?>?p=<?= urlencode($_SERVER['REQUEST_URI']) ?>"><?php $plxShow->lang('CONNEXION'); ?></a>
		</div>
	</div>
<?php
	} elseif(!$forSubScribersOnly or isset($_SESSION['user'])) {
			# affichage formulaire
?>
	<form id="comment-form" action="<?php $plxShow->artUrl(); ?>#form" method="post">
		<h2><?php $plxShow->lang('WRITE_A_COMMENT') ?></h2>
		<fieldset>
<?php
		if(isset($_SESSION['user'])) {
			# for subscribers
?>
			<div class="grid subscribers-notice">
				<div class="col sml-12 med-8">
					<span><?php $plxShow->lang('SUBSCRIBER_CONNECTED') ?></span> : <strong><?= $plxMotor->aUsers[$_SESSION['user']]['name'] ?></strong>
				</div>
				<div class="col sml-12 med-4 med-text-right">
					<a class="button" href="core/admin/auth.php?d=1"><?php $plxShow->lang('DECONNEXION'); ?></a>
				</div>
			</div>
<?php
		} else {
			# for everybody
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
					<input id="id_mail" name="mail" type="email" size="20" value="<?php $plxShow->comGet('mail',''); ?>" />
				</div>
				<div class="col sml-12 lrg-6">
					<label for="id_site"><?php $plxShow->lang('WEBSITE') ?> :</label>
					<input id="id_site" name="site" type="url" size="20" value="<?php $plxShow->comGet('site',''); ?>" />
				</div>
			</div>
<?php
		}
?>
			<div class="grid">
				<div class="col sml-12">
					<div id="id_answer"></div>
					<label for="id_content" class="lab_com"><?php $plxShow->lang('COMMENT') ?>* :</label>
					<textarea id="id_content" name="content" cols="35" rows="6" required><?php $plxShow->comGet('content',''); ?></textarea>
				</div>
			</div>
<?php
		if($plxShow->plxMotor->aConf['capcha']) {
			# contrôle anti-spam
?>
			<div class="grid">
				<div class="col sml-12">
					<label for="id_rep"><strong><?= $plxShow->lang('ANTISPAM_WARNING') ?></strong>*</label>
				</div>
				<div class="col sml-12 med-10">
					<?php $plxShow->capchaQ(); ?>
				</div>
				<div class="col sml-12 med-2 med-text-right">
					<input id="id_rep" name="rep" type="text" size="2" maxlength="1" style="width: auto; display: inline;" required />
				</div>
			</div>
<?php
		}
?>
			<div class="grid">
				<input type="hidden" id="id_parent" name="parent" value="<?php $plxShow->comGet('parent',''); ?>" />
				<div class="col sml-6">
					<button id="resetBtn" type="reset" class="blue"><?php $plxShow->lang('CANCEL'); ?></button>
				</div>
				<div class="col sml-6 text-right">
					<button type="submit" class="blue"><?php $plxShow->lang('SEND') ?></button>
				</div>
			</div>
		</fieldset>
	</form>
	<script>
	(function() {
		const formComment = document.getElementById('comment-form');
		if(formComment) {
			const container = document.getElementById('comments-container');
			if(container) {
				container.addEventListener('click', function(ev) {
					if(ev.target.hasAttribute('href') && ev.target.href.endsWith('#form')) {
						ev.preventDefault();
						formComment.remove();
						ev.target.parentElement.appendChild(formComment);
						formComment.parent.value = ev.target.parentElement.id.replace(/^c/, '');
						container.classList.add('response');
					}
				});

				const resetBtn = document.getElementById('resetBtn');
				if(resetBtn) {
					resetBtn.addEventListener('click', function(ev) {
						ev.target.form.elements.parent.value = '';
						container.classList.remove('response');
						formComment.remove();
						container.after(formComment);
					});
				}
			}
		}
	})();
	</script>
<?php
	}
} # Fin commentaires autorisés
