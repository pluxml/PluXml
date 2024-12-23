<?php

/**
 * Classe plxDate rassemblant les fonctions utiles à PluXml
 * concernant la manipulation des dates
 *
 * @package PLX
 * @author	Stephane F., Amauray Graillat, Jean-Pierre Pourrez @bazooka07
 **/

class plxDate {
	const PLX_PATTERN = '#^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})$#';
	const ISO_PATTERN = '#^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::\d{2})?$#';
	const NAMES = array(
		'month' => array(
			'01' => L_JANUARY,
			'02' => L_FEBRUARY,
			'03' => L_MARCH,
			'04' => L_APRIL,
			'05' => L_MAY,
			'06' => L_JUNE,
			'07' => L_JULY,
			'08' => L_AUGUST,
			'09' => L_SEPTEMBER,
			'10' => L_OCTOBER,
			'11' => L_NOVEMBER,
			'12' => L_DECEMBER
		),
		'short_month' => array(
			'01' => L_SHORT_JANUARY,
			'02' => L_SHORT_FEBRUARY,
			'03' => L_SHORT_MARCH,
			'04' => L_SHORT_APRIL,
			'05' => L_SHORT_MAY,
			'06' => L_SHORT_JUNE,
			'07' => L_SHORT_JULY,
			'08' => L_SHORT_AUGUST,
			'09' => L_SHORT_SEPTEMBER,
			'10' => L_SHORT_OCTOBER,
			'11' => L_SHORT_NOVEMBER,
			'12' => L_SHORT_DECEMBER
		),
		'day' => array(
			'1' => L_MONDAY,
			'2' => L_TUESDAY,
			'3' => L_WEDNESDAY,
			'4' => L_THURSDAY,
			'5' => L_FRIDAY,
			'6' => L_SATURDAY,
			'0' => L_SUNDAY
		)
	);

	/**
	 * Méthode qui retourne le libellé du mois ou du jour passé en paramètre
	 *
	 * @param	key		constante: 'day', 'month' ou 'short_month'
	 * @param	value	numero du mois ou du jour
	 * @return	string	libellé du mois (long ou court) ou du jour
	 * @author	Stephane F., Pedro "P3ter" CADETE
	 **/
	public static function getCalendar($key, $value) {
		if(!is_numeric($value)) return false;

		if($key == 'long_month') {
			# pour rétro-compatibilité
			$key = 'month';
			$longFormat = true;
		}
		if(array_key_exists($key, self::NAMES) and array_key_exists($value, self::NAMES[$key]))
			return empty($longFormat) ? self::NAMES[$key][$value] : str_pad(self::NAMES[$key][$value], 9);
		else
			return false;

	}

	/**
	 * Méthode qui formate l'affichage d'une date
	 *
	 * @param	date	date/heure au format YYYYMMDDHHMM
	 * @param	format	format d'affichage
	 * @return	string	date/heure formatée
	 * @author	Stephane F.
	 **/
	public static function formatDate($date, $format='#num_day/#num_month/#num_year(4)') {

		# On decoupe notre date
		if(
			preg_match(self::PLX_PATTERN, $date, $matches) or
			# format ISO or <input type="datetime-local">
			preg_match(self::ISO_PATTERN, $date, $matches)
		) {
			$day_of_week = date('w',mktime(0,0,0,intval($matches[2]), intval($matches[3]), intval($matches[1])));
			# On retourne notre date au format humain
			return strtr($format, array(
				'#num_year(4)'	=> $matches[1],
				'#num_month'	=> $matches[2],
				'#num_day'		=> $matches[3],
				'#hour'			=> $matches[4],
				'#minute'		=> $matches[5],
				'#time'			=> $matches[4] . ':' . $matches[5],
				'#day'			=> plxDate::getCalendar('day', $day_of_week),
				'#short_month'	=> plxDate::getCalendar('short_month', $matches[2]),
				'#month'		=> plxDate::getCalendar('month', $matches[2]),
				'#num_day(1)'	=> intval($matches[3]),
				'#num_day(2)'	=> $matches[3],
				'#num_year(2)'	=> substr($date, 2, 2),
			));
		}

		return '';
	}

	/**
	 * Méthode qui convertis un timestamp en date/time
	 *
	 * @param	timestamp	timstamp au format unix
	 * @return	string		date au format YYYYMMDDHHMM
	 * @author	Stephane F.
	 **/
	public static function timestamp2Date($timestamp) {

		return date('YmdHi', $timestamp);

	}

	/**
	 * Méthode qui éclate une date au format YYYYMMDDHHMM dans un tableau
	 *
	 * @param	date		date au format YYYYMMDDHHMM
	 * @return	array		tableau contenant le détail de la date
	 * @author	Stephane F.
	 **/
	public static function date2Array($date) {

		if(
			preg_match(self::PLX_PATTERN, $date, $capture) or
			preg_match(self::ISO_PATTERN, $date, $capture)
		) {
			return array (
				'year' 	=> $capture[1],
				'month' => $capture[2],
				'day' 	=> $capture[3],
				'hour'	=> $capture[4],
				'minute'=> $capture[5],
				'time' 	=> $capture[4].':'.$capture[5],
			);
		}

		# default date
		return array (
			'year' 	=> '1970',
			'month' => '01',
			'day' 	=> '01',
			'hour'	=> '00',
			'minute'=> '00',
			'time' 	=> '00:00',
		);
	}

	/**
	 * Méthode qui vérifie la validité de la date et de l'heure
	 *
	 * @param	int		mois
	 * @param	int		jour
	 * @param	int		année
	 * @param	int		heure:minute
	 * @return	boolean	vrai si la date est valide
	 * @author	Amaury Graillat
	 **/
	public static function checkDate($day, $month, $year, $time) {

		return (
			preg_match('@^[123]\d{3}$@', $year)
			and preg_match('@^(?:[01]\d|2[0-3])\:[0-5]\d$@', $time)
			and checkdate($month, $day, $year)
		);
	}

	/**
	 * Fonction de conversion de date ISO en format RFC822
	 *
	 * @param	date	date à convertir
	 * @return	string	date au format iso.
	 * @author	Amaury GRAILLAT
	 **/
	public static function dateIso2rfc822($date) {

		$tmpDate = plxDate::date2Array($date);
		return date(DATE_RSS, mktime(substr($tmpDate['time'],0,2), substr($tmpDate['time'],3,2), 0, $tmpDate['month'], $tmpDate['day'], $tmpDate['year']));
	}

}
