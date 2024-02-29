<?php include 'header.php'; ?>
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="<?= $contentClass ?>">
<?php include 'posts.php'; ?>
				</div>
<?php
if (!defined('FULL_WIDTH')) {
	include 'sidebar.php';
}
?>
			</div>
		</div>
	</main>
<?php
include 'footer.php';
