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

		function myFunction(event) {
			var option = this.options[this.selectedIndex];
			if(option.hasAttribute('data-level')) {
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

		var targets = document.querySelectorAll(query)
		targets.forEach(function(element) {
			element.addEventListener('change', myFunction);
		});
	})('.scan-folders');
</script>

</body>

</html>