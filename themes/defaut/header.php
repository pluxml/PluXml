<?php if (!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html>
<html lang="<?php $plxShow->defaultLang() ?>">
<head>
<meta charset="<?php $plxShow->charset('min'); ?>">
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
<title><?php $plxShow->pageTitle(); ?></title>
<?php $plxShow->meta('description') ?>
<?php $plxShow->meta('keywords') ?>
<?php $plxShow->meta('author') ?>
<?php $plxShow->templateCss() ?>
<link rel="icon" href="<?php $plxShow->template(); ?>/img/favicon.png" />
<link rel="stylesheet" href="<?php $plxShow->template(); ?>/style.css" media="screen"/>
<link rel="alternate" type="application/rss+xml" title="<?php $plxShow->lang('ARTICLES_RSS_FEEDS') ?>" href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" />
<link rel="alternate" type="application/rss+xml" title="<?php $plxShow->lang('COMMENTS_RSS_FEEDS') ?>" href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" />
<!--[if lt IE 9]>
<script src="<?php $plxShow->template(); ?>/js/html5ie.js"></script>
<script src="<?php $plxShow->template(); ?>/js/respond.min.js"></script>
<![endif]-->
</head>

<body id="top">

	<header role="banner">

		<div class="content">

			<h1>
				<?php $plxShow->mainTitle('link'); ?>
			</h1>
			<h2>
				<?php $plxShow->subTitle(); ?>
			</h2>

		</div>

	</header>

	<nav role="navigation">

		<div class="content">

			<ul>
				<?php $plxShow->staticList($plxShow->getLang('HOME'),'<li id="#static_id"><a href="#static_url" class="#static_status" title="#static_name">#static_name</a></li>'); ?>
				<?php $plxShow->pageBlog('<li id="#page_id"><a class="#page_status" href="#page_url" title="#page_name">#page_name</a></li>'); ?>
			</ul>

		</div>

	</nav>
