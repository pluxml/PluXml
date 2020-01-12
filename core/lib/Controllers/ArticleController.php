<?php

/**
 * ArticleController class the PluXml controller for Articles
 *
 * @package PLX
 * @author	Pedro "P3ter" CADETE
 **/

namespace Pluxml\Controllers;

class ArticleController {
	
	public function show($slug, $id, $page) {
		echo "Je suis l'article $id et en page : $page";
	}
}