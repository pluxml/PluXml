<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
<div id="page">
	<div id="content">
		<?php while($plxShow->plxMotor->plxRecord_arts->loop()): # On boucle sur les articles ?>
			<div class="post">
				<h2 class="title"><?php $plxShow->artTitle('link'); ?></h2>
				<p class="post-content">
					<p><?php $plxShow->artChapo("Lire : #art_title", true, 150); ?></p>
				</p>
				<p class="post-info">
					Class&eacute; dans : <?php $plxShow->artCat(); ?><br />
					<?php $plxShow->artDate('#num_day/#num_month/#num_year(2)'); ?> - <?php $plxShow->artNbCom(); ?>
				</p>
				<div class="clearer"></div>
			</div>
		<?php endwhile; # Fin de la boucle sur les articles ?>
		<?php # On affiche le fil Rss de cet article ?>
		<div class="feeds"><?php $plxShow->artFeed('rss',$plxShow->catId()); ?></div>
		<?php # On affiche la pagination ?>
		<p id="pagination"><?php $plxShow->pagination(); ?></p>
	</div>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>