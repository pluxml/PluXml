<?php

class PlxSearch
{
    private $_articlesResults = [];
    private $_pagesResults = [];

    private $_format_date = '#num_day/#num_month/#num_year(4)';

    public function __construct(string $query = "")
    {
        if (!empty($query)) {
            $plxMotor = plxMotor::getinstance();

            $articles = [];
            $pages = [];
            $searchword = plxUtils::unSlash(strtolower(htmlspecialchars(trim($_POST['search']))));

            $plxGlob_arts = clone $plxMotor->plxGlob_arts;
            $motif = '/^[0-9]{4}.[' . $plxMotor->activeCats . ',]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
            if ($aFiles = $plxGlob_arts->query($motif, 'art', 'rsort', 0, false, 'before')) {
                foreach ($aFiles as $v) { # On parcourt tous les fichiers
                    $art = $plxMotor->parseArticle(PLX_ROOT . $plxMotor->aConf['racine_articles'] . $v);
                    $searchstring = plxUtils::unSlash(strtolower(plxUtils::strRevCheck($art['title'] . $art['chapo'] . $art['content'])));
                    if ($searchword != '' and strpos($searchstring, $searchword) !== false) {
                        $article["url"] = $plxMotor->urlRewrite("?".$art['url']);
                        $article["title"] = plxUtils::strCheck($art['title']);;
                        $article["date"] = plxDate::formatDate($art['date'], $this->getFormatDate());
                        $articles[] = $article;
                        $this->setArticlesResults($articles);
                    }
                }
            }

            if ($plxMotor->aStats) {
                foreach ($plxMotor->aStats as $k => $v) {
                    if ($v['active'] == 1 and $v['url'] != $plxMotor->mode) {
                        $filename = PLX_ROOT . $plxMotor->aConf['racine_statiques'] . $k . '.' . $v['url'] . '.php';
                        if (file_exists($filename)) {
                            $searchstring = plxUtils::unSlash(strtolower(plxUtils::strRevCheck(file_get_contents($filename))));
                            if (strpos($searchstring, $searchword) !== false) {
                                $page["url"] = $plxMotor->urlRewrite("?".$v['url']);
                                $page["title"] = plxUtils::strCheck($v['name']);
                                $pages[] = $page;
                                $this->setPagesResults($pages);
                            }
                        }
                    }
                }
            }
        }
    }

    public function getArticlesResults(): array
    {
        return $this->_articlesResults;
    }

    private function setArticlesResults($results): void
    {
        $this->_articlesResults = array_merge($this->getArticlesResults(), $results);
    }

    public function getPagesResults(): array
    {
        return $this->_pagesResults;
    }

    private function setPagesResults(array $pagesResults): void
    {
        $this->_pagesResults = $pagesResults;
    }

    private function getFormatDate(): string
    {
        return $this->_format_date;
    }
}