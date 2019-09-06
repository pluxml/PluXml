<!DOCTYPE html>
<head>
	<meta name="robots" content="noindex, nofollow" />
	<meta charset="<?= strtolower($charset) ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?php echo L_UPDATE_TITLE.' '.$plxUtils->strCheck($plxUpdater->newVersion) ?></title>
	<link rel="stylesheet" type="text/css" href="<?= $plxLayoutDir.'css/plucss.css'?>" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?= $plxLayoutDir.'css/theme.css'?>" media="screen" />
	<link rel="icon" href="<?php echo PLX_CORE ?>admin/theme/images/pluxml.gif" />
</head>

<body>

	<main class="main grid">

		<aside class="aside col sml-12 med-3 lrg-2">

		</aside>

		<section class="section col sml-12 med-9 med-offset-3 lrg-10 lrg-offset-2" style="margin-top: 0">

			<header>

				<h1><?php echo L_UPDATE_TITLE.' '.$plxUtils->strCheck($plxUpdater->newVersion) ?></h1>

			</header>

			<?php if(empty($_POST['submit'])) : ?>
				<?php if($plxUpdater->oldVersion==$plxUpdater->newVersion) : ?>
				<p><strong><?php echo L_UPDATE_UPTODATE ?></strong></p>
				<p><?php echo L_UPDATE_NOT_AVAILABLE ?></p>
				<p><a href="<?php echo PLX_ROOT; ?>" title="<?php echo L_UPDATE_BACK ?>"><?php echo L_UPDATE_BACK ?></a></p>
				<?php else: ?>
				<form action="index.php" method="post">
					<fieldset>
						<div class="grid">
							<div class="col sml-12 med-5 label-centered">
								<label for="id_default_lang"><?php echo L_SELECT_LANG ?></label>
							</div>
							<div class="col sml-12 med-7">
								<?php $plxUtils->printSelect('default_lang', $plxUtils->getLangs(), $lang) ?>&nbsp;
							</div>
						</div>
						<div class="grid">
							<div class="col sml-12">
								<input type="submit" name="select_lang" value="<?php echo L_INPUT_CHANGE ?>" />
								<?php echo $plxToken->getTokenPostMethod() ?>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<p><strong><?php echo L_UPDATE_WARNING1.' '.$plxUpdater->oldVersion ?></strong></p>
						<?php if(empty($plxUpdater->oldVersion)) : ?>
						<p><?php echo L_UPDATE_SELECT_VERSION ?></p>
						<p><?php $plxUtils->printSelect('version',array_keys($versions),''); ?></p>
						<p><?php echo L_UPDATE_WARNING2 ?></p>
						<?php endif; ?>
						<p><?php echo L_UPDATE_WARNING3 ?></p>
						<p><input type="submit" name="submit" value="<?php echo L_UPDATE_START ?>" /></p>
					</fieldset>
				</form>
				<?php endif; ?>
			<?php else: ?>
			<?php
			$version = isset($_POST['version']) ? $_POST['version'] : $plxUpdater->oldVersion;
			$plxUpdater->startUpdate($version);
			?>
			<p><a href="<?php echo PLX_ROOT; ?>" title="<?php echo L_UPDATE_BACK ?>"><?php echo L_UPDATE_BACK ?></a></p>
			<?php endif; ?>
		</section>

	</main>

</body>

</html>