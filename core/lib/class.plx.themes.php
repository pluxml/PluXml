<?php
/**
 * PlxThemes class is in charge of themes in PluXml Administration
 *
 * @package PLX
 * @author  Florent MONTHEL, Stephane F, Pedro "P3ter" CADETE
 **/

class plxThemes
{
    public $racineTheme;
    public $activeTheme;
    public $themesList = array();

    public function __construct($racineTheme, $activeTheme)
    {
        $this->racineTheme = $racineTheme;
        $this->activeTheme = $activeTheme;
        $this->setThemesList();
    }

    public function getImgPreview($theme)
    {
        $img = '';
        $previews = glob($this->racineTheme . $theme . '/preview.{jpg,jpeg,png,gif}',  GLOB_BRACE);
        $src = !empty($previews) ? $previews[0] : 'theme/images/theme.png';
        $imgSizes = getimagesize($src);
        return '<img class="img-preview" src="' . $src . '" alt="preview" ' . $imgSizes[3] . ' />';
    }

    public function getInfos($theme)
    {
        $aInfos = array();
        $filename = $this->racineTheme . $theme . '/infos.xml';
        if (is_file($filename)) {
            $data = implode('', file($filename));
            $parser = xml_parser_create(PLX_CHARSET);
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
            xml_parse_into_struct($parser, $data, $values, $iTags);
            xml_parser_free($parser);
            $aInfos = array(
                'title' => (isset($iTags['title']) and isset($values[$iTags['title'][0]]['value'])) ? $values[$iTags['title'][0]]['value'] : '',
                'author' => (isset($iTags['author']) and isset($values[$iTags['author'][0]]['value'])) ? $values[$iTags['author'][0]]['value'] : '',
                'version' => (isset($iTags['version']) and isset($values[$iTags['version'][0]]['value'])) ? $values[$iTags['version'][0]]['value'] : '',
                'date' => (isset($iTags['date']) and isset($values[$iTags['date'][0]]['value'])) ? $values[$iTags['date'][0]]['value'] : '',
                'site' => (isset($iTags['site']) and isset($values[$iTags['site'][0]]['value'])) ? $values[$iTags['site'][0]]['value'] : '',
                'description' => (isset($iTags['description']) and isset($values[$iTags['description'][0]]['value'])) ? $values[$iTags['description'][0]]['value'] : '',
            );
        }
        return $aInfos;
    }

    public function setThemesList()
    {
        // The active theme is the first to be set in the list
        if (is_dir($this->racineTheme . $this->activeTheme)) {
            $this->themesList[$this->activeTheme] = $this->activeTheme;
        }

        // Other themes
        $files = plxGlob::getInstance($this->racineTheme, true);
        if ($styles = $files->query("/[a-z0-9-_\.\(\)]+/i", "", "sort")) {
            foreach ($styles as $k => $v) {
                if (is_file($this->racineTheme . $v . '/infos.xml')) {
                    if (substr($v, 0, 7) != 'mobile.' and $v != $this->activeTheme)
                        $this->themesList[$v] = $v;
                }
            }
        }
    }
}
