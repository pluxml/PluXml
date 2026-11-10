<?php
if(!isset($plxAdmin)) {
	redirectLogin();
}

$i18n = array(
	'delete' 	=> L_CONFIRM_DELETE,
	'delete2' 	=> L_CONFIRM_DELETE_2,
	'noChecked'	=> L_NO_CHECKED,
	'noSelection' => L_NO_SELECTION,
);
?>
		</section>

</main>

<?php eval($plxAdmin->plxPlugins->callHook('AdminFootEndBody')) # Hook Plugins ?>

<script src="js/visual.js?v=<?= PLX_VERSION ?>"></script>
<script src="js/drag-and-drop.js"></script>
<script>
	setMsg();
<?php
if(preg_match('#/(?:medias|article|categorie|statique)\.php$#', $_SERVER['SCRIPT_NAME'])) {
?>
	mediasManager.construct({
		windowName : "<?= L_MEDIAS_TITLE ?>",
		racine:	'<?= $plxAdmin->racine ?>',
		urlManager: '<?= preg_replace('#(\w[\w-]+\.php)$#', 'medias.php', $_SERVER['SCRIPT_NAME']) ?>',
	});
<?php
}
?>

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
					alert("<?= L_FILE_REQUIRED;?>");
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

		const selectionBtn = document.querySelector('input[name="selectionBtn"][data-selection]');
		const i18n = JSON.parse('<?= json_encode($i18n) ?>');
		if(selectionBtn && selectionBtn.dataset.selection.length > 0) {
			const selector = selectionBtn.form.elements.selection;
			selectionBtn.addEventListener('click', function(ev) {
				const chkName = ev.target.dataset.selection; // nae of the checkboxes
				const checkBoxes = ev.target.form.querySelectorAll('[name="' + chkName + '"]:checked');
				const count = checkBoxes.length;
				if(count == 0) {
					alert(i18n.noChecked);
				} else {
					// Au moins un élemént sélectionné
					if(selector.value.length == 0) {
						alert(i18n.noSelection);
					} else {
						if(selector.value == 'delete') {
							if(confirm((count == 1) ? i18n.delete : i18n.delete2.replace(/\b2\b/, count))) {
								return;
							}
							Array.from(checkBoxes).forEach(function(el) {
								el.checked = false;
							})
						} else {
							console.error('unknown action: ' + selector.value);
							return;
						}
					}
				}

				ev.preventDefault();
				return;
			});
		}

	})('select.scan-folders');
</script>

</body>

</html>
