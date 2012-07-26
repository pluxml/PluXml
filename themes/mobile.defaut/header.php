<?php if(!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN"
"http://www.openmobilealliance.org/tech/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $plxShow->defaultLang() ?>" lang="<?php $plxShow->defaultLang() ?>">
<head>
	<title><?php $plxShow->pageTitle(); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php $plxShow->charset(); ?>" />
	<?php $plxShow->meta('description') ?>
	<?php $plxShow->meta('keywords') ?>
	<?php $plxShow->meta('author') ?>
	<meta http-equiv="Cache-control" content="no-cache" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />
	<link rel="stylesheet" type="text/css" href="<?php $plxShow->template(); ?>/style.css" media="screen" />
	<link rel="alternate" type="application/rss+xml" title="Rss articles" href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" />
	<link rel="alternate" type="application/rss+xml" title="Rss commentaires" href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" />
</head>
<body>
<div id="top">
	<div id="header">
		<h1><?php $plxShow->mainTitle('link'); ?></h1>
		<p><?php $plxShow->subTitle(); ?></p>
	</div>
</div>