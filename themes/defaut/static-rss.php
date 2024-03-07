<?php
if (!defined('PLX_ROOT')) {
	exit;
}

include 'header.php';
const DEBUG_RSS = false;
?>
	<main class="main">
		<div class="container">
			<div class="grid">
				<div class="<?= $contentClass ?>">
					<article class="static" id="static-page-<?php echo $plxShow->staticId(); ?>">
						<header>
							<h2><?php $plxShow->staticTitle(); ?></h2>
						</header>
<?php
/*
 * http://test.lan/PluXml-5.8.4/feed.php?rss
 * http://test.lan/PluXml-5.8.4/feed/rss/categorie1/rubrique-1
 * http://test.lan/PluXml-5.8.4/feed/rss/tag/soleil
 * https://www.krocui.com/feed/rss
 *
 * https://www.huffingtonpost.fr/feeds/index.xml
 *
 * enclosure avec contenu audio
 * https://www.arteradio.com/xml_sound_emission?emissionname=%22FEN%C3%8ATRE%20SUR%20COUR%22
 * https://podcast.bfmbusiness.com/channel295/BFMchannel295.xml
 * http://radiofrance-podcast.net/podcast09/rss_14497.xml
 * https://podcast.rmc.fr/channel30/RMCInfochannel30.xml
 *
 * enclosure avec image :
 * https://www.france24.com/fr/france/rss
 * */
ob_start();
$plxShow->staticContent();
$content = ob_get_clean();

$pattern = '@data-rss="([^"]+)"[^>]*?>@';
if (preg_match($pattern, $content, $matches)) {
	$ch = curl_init($matches[1]);
	curl_setopt_array($ch, array(
		CURLOPT_RETURNTRANSFER  => true,
		CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0',
		CURLOPT_HTTPHEADER      => array(
			'Accept'            => 'application/xhtml+xml,application/xml;q=0.9,*/*;q=0.5',
			'DNT'               => '1',
			'Accept-Language'   => 'fr-FR,fr;q=0.8,en-US;q=0.5,en;q=0.3',
		),
		CURLOPT_FOLLOWLOCATION  => true,
	));
	$buffer = curl_exec($ch);
	$status = curl_getinfo($ch);
	curl_close($ch);
	if (DEBUG_RSS) {
		echo '<!--' . PHP_EOL;
		print_r($status);
		echo '-->' . PHP_EOL;
	}
	if ($buffer !== false && $status['http_code'] == 200) {
		unset($status);
		$obj = simplexml_load_string($buffer); ?>
<header class="static-header">
	<h3><a href="<?= $obj->channel->link->__toString(); ?>"><?= $obj->channel->title->__toString() ?></a></h3>
	<p><?= $obj->channel->description->__toString(); ?></p>
</header>
<section>
<?php
		foreach ($obj->channel->item as $item) {
			$dt = new DateTime($item->pubDate->__toString());
?>
	<article>
		<h4><?= $item->title->__toString() ?></h4>
		<p>Publié le <?= preg_replace_callback('@^(\d)@', function ($t) {
				return plxDate::getCalendar('day', $t[1]);
					 }, $dt->format('w d/m/Y à G\hi')) ?></p>
		<div>
			<?= $item->description->__toString() ?>
		</div>
		<p><a href="<?= $item->link->__toString() ?>" target="_blank">Lire la suite</a></p>
<?php
			if ($item->enclosure) {
				if (strpos($item->enclosure['type'], 'audio/') === 0) {
?>
		<p><audio src="<?= $item->enclosure['url'] ?>" controls preload="none"><a href="$item->enclosure['url']" target="_blank">Ecouter</a></p>
<?php
				} elseif (strpos($item->enclosure['type'], 'image/') === 0) {
?>
		<p><img src="<?= $item->enclosure['url'] ?>" /></p>
<?php
				}
			}
?>
	</article>
<?php
		}
	} else {
?>
			<div>
				<p>Error <?= $status['http_code'] ?></p>
				<p><em><?= $status['url'] ?></em>)</p>
				<p>Content-Type : <?= $status['content_type'] ?></p>
				<pre><?php /*  print_r($status); */ ?></pre>
			</div>
<?php
	}
?>
</section>
	<?php
} else {
?>
						<p class="alert"><?php $plxShow->lang('RSS_INFO'); ?></p>
						<pre><code>&lt;div data-rss="<?php $plxShow->lang('RSS_FOLDER'); ?>"&gt;&lt;/div&gt;</code></pre>
<?php
	echo $content;
}
?>
					</article>
				</div>
<?php
if(!defined('FULL_WIDTH')) {
	include 'sidebar.php';
}
?>
			</div>
		</div>
	</main>
<?php
include 'footer.php';
