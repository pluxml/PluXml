<?php include __DIR__ . '/header.php'; ?>

<main class="main">

    <div class="container">

        <div class="grid">

            <div class="content col sml-12 med-9">

                <article class="article static" id="static-page-<?php echo $plxShow->staticId(); ?>">

                    <header>
                        <h2>
                            <?php $plxShow->staticTitle(); ?>
                        </h2>
                    </header>

                    <?php $plxShow->staticContent(); ?>

                </article>

            </div>

            <?php include __DIR__ . '/sidebar.php'; ?>

        </div>

    </div>

</main>

<?php include __DIR__ . '/footer.php'; ?>
