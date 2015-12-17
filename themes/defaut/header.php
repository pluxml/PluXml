<?php if (!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html>
<html lang="<?php $plxShow->defaultLang() ?>">
<head>
	<meta charset="<?php $plxShow->charset('min'); ?>">
	<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0">
	<title><?php $plxShow->pageTitle(); ?></title>
	<?php $plxShow->meta('description') ?>
	<?php $plxShow->meta('keywords') ?>
	<?php $plxShow->meta('author') ?>
	<link rel="icon" href="<?php $plxShow->template(); ?>/img/favicon.png" />
	<link rel="stylesheet" href="<?php $plxShow->template(); ?>/css/plucss.css" media="screen"/>
	<link rel="stylesheet" href="<?php $plxShow->template(); ?>/css/theme.css" media="screen"/>
	<?php $plxShow->templateCss() ?>
	<?php $plxShow->pluginsCss() ?>
	<link rel="alternate" type="application/rss+xml" title="<?php $plxShow->lang('ARTICLES_RSS_FEEDS') ?>" href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" />
	<link rel="alternate" type="application/rss+xml" title="<?php $plxShow->lang('COMMENTS_RSS_FEEDS') ?>" href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" />
</head>

<body id="top">

<div class="container">

	<header class="header sml-text-center med-text-left" role="banner">
		<h1 class="no-margin"><?php $plxShow->mainTitle('link'); ?></h1>
		<h2 class="h5 no-margin"><?php $plxShow->subTitle(); ?></h2>
	</header>

	<nav class="nav" role="navigation">
		<div class="responsive-menu">
			<label for="menu"><?php $plxShow->lang('MENU'); ?></label>
			<input type="checkbox" id="menu">
			<ul class="menu expanded">
				<?php $plxShow->staticList($plxShow->getLang('HOME'),'<li class="#static_status" id="#static_id"><a href="#static_url" title="#static_name">#static_name</a></li>'); ?>
				<?php $plxShow->pageBlog('<li id="#page_id"><a class="#page_status" href="#page_url" title="#page_name">#page_name</a></li>'); ?>
			</ul>
		</div>
	</nav>
