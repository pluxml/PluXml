<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
<div id="page">
	<div id="content">
		<div class="post">
			<h2 class="title"><?php $plxShow->artTitle(); ?></h2>
			<p class="post-info">
				Par <?php $plxShow->artAuthor(); ?> -  <?php $plxShow->artCat(); ?><br />
				le <?php $plxShow->artDate('#day #num_day #month #num_year(4) &agrave; #hour:#minute'); ?>
			</p>
			<p>
				<?php $plxShow->artContent(); ?>
			</p>
		</div>
		<?php include(dirname(__FILE__).'/commentaires.php'); # On insere les commentaires ?>
	</div>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>