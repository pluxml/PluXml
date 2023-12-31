<?php
if (!defined('PLX_ROOT')) {
    exit;
}

function byteConvert($bytes)
{
    if ($bytes == 0) {
        return "0.00&nbsp;";
    }

    $s = array('&nbsp;', 'K', 'M', 'G', 'T', 'P');
    $e = floor(log($bytes, 1024));

    return round($bytes / pow(1024, $e), 2) . $s[$e];
}

include 'header.php';
?>
<!-- begin of static-download.php -->
            <article class="static article" id="static-page-<?= $plxShow->staticId(); ?>">
                <header class="static-header">
                    <h2><?php $plxShow->staticTitle(); ?></h2>
                </header>
<?php
// On capture le contenu de la page statique
ob_start();
$plxShow->staticContent();
$output = ob_get_clean();

// On vérifie que ce contenu matches avec le motif ci-dessous
$pattern = '#<div[^>]*\s+data-download="([^"]+)".*?>#';
if (preg_match($pattern, $output, $matches)) {
    $root = PLX_ROOT . $plxMotor->aConf['medias'];
    $dir1 = $root . rtrim($matches[1], '/');
    if (is_dir($dir1)) {
        $files = glob($dir1 . '/*');
        if (!empty($files)) {
            $start = strlen($root);
            $description = array();
            $htaccess = $dir1 . '/.htaccess';
            if (file_exists($htaccess)) {
                foreach (array_map('trim', file($htaccess)) as $line) {
                    if (preg_match('#^AddDescription\s+"([^"]+)"\s+([\w-]+\.\w+)$#i', $line, $matches)) {
                        $description[$matches[2]] = trim($matches[1]);
                    }
                }
            }

            // On capture le tableau des fichiers
            ob_start(); ?>
                <div class="scrollable-table">
                    <table>
                        <thead>
                            <tr class="color1">
                                <th>&nbsp;</th>
                                <th><?php $plxShow->lang('FILENAME'); ?></th>
                                <th><?php $plxShow->lang('FILEDATE'); ?></th>
                                <th><?php $plxShow->lang('FILESIZE'); ?></th>
                                <th><?php $plxShow->lang('FILEDESCRIPTION'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
            <?php
            foreach ($files as $filename) {
                $href = $plxMotor->urlRewrite('index.php?download/' . plxEncrypt::encryptId('/' . substr($filename, $start)));
                $f = basename($filename);
                $descr = isset($description[$f]) ? $description[$f] : ''; ?>
                            <tr>
                                <td class="<?= pathinfo($filename, PATHINFO_EXTENSION) ?>">&nbsp;</td>
                                <td><a href="<?= $href ?>" download="<?= basename($filename) ?>"><?= basename($filename) ?></a></td>
                                <td><?= date('Y-m-d H:i', filemtime($filename)) ?></td>
                                <td><?= byteConvert(filesize($filename)) ?></td>
                                <td><?= $descr ?></td>
                            </tr>
                <?php
            } ?>
                        </tbody>
                    </table>
                </div>
            <?php
            echo preg_replace($pattern, '$0' . ob_get_clean(), $output);
        } else {
            echo preg_replace($pattern, '$0' . $plxShow->getLang('NOTHING_FOR_DOWNLOADING'), $output);
        }
    } else {
        echo preg_replace($pattern, '$0' . $plxShow->getLang('NO_DIR') . preg_replace('#^' . PLX_ROOT . '#', ' :<br />', $dir1), $output);
    }
} else {
    echo $output;
}
?>
            </article>
<!-- end of static-download.php -->

<?php
include 'footer.php';
