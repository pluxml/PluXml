<?php

class PlxSearch
{
    private $_plxMotor;
    private $_pagesResults = [];

    public function __construct(string $query = "")
    {
        if (!empty($query)) {
            $this->setPlxMotor();

            $articles = [];
            $pages = [];
            $searchword = plxUtils::unSlash(strtolower(htmlspecialchars(trim($_POST['search']))));

            $plxGlob_arts = clone $this->getPlxMotor()->plxGlob_arts;
            $motif = '/^[0-9]{4}.[' . $this->getPlxMotor()->activeCats . ',]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
            if ($aFiles = $plxGlob_arts->query($motif, 'art', 'rsort', 0, false, 'before')) {
                foreach ($aFiles as $v) { # On parcourt tous les fichiers
                    $art = $this->getPlxMotor()->parseArticle(PLX_ROOT . $this->getPlxMotor()->aConf['racine_articles'] . $v);
                    $searchstring = plxUtils::unSlash(strtolower(plxUtils::strRevCheck($art['title'] . $art['chapo'] . $art['content'])));
                    if ($searchword != '' and strpos($searchstring, $searchword) !== false) {
                        $articles[] = $art;
                        $this->getPlxMotor()->plxRecord_arts = new plxRecord($articles);
                    }
                }
            }

            if ($this->getPlxMotor()->aStats) {
                foreach ($this->getPlxMotor()->aStats as $k => $v) {
                    if ($v['active'] == 1 and $v['url'] != $this->getPlxMotor()->mode) {
                        $filename = PLX_ROOT . $this->getPlxMotor()->aConf['racine_statiques'] . $k . '.' . $v['url'] . '.php';
                        if (file_exists($filename)) {
                            $searchstring = plxUtils::unSlash(strtolower(plxUtils::strRevCheck(file_get_contents($filename))));
                            if (strpos($searchstring, $searchword) !== false) {
                                $page["url"] = $this->getPlxMotor()->urlRewrite("?".$v['url']);
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

    public function getPlxMotor(): plxMotor
    {
        return $this->_plxMotor;
    }

    public function setPlxMotor(): void
    {
        $this->_plxMotor = plxMotor::getInstance();
    }

    public function getPagesResults(): array
    {
        return $this->_pagesResults;
    }

    private function setPagesResults(array $pagesResults): void
    {
        $this->_pagesResults = $pagesResults;
    }
}