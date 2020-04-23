<?php

const L_LANGUE						= 'Español';

const L_DATE_CREATION				= 'Fecha de creación';
const L_DATE_UPDATE					= 'Fecha de actualización';

# common
const L_PLUXML_VERSION				= 'Versión de PluXml';
const L_WRONG_PHP_VERSION			= 'PluXml necesita PHP %s posterior.';
const L_HOMEPAGE					= 'Página principal';
const L_BACK_HOMEPAGE_TITLE			= 'Volver a la página de inicio';
const L_UNCLASSIFIED				= 'Sin categoría';
const L_INFO_PHP_VERSION			= 'Versión de PHP';
const L_INFO_CHARSET				= 'codificación';
const L_NONE1						= 'ninguno';
const L_NONE2						= 'ninguna';
const L_PASSWORD					= 'Contraseña';
const L_TIMEZONE					= 'Huso horario';
const L_SELECT_LANG					= 'Seleccione su idioma';
const L_CONFIRM_PASSWORD			= 'Confirme la contraseña';
const L_PROFIL_LOGIN				= 'Cuenta de usuario';
const L_INPUT_CHANGE				= 'Cambiar';
const L_COMMENTS_CLOSED				= 'Comentarios cerrados';
const L_SITE_DESCRIPTION			= 'Blog o CMS con XML!';

# index.php
const L_ERR_THEME_NOTFOUND			= 'No se encontró el tema principal de PluXml';
const L_ERR_FILE_NOTFOUND			= 'No se encontró el archivo destino de PluXml';
const L_ERR_PAGE_NOT_FOUND			= 'No se encontró la página';

# class.plx.date.php
const L_SHORT_JANUARY				= 'ene';
const L_SHORT_FEBRUARY				= 'feb';
const L_SHORT_MARCH					= 'mar';
const L_SHORT_APRIL					= 'abr';
const L_SHORT_MAY					= 'may';
const L_SHORT_JUNE					= 'jun';
const L_SHORT_JULY					= 'jul';
const L_SHORT_AUGUST				= 'ago';
const L_SHORT_SEPTEMBER				= 'sep';
const L_SHORT_OCTOBER				= 'oct';
const L_SHORT_NOVEMBER				= 'nov';
const L_SHORT_DECEMBER				= 'dic';
const L_JANUARY						= 'enero';
const L_FEBRUARY					= 'febrero';
const L_MARCH						= 'marzo';
const L_APRIL						= 'abril';
const L_MAY							= 'mayo';
const L_JUNE						= 'junio';
const L_JULY						= 'julio';
const L_AUGUST						= 'agosto';
const L_SEPTEMBER					= 'septiembre';
const L_OCTOBER						= 'octubre';
const L_NOVEMBER					= 'noviembre';
const L_DECEMBER					= 'diciembre';
const L_MONDAY						= 'lunes';
const L_TUESDAY						= 'martes';
const L_WEDNESDAY					= 'miércoles';
const L_THURSDAY					= 'jueves';
const L_FRIDAY						= 'viernes';
const L_SATURDAY					= 'sábado';
const L_SUNDAY						= 'domingo';

# class.plx.capcha.php
const L_LAST						= 'última';
const L_FIRST						= 'primera';
const L_SECOND						= 'segunda';
const L_THIRD						= 'tercera';
const L_FOURTH						= 'cuarta';
const L_FIFTH						= 'quinta';
const L_SIXTH						= 'sexta';
const L_SEVENTH						= 'septima';
const L_EIGTH						= 'octava';
const L_NINTH						= 'novena';
const L_TENTH						= 'décima';
const L_NTH							= 'º';
const L_CAPCHA_QUESTION				= 'Cuál es la <span class="capcha-letter">%s</span> letra en la palabra <span class="capcha-word">%s</span>?';

# class.plx.utils.php
const L_WRITE_ACCESS				= 'Puede editar %s';
const L_WRITE_NOT_ACCESS			= 'No se puede editar %s o no existe';
const L_MODREWRITE_AVAILABLE		= 'Módulo de Apache para re-escritura de URLs («mod_rewrite») disponible';
const L_MODREWRITE_NOT_AVAILABLE	= 'Módulo de Apache para re-escritura de URLs («mod_rewrite») no disponible';
const L_LIBGD_INSTALLED				= 'Biblioteca GD instalada';
const L_LIBGD_NOT_INSTALLED			= 'Biblioteca GD no instalada';
const L_LIBXML_INSTALLED			= 'Biblioteca XML instalada';
const L_LIBXML_NOT_INSTALLED		= 'Biblioteca XML no instalada';
const L_MAIL_AVAILABLE				= 'Función de envío de correos disponible';
const L_MAIL_NOT_AVAILABLE			= 'Función de envío de correos no disponible';

# class.plx.motor.php
const L_ARTICLE_NO_TAG				= 'No existen artículos para esta etiqueta!';
const L_UNKNOWN_CATEGORY			= 'Esta categoría no existe!';
const L_NO_ARTICLE_PAGE				= 'No existen artículos para esta página!';
const L_UNKNOWN_ARTICLE				= 'Este artículo no existe!';
const L_COM_PUBLISHED				= 'El comentario está publicado';
const L_COM_IN_MODERATION			= 'Comentario en proceso de moderación por el administrador de este sitio';
const L_UNKNOWN_STATIC				= 'Esta página no existe!';
const L_DOCUMENT_NOT_FOUND			= 'No se encotró el documento especificado!';
const L_NEWCOMMENT_ERR				= 'Se ha producido un error al publicar este comentario';
const L_NEWCOMMENT_FIELDS_REQUIRED	= 'Ingrese todos los campos obligatorios';
const L_NEWCOMMENT_ERR_ANTISPAM		= 'Falló La comprobación anti-spam';

# class.plx.show.php

const L_HTTPENCODING				= 'Compresión %s activada';
const L_ARCHIVES			= 'Archivos';
const L_TAG				= 'Etiqueta';
const L_NO_CATEGORY					= 'ninguna categoría';
const L_CATEGORY					= 'categoría';
const L_CATEGORIES					= 'categorías';
const L_NO_ARTICLE					= 'sin artículo';
const L_ARTICLE						= 'artículo';
const L_ARTICLES					= 'artículos';
const L_ARTAUTHOR_UNKNOWN			= 'desconocido';
const L_ARTCHAPO					= 'Leer más #art_title';
const L_ARTFEED_RSS_CATEGORY		= 'Sindicación RSS de los artículos de esta categoría';
const L_ARTFEED_RSS_TAG				= 'Sindicación RSS de los artículos de esta etiqueta';
const L_ARTFEED_RSS					= 'Sindicación RSS de los artículos';
const L_NO_COMMENT					= 'sin comentarios';
const L_COMMENT						= 'comentario';
const L_COMMENTS					= 'comentarios';
const L_COMFEED_RSS_ARTICLE			= 'Sindicación RSS de los comentarios de este artículo';
const L_COMFEED_RSS					= 'Sindicación RSS de los comentarios';
const L_STATICCONTENT_INPROCESS		= 'Página en proceso de edición';
const L_SAID						= 'ha dicho';

const L_PAGINATION_FIRST_TITLE		= 'Ir a la primera página';
const L_PAGINATION_FIRST			= '«';
const L_PAGINATION_PREVIOUS_TITLE	= 'Página anterior';
const L_PAGINATION_PREVIOUS			= 'anterior';
const L_PAGINATION_NEXT_TITLE		= 'Página siguiente';
const L_PAGINATION_NEXT				= 'siguiente';
const L_PAGINATION_LAST_TITLE		= 'Ir a la última página';
const L_PAGINATION_LAST				= '»';
const L_PAGINATION					= 'página %s de %s';

const L_BLOG						= 'Blog';

const L_YEAR					    = 'año';
const L_TOTAL					    = 'total';

# class.plx.feed.php
const L_FEED_NO_PRIVATE_URL			= 'Las URLs privadas no han sido inicializadas en sus parámetros de administración!';
const L_FEED_ONLINE_COMMENTS		= 'Comentarios de usuarios';
const L_FEED_OFFLINE_COMMENTS		= 'Comentarios sin conexión';
const L_WRITTEN_BY					= 'Escrito por';

?>
