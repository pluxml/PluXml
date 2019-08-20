<?php
/**
 * Use config.ini and param
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/
namespace models;

class PlxConfigModel {

    const PLX_CONFIG_INI_FILE = 'config.ini';
    
    private $_configIniFile = self::PLX_CONFIG_INI_FILE;
    private $_configIni = array(); # from PLX_CONFIG_INI_FILE parsing
    private $_configuration = array(); # from user configuration file defined in PLX_CONFIG_INI_FILE

    private static $instance;

    /**
     * PlxCOnfigModel singleton creation
     *
     * @return	self return a PlxConfigModel instance
     * @author	Pedro "P3ter" CADETE
     **/
    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = false;
            self::$instance = new PlxConfigModel();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->setConfigIni();
        
        if (is_file($this->getConfigIni('XMLFILE_PARAMETERS'))) {
            $this->setConfiguration($this->getConfigIni('XMLFILE_PARAMETERS'));
        }
        
        printf($this->getConfigIni('XMLFILE_PARAMETERS'));
        printf($this->getConfigIniFile());
    }

    /**
     * Get $_configIniFile
     * @return string
     */
    public function getConfigIniFile() {
        return $this->_configIniFile;
    }

    /**
     * Get $_configIni the array from PLX_CONFIG_INI_FILE
     * @param string $key
     */
    public function getConfigIni(string $key) {
        return $this->_configIni[$key];
    }

    /**
     * Get $_configiguration an array from the user configuration file defined in PLX_CONFIG_INI_FILE  
     * @param string $property
     * @return string
     */
    public function getConfiguration(string $property) {
        return $this->_configuration[$property];
    }

    /**
     * Set $_configIni an array from PLX_CONFIG_INI_FILE parsing
     * @return array
     */
    private function setConfigIni() {
        return $this->_configIni = parse_ini_file($this->getConfigIniFile());
    }
    
    /**
     * Set the $_configuration array with configuration.xml properties
     * @param string $filename
     * @return array
     * @author Anthony GUÉRIN, Florent MONTHEL, Stéphane F, Pedro "P3ter" CADETE
     */
    private function setConfiguration(string $filename){
        $aConf = array(); # contains all the properties from $filename
        
        // XML parser
        $data = implode('',file($filename));
        $parser = xml_parser_create($this->getConfigIni('PLX_CHARSET'));
        $values = array();
        $iTags = array();
        xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
        xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
        xml_parse_into_struct($parser,$data,$values,$iTags);
        xml_parser_free($parser);
        
        // put the properties in $aConf array if the "property" XML element exist
        if(isset($iTags['property'])) {
            $nb = sizeof($iTags['property']);
            for($i = 0; $i < $nb; $i++) {
                if(isset($values[ $iTags['property'][$i] ]['value'])) # the property contains a value
                    $aConf[ $values[ $iTags['property'][$i] ]['attributes']['name'] ] = $values[ $iTags['property'][$i] ]['value'];
                    else # the property has no value
                        $aConf[ $values[ $iTags['property'][$i] ]['attributes']['name'] ] = '';
            }
        }
        
        // root url automatic definition
        $aConf['racine'] = PlxUtilsModel::getRacine();
        
        # On gère la non régression en cas d'ajout de paramètres sur une version de pluxml déjà installée
        $aConf['bypage_admin'] = PlxUtilsModel::getValue($aConf['bypage_admin'],10);
        $aConf['tri_coms'] = PlxUtilsModel::getValue($aConf['tri_coms'],$aConf['tri']);
        $aConf['bypage_admin_coms'] = PlxUtilsModel::getValue($aConf['bypage_admin_coms'],10);
        $aConf['bypage_archives'] = PlxUtilsModel::getValue($aConf['bypage_archives'],5);
        $aConf['bypage_tags'] = PlxUtilsModel::getValue($aConf['bypage_tags'],5);
        $aConf['userfolders'] = PlxUtilsModel::getValue($aConf['userfolders'],0);
        $aConf['meta_description'] = PlxUtilsModel::getValue($aConf['meta_description']);
        $aConf['meta_keywords'] = PlxUtilsModel::getValue($aConf['meta_keywords']);
        $aConf['default_lang'] = PlxUtilsModel::getValue($aConf['default_lang'],$this->getConfigIni('DEFAULT_LANG'));
        $aConf['racine_plugins'] = PlxUtilsModel::getValue($aConf['racine_plugins'], 'plugins/');
        $aConf['racine_themes'] = PlxUtilsModel::getValue($aConf['racine_themes'], 'themes/');
        $aConf['mod_art'] = PlxUtilsModel::getValue($aConf['mod_art'],0);
        $aConf['display_empty_cat'] = PlxUtilsModel::getValue($aConf['display_empty_cat'],0);
        $aConf['timezone'] = PlxUtilsModel::getValue($aConf['timezone'],@date_default_timezone_get());
        $aConf['thumbs'] = isset($aConf['thumbs']) ? $aConf['thumbs'] : 1;
        $aConf['hometemplate'] = isset($aConf['hometemplate']) ? $aConf['hometemplate'] : 'home.php';
        $aConf['custom_admincss_file'] = PlxUtilsModel::getValue($aConf['custom_admincss_file']);
        $aConf['medias'] = isset($aConf['medias']) ? $aConf['medias'] : 'data/images/';

        return $this->_configuration = $aConf;
    }
}

?>