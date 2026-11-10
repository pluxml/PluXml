<?php

/**
 * Classe plxDate rassemblant les fonctions utiles à PluXml
 * concernant la manipulation des dates
 *
 * @package PLX
 * @author	Stephane F., Amauray Graillat
 **/

class plxDate {
	const NAMES= array(
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
		'long_month' => array(
			'01' => L_LONG_JANUARY,
			'02' => L_LONG_FEBRUARY,
			'03' => L_LONG_MARCH,
			'04' => L_LONG_APRIL,
			'05' => L_LONG_MAY,
			'06' => L_LONG_JUNE,
			'07' => L_LONG_JULY,
			'08' => L_LONG_AUGUST,
			'09' => L_LONG_SEPTEMBER,
			'10' => L_LONG_OCTOBER,
			'11' => L_LONG_NOVEMBER,
			'12' => L_LONG_DECEMBER
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
	 * @param	string $key		constante: 'day', 'month' ou 'short_month'
	 * @param	string $value	numero du mois ou du jour
	 * @return	string	libellé du mois (long ou court) ou du jour
	 * @author	Stephane F., Pedro "P3ter" CADETE
	 **/
	public static function getCalendar($key, $value) {
		if(
			!is_numeric($value) or
			!array_key_exists($key, self::NAMES) or
			!array_key_exists($value, self::NAMES[$key])
		) {
			return '';
		}

		return self::NAMES[$key][$value];
	}

	/**
	 * Méthode qui formate l'affichage d'une date
	 *
	 * @param	string $date	date/heure au format YYYYMMDDHHMM
	 * @param	string $format	format d'affichage
	 * @return	string	date/heure formatée
	 * @author	Stephane F.
	 **/
	public static function formatDate($date, $format='#num_day/#num_month/#num_year(4)') {

		# On decoupe notre date
		$year4 = substr($date, 0, 4);
		$year2 = substr($date, 2, 2);
		$month = substr($date, 4, 2);
		$day = substr($date, 6, 2);
		$day_num = date('w', mktime(0, 0, 0, intval($month), intval($day), intval($year4)));
		$hour = substr($date,8,2);
		$minute = substr($date,10,2);

		# On retourne notre date au format humain
		return strtr($format, array(
			'#time'			=>  $hour . ':' . $minute,
			'#minute'		=>  intval($minute),
			'#hour'			=>  intval($hour),
			'#day'			=>  self::getCalendar('day', $day_num),
			'#short_month'	=>  self::getCalendar('short_month', $month),
			'#month'		=>  self::getCalendar('month', $month),
			'#num_day(1)'	=>  intval($day),
			'#num_day(2)'	=>  intval($day),
			'#num_day'		=>  $day,
			'#num_month'	=>  $month,
			'#num_year(2)'	=>  intval($year2),
			'#num_year(4)'	=>  intval($year4),
		));
	}

	/**
	 * Méthode qui convertis un timestamp en date/time
	 *
	 * @param	int $timestamp	timstamp au format unix
	 * @return	string		date au format YYYYMMDDHHMM
	 * @author	Stephane F.
	 **/
	public static function timestamp2Date($timestamp) {

		return date('YmdHi', $timestamp);

	}

	/**
	 * Méthode qui éclate une date au format YYYYMMDDHHMM dans un tableau
	 *
	 * @param	string $date		date au format YYYYMMDDHHMM
	 * @return	array		tableau contenant le détail de la date
	 * @author	Stephane F.
	 **/
	public static function date2Array($date) {
		preg_match('/(\d{4})(\d{2})(\d{2})([0-9:]{2})([0-9:]{2})/', $date, $capture);
		return array (
			'year' 	=> intval($capture[1]),
			'month' => intval($capture[2]),
			'day' 	=> intval($capture[3]),
			'hour'	=> intval($capture[4]),
			'minute'=> intval($capture[5]),
			'time' 	=> $capture[4].':'.$capture[5]
		);
	}

	/**
	 * Méthode qui vérifie la validité de la date et de l'heure
	 *
	 * @param	int		$day
	 * @param	int		$month
	 * @param	int		$year
	 * @param	string $time		heure:minute
	 * @return	boolean	vrai si la date est valide
	 * @author	Amaury Graillat
	 **/
	public static function checkDate($day, $month, $year, $time) {

		return (preg_match("/^(0[1-9]|[1-2]\d|3[0-1])(0[1-9]|1[0-2])[1-2]\d{3}([0-1]\d|2[0-3])\:[0-5]\d$/",$day.$month.$year.$time)
			AND checkdate($month, $day, $year));

	}

	/**
	 * Fonction de conversion de date ISO en format RFC822
	 *
	 * @param	string $date	date à convertir
	 * @return	string	date au format iso.
	 * @author	Amaury GRAILLAT
	 **/
	public static function dateIso2rfc822($date) {

		$tmpDate = plxDate::date2Array($date);
		return date(DATE_RSS, mktime(intval(substr($tmpDate['time'], 0, 2)), intval(substr($tmpDate['time'], 3, 2)), 0, $tmpDate['month'], $tmpDate['day'], $tmpDate['year']));
	}

}
