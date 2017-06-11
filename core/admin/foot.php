<?php if(!defined('PLX_ROOT')) exit; ?>

		</section>

</main>

<?php eval($plxAdmin->plxPlugins->callHook('AdminFootEndBody')) ?>

<script type="text/javascript">
	setMsg();
	mediasManager.construct({
		windowName : "<?php echo L_MEDIAS_TITLE ?>",
		racine:	"<?php echo plxUtils::getRacine() ?>",
		urlManager: "core/admin/medias.php"
	});

	(function(query) {

		'use strict';

		function selectChangeEvt(event) {
			var option = this.options[this.selectedIndex];
			if(option.classList.contains('folder') && option.hasAttribute('data-level')) {
				var level = option.getAttribute('data-level') + 'X';
				var visibles = this.querySelectorAll('.visible[data-level^="' + level + '"]');
				visibles.forEach(function(item) {
					item.classList.remove('visible');
				});
				var mySibling = option.nextElementSibling;
				while((mySibling != null) && (mySibling.getAttribute('data-level') == level)) {
					mySibling.classList.add('visible');
					mySibling = mySibling.nextElementSibling;
				}
				event.preventDefault();
			}
		}

		function checkFileOnly(event) {
			if(this.filesSelect != undefined) {
				// interdire la sélection des dossiers
				var option = this.filesSelect.options[this.filesSelect.selectedIndex];
				if(option.value.length == 0 || option.hasAttribute('data-folder')) {
					alert("<?php echo L_FILE_REQUIRED;?>");
					event.preventDefault();
				}
			}
		}

		var targets = document.querySelectorAll(query)
		targets.forEach(function(element) {
			if(element.options.length > 30) { <!-- Nombre minimum d'entrées de dossiers ou fichiers pour cacher l'arborescence des dossiers
				element.addEventListener('change', selectChangeEvt);
			} else {
				// Pas assez de fichier, on déplie tout (unfold)
				element.classList.remove('fold');
			}
			if(element.classList.contains('data-files')) {
				element.form.filesSelect = element;
				element.form.addEventListener('submit', checkFileOnly)
			}
		});
	})('select.scan-folders');
</script>

</body>

</html>