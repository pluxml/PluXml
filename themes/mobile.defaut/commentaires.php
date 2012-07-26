<?php if(!defined('PLX_ROOT')) exit; ?>
<?php # Si on a des commentaires ?>
<?php if($plxShow->plxMotor->plxRecord_coms): ?>
	<div id="comments">
		<h2>Commentaires</h2>
		<?php while($plxShow->plxMotor->plxRecord_coms->loop()): # On boucle sur les commentaires ?>
			<div id="<?php $plxShow->comId(); ?>" class="comment type-<?php $plxShow->comType(); ?>">
				<div class="info_comment">
					<p>Par <?php $plxShow->comAuthor('link'); ?>
					le <?php $plxShow->comDate('#num_day/#num_month/#num_year(2) &agrave; #hour:#minute'); ?></p>
				</div>
				<blockquote><p><?php $plxShow->comContent() ?></p></blockquote>
			</div>
		<?php endwhile; # Fin de la boucle sur les commentaires ?>
		<?php # On affiche le fil Rss de cet article ?>
		<div class="feeds"><?php $plxShow->comFeed('rss',$plxShow->artId()); ?></div>
	</div>
<?php endif; # Fin du if sur la prescence des commentaires ?>
<?php # Si on autorise les commentaires ?>
<?php if($plxShow->plxMotor->plxRecord_arts->f('allow_com') AND $plxShow->plxMotor->aConf['allow_com']): ?>
	<div id="form">
		<h2>Ecrire un commentaire</h2>
		<p class="message_com"><?php $plxShow->comMessage(); ?></p>
		<form action="<?php $plxShow->artUrl(); ?>#form" method="post">
			<fieldset>
				<label for="id_name">Nom&nbsp;:</label>
				<input id="id_name" name="name" type="text" size="30" value="<?php $plxShow->comGet('name',''); ?>" maxlength="30" /><br />
				<label for="id_site">Site (facultatif)&nbsp;:</label>
				<input id="id_site" name="site" type="text" size="30" value="<?php $plxShow->comGet('site','http://'); ?>" /><br />
				<label for="id_mail">E-mail (facultatif)&nbsp;:</label>
				<input id="id_mail" name="mail" type="text" size="30" value="<?php $plxShow->comGet('mail',''); ?>" /><br />
				<label for="id_content">Commentaire&nbsp;:</label>
				<textarea id="id_content" name="content" cols="35" rows="8"><?php $plxShow->comGet('content',''); ?></textarea>
				<?php # Affichage du capcha anti-spam
				if($plxShow->plxMotor->aConf['capcha']): ?>
					<label for="id_rep"><strong>V&eacute;rification anti-spam</strong>&nbsp;:</label>
					<p><?php $plxShow->capchaQ(); ?>&nbsp;:&nbsp;<input id="id_rep" name="rep" type="text" size="10" /></p>
				<?php endif; # Fin du if sur le capcha anti-spam ?>
				<p><input type="submit" value="Envoyer" />&nbsp;<input type="reset" value="Effacer" /></p>
			</fieldset>
		</form>
	</div>
<?php endif; # Fin du if sur l'autorisation des commentaires ?>