<?php
/**
 * Plugin jquery
 *
 * @package	PLX
 * @author	Stephane F
 **/
class jquery extends plxModule {

	/**
	 * Constructeur de la classe
	 *
	 * @param	default_lang	langue par défaut utiliséepar PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe (obligatoire)
		parent::__construct($default_lang);

		# Déclarations des hooks
		$this->addHook('ThemeEndHead', 'addJQuery');
		$this->addHook('AdminTopEndHead', 'addJQuery');
	}

	/**
	 * Méthode qui ajoute l'insertion du javascript dans la partie <head> du site
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function addJQuery() {
		echo '
<script src="'.$this->REL_PATH().'jquery.min.js"></script>
<script>
$.ajaxPrefilter(function( options, original_Options, jqXHR ) {
    options.async = true;
});
</script>
';
	}

}
?>