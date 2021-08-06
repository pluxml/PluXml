<? include __DIR__ . '/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="grid">
                <div class="content col sml-12 med-9">

                    <ul class="repertory menu breadcrumb">
                        <li><a href="<?= $plxShow->racine() ?>"><?= $plxShow->lang('HOME'); ?></a></li>
                        <li>Search</li>
                    </ul>

                    <form action="<?= $plxShow->urlRewrite('?search') ?>" method="post">
                        <input type="text" name="search" value="<?= $_POST['search'] ?>"/>
                    </form>

                    <? if (empty($plxShow->searchPagesResults())): ?>
                        <p>Not found</p>
                    <? else: ?>
                        <? if (!empty($plxShow->searchPagesResults())): ?>
                            <p>Pages&nbsp;(<?= count($plxShow->searchPagesResults()) ?>)&nbsp;:</p>
                            <ul>
                                <? foreach ($plxShow->searchPagesResults() as $page): ?>
                                    <li><a href="<?= $page["url"] ?>"><?= $page["title"] ?></a></li>
                                <? endforeach; ?>
                            </ul>
                        <? endif; ?>
                    <? endif; ?>

                    <?php while ($plxShow->plxMotor->plxRecord_arts->loop()): ?>

                        <p>Articles&nbsp;(<?= $plxShow->plxMotor->plxRecord_arts->size ?>)&nbsp;:</p>
                        <article class="article" id="post-<?= $plxShow->artId(); ?>">

                            <header>
                            <span class="art-date">
                                <time datetime="<?php $plxShow->artDate('#num_year(4)-#num_month-#num_day'); ?>">
                                    <?php $plxShow->artDate('#num_day #month #num_year(4)'); ?>
                                </time>
                            </span>
                                <h2>
                                    <?php $plxShow->artTitle('link'); ?>
                                </h2>
                                <div>
                                    <small>
                                        <span class="written-by">
                                            <?php $plxShow->lang('WRITTEN_BY'); ?><?php $plxShow->artAuthor() ?>
                                        </span>
                                        <span class="art-nb-com">
                                            <?php $plxShow->artNbCom(); ?>
                                        </span>
                                    </small>
                                </div>
                                <div>
                                    <small>
                                        <span class="classified-in">
                                            <?php $plxShow->lang('CLASSIFIED_IN') ?> : <?php $plxShow->artCat() ?>
                                        </span>
                                        <span class="tags">
                                            <?php $plxShow->lang('TAGS') ?> : <?php $plxShow->artTags() ?>
                                        </span>
                                    </small>
                                </div>
                            </header>

                            <?php $plxShow->artThumbnail(); ?>
                            <?php $plxShow->artChapo(); ?>

                        </article>

                    <?php endwhile; ?>
                </div>
                <? include __DIR__ . '/sidebar.php'; ?>
            </div>
        </div>
    </main>

<? include __DIR__ . '/footer.php'; ?>