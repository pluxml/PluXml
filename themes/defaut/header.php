<?php if(!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $plxShow->defaultLang() ?>" lang="<?php $plxShow->defaultLang() ?>">

<head>

	<title><?php $plxShow->pageTitle(); ?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=<?php $plxShow->charset(); ?>" />
	<?php $plxShow->meta('description') ?>
	<?php $plxShow->meta('keywords') ?>
	<?php $plxShow->meta('author') ?>

	<link rel="icon" href="<?php $plxShow->template(); ?>/img/favicon.png" />
	<link rel="stylesheet" type="text/css" href="<?php $plxShow->template(); ?>/style.css" media="screen" />
	<!--[if IE]>
		<link rel="stylesheet" type="text/css" href="<?php $plxShow->template(); ?>/ie.css" media="screen" />
	<![endif]-->
	<?php $plxShow->templateCss() ?>

	<link rel="alternate" type="application/rss+xml" title="<?php $plxShow->lang('ARTICLES_RSS_FEEDS') ?>" href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" />
	<link rel="alternate" type="application/rss+xml" title="<?php $plxShow->lang('COMMENTS_RSS_FEEDS') ?>" href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" />

</head>

<body id="top">

	<div id="header">

		<ul id="skip">
			<li><a href="<?php $plxShow->urlRewrite('#nav') ?>" title="<?php $plxShow->lang('GOTO_MENU') ?>"><?php $plxShow->lang('GOTO_MENU') ?></a></li>
			<li><a href="<?php $plxShow->urlRewrite('#section') ?>" title="<?php $plxShow->lang('GOTO_CONTENT') ?>"><?php $plxShow->lang('GOTO_CONTENT') ?></a></li>
		</ul>

		<h1><?php $plxShow->mainTitle('link'); ?></h1>
		<p><?php $plxShow->subTitle(); ?></p>

		<ul id="nav">
			<?php $plxShow->staticList($plxShow->getLang('HOME'),'<li id="#static_id"><a href="#static_url" class="#static_status" title="#static_name">#static_name</a></li>'); ?>
			<?php $plxShow->pageBlog('<li id="#page_id"><a class="#page_status" href="#page_url" title="#page_name">#page_name</a></li>'); ?>
			<li class="feed">
				<ul>
					<li><a href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" title="<?php $plxShow->lang('ARTICLES_RSS_FEEDS') ?>"><?php $plxShow->lang('ARTICLES') ?></a></li>
					<li><a href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" title="<?php $plxShow->lang('COMMENTS_RSS_FEEDS') ?>"><?php $plxShow->lang('COMMENTS') ?></a></li>
				</ul>
			</li>
		</ul>

	</div>
