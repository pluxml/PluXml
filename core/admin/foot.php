<!------ Start of foot.php ----->
<?php if(!defined('PLX_ROOT')) exit; ?>

		</section>

</main>

<?php eval($plxAdmin->plxPlugins->callHook('AdminFootEndBody')) # Hook Plugins ?>

<script src="scripts/functions.js?v=<?= PLX_VERSION ?>"></script>
<script src="scripts/visual.js?v=<?= PLX_VERSION ?>"></script>
<script src="scripts/mediasManager.js?v=<?= PLX_VERSION ?>"></script>
<script src="scripts/multifiles.js?v=<?= PLX_VERSION ?>"></script>
<script src="scripts/drag-and-drop.js"></script>
<script src="scripts/medias.js"></script>

<script>
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
					alert('<?= L_FILE_REQUIRED;?>');
					event.preventDefault();
				}
			}
		}

		var targets = document.querySelectorAll(query);
		if(typeof targets === 'array' && targets.length > 0) {
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
		}
	})('select.scan-folders');
</script>

</body>

</html>
