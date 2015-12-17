<?php if(!defined('PLX_ROOT')) exit; ?>

		</section>

</main>

<?php eval($plxAdmin->plxPlugins->callHook('AdminFootEndBody')) ?>

<script>
	setMsg();
	mediasManager.construct({
		windowName : "<?php echo L_MEDIAS_TITLE ?>",
		racine:	"<?php echo plxUtils::getRacine() ?>",
		root: "<?php echo PLX_ROOT ?>",
		urlManager: "<?php echo PLX_CORE ?>admin/medias.php",
	});
</script>

</body>

</html>
