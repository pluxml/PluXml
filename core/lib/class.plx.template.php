<?php

/**
 * plxTemplates class is in charge of templates management
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

class PlxTemplate {
    
    private $_templateFolder;                       // the template's relative path
    private $_templateName;                         // the template's name
    private $_templateRawContent;                   // the template's content from filesystem
    private $_templateGeneratedContent;             // generated content from a template
    
    /**
     * Init the templat's name, its raw and generated content
     * 
     * @param   $templateName           string      template's file name
     * @param   $templatePlaceholder    array       placeholder's values to replace in the raw template ("##PLACEHOLDER##" => "value")
     * @author  Pedro "P3ter" CADETE
     */
    public function __construct (string $templateName, array $templatePlaceholdersValues){
        $this->_templateFolder = "../templates/";
        $this->setTemplateName($templateName);
        if ($this->setTemplateRawContent()) 
            $this->setTemplateGeneratedContent($templatePlaceholdersValues);
    }
    
    /**
     * Set the template's name
     * 
     * @param   $name   string      template's file name
     * @return
     * @author  Pedro "P3ter" CADETE
     */
    public function setTemplateName (string $name){
        $this->_templateName = $name;
        return;
    }
    
    /**
     * Set the template's content from filesystem using $_templateFolder and $templateName
     *
     * @return  boolean     false in case of error during opening the template from the filesystem
     * @author  Pedro "P3ter" CADETE
     */
    public function setTemplateRawContent() {
        
        if (!$templateFile = fopen($this->_templateFolder.$this->_templateName, "r")) {
            trigger_error("Unable to open template file ($this->_templateName)", E_USER_ERROR);
            return false;
        }
        else {
            while (!feof($templateFile))
            {
                $this->_templateRawContent .= fread($templateFile, filesize($this->_templateFolder.$this->_templateName));
            }
            fclose($templateFile);
            return true;
        }
    }
    
    /**
     * Set the template's generated content
     *
     * @param   $templatePlaceholder    array       placeholder's values to replace in the raw template ("##PLACEHOLDER##" => "value")
     * @return
     * @author  Pedro "P3ter" CADETE
     */
    public function setTemplateGeneratedContent(array $placeholdersValues) {
        
        $this->_templateGeneratedContent = str_replace(array_keys($placeholdersValues), array_values($placeholdersValues), $this->_templateRawContent);
        
        return;
    }
    
    /**
     * Get the template's name
     * @return  string
     * @author  Pedro "P3ter" CADETE
     */
    public function getTemplateName (){
        return $this->_templateName;
    }
    
    /**
     * Get the template's raw content
     * @return  string
     * @author  Pedro "P3ter" CADETE
     */
    public function getTemplateRawContent (){
        return $this->_templateRawContent;
    }
    
    /**
     * Get the generated content from the raw template
     * @return  string
     * @author  Pedro "P3ter" CADETE
     */
    public function getTemplateGeneratedContent (){
        return $this->_templateGeneratedContent;
    }
}
?>