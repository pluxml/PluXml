<?php if(!defined('PLX_ROOT')) exit; ?>

	<?php if($plxShow->plxMotor->plxRecord_coms): ?>

		<h2>
			<?php echo $plxShow->artNbCom(); ?>
		</h2>

		<?php while($plxShow->plxMotor->plxRecord_coms->loop()): # On boucle sur les commentaires ?>

		<div id="<?php $plxShow->comId(); ?>">
			<p>
				<small>
					<a href="<?php $plxShow->ComUrl(); ?>" title="#<?php echo $plxShow->plxMotor->plxRecord_coms->i+1 ?>">#<?php echo $plxShow->plxMotor->plxRecord_coms->i+1 ?></a>
					<time datetime="<?php $plxShow->comDate('#num_year(4)-#num_month-#num_day #hour:#minute'); ?>"><?php $plxShow->comDate('#day #num_day #month #num_year(4) - #hour:#minute'); ?></time> - 
					<?php $plxShow->comAuthor('link'); ?>
					<?php $plxShow->lang('SAID'); ?> :
				</small>
			</p>
			<blockquote class="comment">
				<p class="content_com type-<?php $plxShow->comType(); ?>"><?php $plxShow->comContent(); ?></p>
			</blockquote>
		</div>

		<?php endwhile; # Fin de la boucle sur les commentaires ?>

		<p><?php $plxShow->comFeed('rss',$plxShow->artId()); ?></p>

	<?php endif; ?>

	<?php if($plxShow->plxMotor->plxRecord_arts->f('allow_com') AND $plxShow->plxMotor->aConf['allow_com']): ?>

	<h2>
		<?php $plxShow->lang('WRITE_A_COMMENT') ?>
	</h2>

	<form action="<?php $plxShow->artUrl(); ?>#form" method="post">

		<fieldset>

			<div class="basic-form">
				<label for="id_name"><?php $plxShow->lang('NAME') ?> :</label>
				<input id="id_name" name="name" type="text" size="20" value="<?php $plxShow->comGet('name',''); ?>" maxlength="30" />
			</div>
			<div class="basic-form">
				<label for="id_mail"><?php $plxShow->lang('EMAIL') ?> :</label>
				<input id="id_mail" name="mail" type="text" size="20" value="<?php $plxShow->comGet('mail',''); ?>" />			
			</div>
			<div class="basic-form">
				<label for="id_site"><?php $plxShow->lang('WEBSITE') ?> :</label>
				<input id="id_site" name="site" type="text" size="20" value="<?php $plxShow->comGet('site',''); ?>" />
			</div>

			<div class="basic-form">
				<label for="id_content" class="lab_com"><?php $plxShow->lang('COMMENT') ?> :</label>
				<textarea id="id_content" name="content" cols="35" rows="6"><?php $plxShow->comGet('content',''); ?></textarea>
			</div>

			<?php $plxShow->comMessage('<p class="text-red"><strong>#com_message</strong></p>'); ?>
			
			<?php if($plxShow->plxMotor->aConf['capcha']): ?>

			<div class="inline-form">
			<label for="id_rep"><strong><?php echo $plxShow->lang('ANTISPAM_WARNING') ?></strong></label>
			<?php $plxShow->capchaQ(); ?> :
			<input id="id_rep" name="rep" type="text" size="2" maxlength="1" style="width: auto; display: inline;" />
			<?php endif; ?>
			</div>
			<input type="submit" value="<?php $plxShow->lang('SEND') ?>" />

		</fieldset>
			
	</form>

	<?php else: ?>

		<p>
			<?php $plxShow->lang('COMMENTS_CLOSED') ?>.
		</p>

	<?php endif; # Fin du if sur l'autorisation des commentaires ?>
