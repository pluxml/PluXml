<?php
if(!class_exists('plxShow')) {
    error_log('plxShow class unknown in ' . basename(__FILE__));
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'Contact your webmaster';
    exit;
}

if(!isset($plxShow)) {
	$plxShow = plxShow::getInstance();
}

function colsClass() {
	$classList = 'col sml-12';
	if(!defined('FULL_WIDTH')) { $classList .= ' med-9'; }
	return $classList;
}

?>
<!DOCTYPE html>
<html lang="<?php $plxShow->defaultLang() ?>">
<head>
	<meta charset="<?php $plxShow->charset('min'); ?>">
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?php $plxShow->pageTitle(); ?></title>
<?php
	$plxShow->meta('description');
	$plxShow->meta('keywords');
	$plxShow->meta('author');
	plxUtils::printLinkCss();
	$plxShow->templateCss();
	$plxShow->pluginsCss();
?>
	<link rel="alternate" type="application/rss+xml" title="<?php $plxShow->lang('ARTICLES_RSS_FEEDS') ?>" href="<?php $plxShow->urlPostsRssFeed($plxShow->plxMotor->mode) ?>" />
	<link rel="alternate" type="application/rss+xml" title="<?php $plxShow->lang('COMMENTS_RSS_FEEDS') ?>" href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" />
</head>

<body id="top" class="page mode-<?php $plxShow->mode(true) ?>">

	<header class="header">

		<div class="container">

			<div class="grid">

				<div class="col sml-6 med-5 lrg-4">

					<div class="logo">
						<h1 class="no-margin heading-small"><?php $plxShow->mainTitle('link'); ?></h1>
						<h2 class="h5 no-margin"><?php $plxShow->subTitle(); ?></h2>
					</div>

				</div>

				<div class="col sml-6 med-7 lrg-8">

					<nav class="nav">

						<div class="responsive-menu">
							<label for="menu"></label>
							<input type="checkbox" id="menu">
							<ul class="menu">
								<?php $plxShow->staticList($plxShow->getLang('HOME'),'<li class="#static_class #static_status" id="#static_id"><a href="#static_url" title="#static_name">#static_name</a></li>'); ?>
								<?php $plxShow->pageBlog('<li class="#page_class #page_status" id="#page_id"><a href="#page_url" title="#page_name">#page_name</a></li>'); ?>
							</ul>
						</div>

					</nav>

				</div>

			</div>

		</div>

	</header>

	<div class="bg"></div>
