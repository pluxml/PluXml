<?php

/**
 * plxTemplates class is in charge of mails templates management
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

class PlxTemplate {

	private $_templateName;				# the template's name
	private $_templateType;				# the template's type : post, page or email
	private $_templateEmailName;		# the sender's name for an email template
	private $_templateEmailFrom;		# the sender's email address for email template type
	private $_templateEmailSubject;		# the subject for an email template type
	private $_templateRawContent;		# the template's content from filesystem
	private $_templateGeneratedContent;	# generated content from a template
	private $_templateFolder;			# the path to the template's folder

	/**
	 * Init all template's attributs from his file name
	 *
	 * @param	$templateName				string	template's file name
	 * @param	$templatePlaceholderValues	array	placeholder's values to replace in the raw template ("##PLACEHOLDER##" => "value")
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 */
	public function __construct($templateFolder, $templateFileName, $templatePlaceholdersValues = array()) {

		$this->setTemplateFolder($templateFolder);
		$template = $this->parseTemplate($this->_templateFolder.$templateFileName);

		$this->setTemplateName($template['name']);
		$this->setTemplateType($template['type']);

		if ($this->getTemplateType() == 'email') {
			$this->setTemplateEmailName($template['emailname']);
			$this->setTemplateEmailFrom($template['emailfrom']);
			$this->setTemplateEmailSubject($template['emailsubject']);
		}

		if ($this->setTemplateRawContent($template['content']) AND !empty($templatePlaceholdersValues))
			$this->setTemplateGeneratedContent($templatePlaceholdersValues);
	}

	/**
	 * Set the template's name
	 *
	 * @param	$folder	string	templates folder
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 */
	private function setTemplateFolder($folder) {

		$this->_templateFolder = $folder;
	}

	/**
	 * Set the template's name
	 *
	 * @param	$name	string	template's name
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 */
	private function setTemplateName($name) {

		$this->_templateName = $name;
	}

	/**
	 * Set the template's type
	 *
	 * @param	$type	string	template's type (post, page, email)
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 */
	private function setTemplateType($type) {

		$this->_templateType = $type;
	}

	/**
	 * Set the name of the email sender
	 *
	 * @param	$emailName	string	template's emailname
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 */
	private function setTemplateEmailName($emailName) {

		$this->_templateEmailName = $emailName;
	}

	/**
	 * Set the "from" email address
	 *
	 * @param	$emailFrom	string	template's emailfrom
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 */
	private function setTemplateEmailFrom($emailFrom) {

		$this->_templateEmailFrom = $emailFrom;
	}

	/**
	 * Set the email subject
	 *
	 * @param	$emailFrom	string	template's emailsubject
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 */
	private function setTemplateEmailSubject($emailSubject) {

		$this->_templateEmailSubject = $emailSubject;
	}

	/**
	 * Set the template's content
	 *
	 * @param	content	string	template's content
	 * @return	void
	 * @author	Pedro "P3ter" CADETE
	 */
	private function setTemplateRawContent($content) {

		$this->_templateRawContent = $content;
	}

	/**
	 * Set the template's generated content
	 *
	 * @param	$templatePlaceholder	array	placeholder's values to replace in the raw template ("##PLACEHOLDER##" => "value")
	 * @return	string	return "1" if no values for placeholders were given
	 * @author	Pedro "P3ter" CADETE
	 */
	private function setTemplateGeneratedContent(array $placeholdersValues) {

		if (!empty($this->_templateRawContent))
			$this->_templateGeneratedContent = str_replace(array_keys($placeholdersValues), array_values($placeholdersValues), $this->_templateRawContent);
		else
			$this->_templateGeneratedContent = '1';
	}

	/**
	 * Get the template's name
	 * @return	string
	 * @author	Pedro "P3ter" CADETE
	 */
	public function getTemplateName() {

		return $this->_templateName;
	}

	/**
	 * Get the template's type
	 * @return	string
	 * @author	Pedro "P3ter" CADETE
	 */
	public function getTemplateType() {

		return $this->_templateType;
	}

	/**
	 * Get the template's emailName
	 * @return	string
	 * @author	Pedro "P3ter" CADETE
	 */
	public function getTemplateEmailName() {

		return $this->_templateEmailName;
	}

	/**
	 * Get the template's emailFrom
	 * @return	string
	 * @author	Pedro "P3ter" CADETE
	 */
	public function getTemplateEmailFrom() {

		return $this->_templateEmailFrom;
	}

	/**
	 * Get the template's emailSubject
	 * @return	string
	 * @author	Pedro "P3ter" CADETE
	 */
	public function getTemplateEmailSubject() {

		return $this->_templateEmailSubject;
	}

	/**
	 * Get the template's raw content
	 * @return	string
	 * @author	Pedro "P3ter" CADETE
	 */
	public function getTemplateRawContent() {

		return $this->_templateRawContent;
	}

	/**
	 * Get the generated content from the raw template
	 * @return	string
	 * @author	Pedro "P3ter" CADETE
	 */
	public function getTemplateGeneratedContent($placeholdersValues = NULL) {

		if (empty($this->_templateGeneratedContent) AND $placeholdersValues != NULL)
			$this->setTemplateGeneratedContent($placeholdersValues);

		return $this->_templateGeneratedContent;
	}

	/**
	 * Method in charge of parsing templates XML files
	 *
	 * @param	filename	fichier de l'article à parser
	 * @return	array
	 * @author	Pedro "P3ter" CADETE
	 **/
	private function parseTemplate($fileName) {

		# parser initialisation
		$data = implode('',file($fileName));
		$parser = xml_parser_create('UTF-8');
		$values = '';
		$index = '';
		$template = array();
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$index);
		xml_parser_free($parser);

		# getting datas from the parser
		$name = plxUtils::getValue($index['name'][0]);
		$template['name'] = plxUtils::getValue($values[$name]['value']);
		$type = plxUtils::getValue($index['type'][0]);
		$template['type'] = plxUtils::getValue($values[$type]['value']);
		if ($template['type'] == 'email') {
			$emailname = plxUtils::getValue($index['emailname'][0]);
			$template['emailname'] = plxUtils::getValue($values[$emailname]['value']);
			$emailfrom = plxUtils::getValue($index['emailfrom'][0]);
			$template['emailfrom'] = plxUtils::getValue($values[$emailfrom]['value']);
			$emailsubject = plxUtils::getValue($index['emailsubject'][0]);
			$template['emailsubject'] = plxUtils::getValue($values[$emailsubject]['value']);

		}
		$content = plxUtils::getValue($index['content'][0]);
		$template['content'] = plxUtils::getValue($values[$content]['value']);

		return $template;
	}
}