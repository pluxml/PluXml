<?php if(!defined('PLX_ROOT')) exit; ?>

		</section>

</main>

<?php eval($plxAdmin->plxPlugins->callHook('AdminFootEndBody')) # Hook Plugins ?>

<script src="<?php echo PLX_CORE ?>lib/drag-and-drop.js"></script>
<script>
	setMsg();
	mediasManager.construct({
		windowName : "<?php echo L_MEDIAS_TITLE ?>",
		racine:	"<?php echo plxUtils::getRacine() ?>",
		urlManager: "core/admin/medias.php"
	});
</script>

</body>

</html>