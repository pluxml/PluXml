<?php
/**
 * Plugin tagEditor
 *
 * @package	PLX
 * @author	Stephane F
 **/
class tagEditor extends plxModule {

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
		$this->addHook('AdminTopEndHead', 'AdminTopEndHead', 'article.php');
		$this->addHook('AdminFootEndBody', 'AdminFootEndBody', 'article.php');

	}

	/**
	 * Méthode qui ajoute l'insertion de la feuille de style dans la partie <head> du site
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminTopEndHead() {
		echo '
<link rel="stylesheet" href="'.$this->REL_PATH().'jquery.tag-editor.css">
<style>
.tag-editor { width: 240px; display: inline-block; vertical-align: middle; }
.tag-editor li { padding: 4px 0; height: 28px; margin: 0;
.tag-editor .tag-editor-spacer { width: 4px }
}
</style>
';
	}

	/**
	 * Méthode qui ajoute l'insertion du javascript dans la partie <body> du site
	 *
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function AdminFootEndBody() {
		echo '
<script src="'.$this->REL_PATH().'jquery.caret.min.js"></script>
<script src="'.$this->REL_PATH().'jquery.tag-editor.min.js"></script>
<script>
	$("#id_tags").tagEditor({forceLowercase:false});
	$("#tags a").click(function(){
		$("#id_tags").tagEditor("addTag", $(this).text());
	});
</script>
';
	}
}
?>