<?php

class PlxSearch
{
    private $_query = "";
    private $_results = [];
    private $_format_date = '#num_day/#num_month/#num_year(4)';

    public function __construct(string $query = "")
    {

        if (!empty($query)) {
            $plxMotor = plxMotor::getinstance();

            $this->setQuery($query);

            $word = plxUtils::strCheck(plxUtils::unSlash($_POST['search']));
            $searchword = strtolower(htmlspecialchars(trim($_POST['search'])));
            $searchword = plxUtils::unSlash($searchword);


            $plxGlob_arts = clone $plxMotor->plxGlob_arts;
            $motif = '/^[0-9]{4}.[' . $plxMotor->activeCats . ',]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
            if ($aFiles = $plxGlob_arts->query($motif, 'art', 'rsort', 0, false, 'before')) {
                foreach ($aFiles as $v) { # On parcourt tous les fichiers
                    $art = $plxMotor->parseArticle(PLX_ROOT . $plxMotor->aConf['racine_articles'] . $v);
                    $searchstring = strtolower(plxUtils::strRevCheck($art['title'] . $art['chapo'] . $art['content']));
                    $searchstring = plxUtils::unSlash($searchstring);
                    if ($searchword != '' and strpos($searchstring, $searchword) !== false) {
                        $searchresults = true;
                        $art_num = intval($art['numero']);
                        $art_url = $art['url'];
                        $art_title = plxUtils::strCheck($art['title']);
                        $art_date = plxDate::formatDate($art['date'], $this->getFormatDate());
                        $result[] = $art_title;

                        //set $_searchResults
                        $this->setResults($result);
                    }
                }
            }

            if ($plxMotor->aStats) {
                foreach ($plxMotor->aStats as $k => $v) {
                    if ($v['active'] == 1 and $v['url'] != $plxMotor->mode) { # si la page est bien active
                        $filename = PLX_ROOT . $plxMotor->aConf['racine_statiques'] . $k . '.' . $v['url'] . '.php';
                        if (file_exists($filename)) {
                            $searchstring = strtolower(plxUtils::strRevCheck(file_get_contents($filename)));
                            $searchstring = plxUtils::unSlash($searchstring);
                            if (strpos($searchstring, $searchword) !== false) {
                                $searchresults = true;
                                $stat_num = intval($k);
                                $stat_url = $v['url'];
                                $stat_title = plxUtils::strCheck($v['name']);
                                $result[] = $stat_title;

                                //set $_searchResults
                                $this->setResults($result);
                            }
                        }
                    }
                }
            }
        }
    }

    public function getQuery(): string
    {
        return $this->_query;
    }

    public function setQuery(string $query): void
    {
        $this->_query = $query;
    }

    public function getResults(): array
    {
        return $this->_results;
    }

    public function setResults($results): void
    {
        $this->_results = array_merge($this->getResults(), $results);
    }

    public function getFormatDate(): string
    {
        return $this->_format_date;
    }
}