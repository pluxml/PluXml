<?php if(!defined('PLX_ROOT')) exit; ?>

	<?php if($plxShow->plxMotor->plxRecord_coms): ?>
		<div id="comments">
			<h2><?php echo $plxShow->artNbCom() ?></h2>
			<?php while($plxShow->plxMotor->plxRecord_coms->loop()): # On boucle sur les commentaires ?>
				<div id="<?php $plxShow->comId(); ?>" class="comment">
					<blockquote>
						<p class="info_comment"><?php $plxShow->comDate('#day #num_day #month #num_year(4) &#64; #hour:#minute'); ?> <?php $plxShow->comAuthor('link'); ?> <?php $plxShow->lang('SAID') ?> : <a class="num-com" href="<?php $plxShow->ComUrl() ?>" title="#<?php echo $plxShow->plxMotor->plxRecord_coms->i+1 ?>">#<?php echo $plxShow->plxMotor->plxRecord_coms->i+1 ?></a></p>
						<p class="content_com type-<?php $plxShow->comType(); ?>"><?php $plxShow->comContent() ?></p>
					</blockquote>
				</div>
			<?php endwhile; # Fin de la boucle sur les commentaires ?>

			<div class="feed-art-com"><?php $plxShow->comFeed('rss',$plxShow->artId()); ?></div>
		</div>
	<?php endif; ?>

	<?php if($plxShow->plxMotor->plxRecord_arts->f('allow_com') AND $plxShow->plxMotor->aConf['allow_com']): ?>
		<div id="form">
			<h2><?php $plxShow->lang('WRITE_A_COMMENT') ?></h2>
			<form action="<?php $plxShow->artUrl(); ?>#form" method="post">
				<fieldset>
					<label for="id_name"><?php $plxShow->lang('NAME') ?>&nbsp;:</label>
					<input id="id_name" name="name" type="text" size="20" value="<?php $plxShow->comGet('name',''); ?>" maxlength="30" />
					<label for="id_site"><?php $plxShow->lang('WEBSITE') ?>&nbsp;:</label>
					<input id="id_site" name="site" type="text" size="20" value="<?php $plxShow->comGet('site',''); ?>" />
					<label for="id_mail"><?php $plxShow->lang('EMAIL') ?>&nbsp;:</label>
					<input id="id_mail" name="mail" type="text" size="20" value="<?php $plxShow->comGet('mail',''); ?>" />

					<label for="id_content" class="lab_com"><?php $plxShow->lang('COMMENT') ?>&nbsp;:</label>
					<textarea id="id_content" name="content" cols="35" rows="6"><?php $plxShow->comGet('content',''); ?></textarea>


					<?php if($plxShow->plxMotor->aConf['capcha']): ?>
					<label for="id_rep"><strong><?php echo $plxShow->lang('ANTISPAM_WARNING') ?></strong>&nbsp;:</label>
					<p><?php $plxShow->capchaQ(); ?>&nbsp;:&nbsp;<input id="id_rep" name="rep" type="text" size="10" /></p>
					<input name="rep2" type="hidden" value="<?php $plxShow->capchaR(); ?>" />
					<?php endif; ?>

					<input type="submit" value="<?php $plxShow->lang('SEND') ?>" /><p class="com-alert"><?php $plxShow->comMessage(); ?></p>
				</fieldset>
			</form>
		</div>
	<?php else: ?>
		<p><?php $plxShow->lang('COMMENTS_CLOSED') ?>.</p>
	<?php endif; # Fin du if sur l'autorisation des commentaires ?>
