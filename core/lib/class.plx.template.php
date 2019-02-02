<?php

/**
 * plxTemplates class is in charge of templates management
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

class PlxTemplate {
    
    private $_templateFolder = "../templates/";     // the template's relative path
    private $_templateName;                         // the template's name
    private $_templateRawContent;                           // the template's content from filesystem
    private $_templateGeneratedContent;                     // generated content from a template
    
    /**
     * Set the template's name
     * 
     * @param   $templateName   string          template's file name
     * @return
     */
    public function setTemplateName (string $name){
        $this->_templateName = $name;
        return;
    }
    
    /**
     * Set the template's content from filesystem using $_templateFolder and $templateName
     *
     * @return  $rawTemplate    string          template's content
     * @author  Pedro "P3ter" CADETE
     */
    public function setTemplateRawContent() {
        
        $templateFile = fopen($this->_templateFolder.$this->_templateName, "r");
        
        while (!feof($templateFile))
        {
            $this->_templateRawContent .= fread($templateFile, filesize($this->_templateFolder.$this->_templateName));
        }
        fclose($templateFile);
        
        return;
    }
    
    /**
     * Set the template's generated content
     *
     * @param   $template       PlxTemplate     the template object
     * @param   $templatePlaceholder    array       placeholder's values to replace in the template
     * @return
     * @author  Pedro "P3ter" CADETE
     */
    public function setTemplateGeneratedContent(array $templatePlaceholdersValues) {
        
        $this->_templateGeneratedContent = str_replace(array_keys($templatePlaceholdersValues), array_values($templatePlaceholdersValues), $this->_templateRawContent);
        
        return;
    }
    
    /**
     * Get the template's name
     * @return
     */
    public function getTemplateName (){
        return $this->_templateName;
    }
    
    /**
     * Get the template's raw content
     * @return
     */
    public function getTemplateRawContent (){
        return $this->_templateRawContent;
    }
    
    /**
     * Get generated content from the raw template
     * @return
     */
    public function getTemplateGeneratedContent (){
        return $this->_templateGeneratedContent;
    }
}

$body = new PlxTemplate();
$body->setTemplateName("email-lostpassword.txt");
$body->setTemplateRawContent();
$placeholdersValues = array(
    "##TITLE##"     =>  "PluXml",
    "##DATE##"      =>  "02/02/2019",
    "##CONTENT##"   =>  "du texte"
);
$body->setTemplateGeneratedContent($placeholdersValues);
echo $body->getTemplateGeneratedContent();

?>