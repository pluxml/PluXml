<?php
/**
 * Plugin QuickSearch
 *
 * @package	PLX
 * @version	1.0
 * @date	05/04/2013
 * @author	i M@N
 **/
 
class plxQuickSearch extends plxPlugin {
public function __construct($default_lang) {
# Appel du constructeur de la classe plxPlugin (obligatoire)
parent::__construct($default_lang);
# Déclaration des hooks
$this->addHook('AdminMediasQuickSearch', 'AdminMediasQuickSearch');
}
public function AdminMediasQuickSearch() {
echo '&nbsp;
<input type="text" id="qs" placeholder="'.$this->getLang('L_RECHERCHER').'" />
<script type="text/javascript" src="'.PLX_PLUGINS.'plxQuickSearch/jquery.quicksearch.js"></script>
<script type="text/javascript">
$(document).ready(function() {
$(\'table.table\').attr({\'id\': \'quicksearch\'});
$(\'th.checkbox input:checkbox\').attr({\'id\': \'checkall\'});
$(\'input#qs\').quicksearch(\'table#quicksearch tbody tr\', {
//    \'delay\': 100,
	\'show\': function () {
		$(\'tr.qsd td input:checkbox\').attr({\'name\': \'idFile[]\',\'checked\': false});
		$(this).removeClass(\'qsd\').addClass(\'qse\');
		$(\'input#checkall:checkbox\').attr({\'checked\': false});
		$(this).show();
	},
	\'hide\': function () {
		$(this).removeClass(\'qse\').addClass(\'qsd\');
		$(\'tr.qsd td input:checkbox\').attr({\'name\': \'disabled\',\'checked\': false});
		$(\'input#checkall:checkbox\').attr({\'checked\': false});
		$(this).hide();
	},
});
});
</script>
';
}
}
	
?>
