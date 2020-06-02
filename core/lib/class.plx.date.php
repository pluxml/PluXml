<?php

/**
 * Classe plxDate rassemblant les fonctions utiles à PluXml
 * concernant la manipulation des dates
 *
 * @package PLX
 * @author    Stephane F., Amauray Graillat, J.P. Pourrez
 **/

class plxDate
{

    const PATTERN = '@^(\d{4})(\d{2})(\d{2})(\d{2}):?(\d{2})@';
    const FORMAT_DATE = '#num_day/#num_month/#num_year(4)';
    const FORMAT_TIME = '#day #num_day #month #num_year(4), #hour:#minute';

    /**
     * Méthode qui retourne le libellé du mois ou du jour passé en paramètre
     * Ne pas mettre $names en constante, la class plxDate peut être déclarée avant le chargement des traductions.
     *
     * @param string $key 'day', 'month' ou 'short_month'
     * @param string $value numero du mois ou du jour
     * @return string libellé du mois (normal ou raccourci) ou du jour
     * @author Stephane F., Pedro "P3ter" CADETE
     **/
    public static function getCalendar($key, $value)
    {
        $value = $value ? $value : intval($value);
        if (!is_numeric($value)) return false;
        $names = array(
            'month' => array(
                0 => '', //All
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
                0 => '', //All
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

        if (array_key_exists($key, $names) and array_key_exists($value, $names[$key]))
            return $names[$key][$value];
        else
            return false;

    }

    /**
     * Méthode qui formate l'affichage d'une date
     *
     * @param string $date date/heure au format YYYYMMDDHHMM
     * @param string $format format d'affichage
     * @return string date/heure formatée
     * @author Stephane F., J.P. Pourrez
     **/
    public static function formatDate($date, $format = self::FORMAT_DATE)
    {
        $parts = self::date2Array($date);
        $day_of_week = date('w', mktime(0, 0, 0, intval($parts['month']), intval($parts['day']), intval($parts['year'])));

        # On retourne notre date au format humain
        return strtr($format, [
            '#time' => $parts['hour'] . ':' . $parts['minute'],
            '#minute' => $parts['minute'],
            '#hour' => $parts['hour'],
            '#day' => self::getCalendar('day', $day_of_week),
            '#short_month' => self::getCalendar('short_month', $parts['month']),
            '#month' => self::getCalendar('month', $parts['month']),
            '#num_day(1)' => intval($parts['day']),
            '#num_day(2)' => $parts['day'],
            '#num_day' => $parts['day'],
            '#num_month' => $parts['month'],
            '#num_year(2)' => substr($parts['year'], -2),
            '#num_year(4)' => $parts['year'],
        ]);
    }

    /**
     * Méthode qui convertit un timestamp en date/time
     *
     * @param string $timestamp timstamp au format unix
     * @return string date au format YYYYMMDDHHMM
     * @author Stephane F.
     **/
    public static function timestamp2Date($timestamp)
    {
        return date('YmdHi', $timestamp);
    }

    /**
     * Méthode qui éclate une date au format YYYYMMDDHHMM dans un tableau
     *
     * @param string $date date au format YYYYMMDDHHMM
     * @return array|bool tableau contenant le détail de la date ou false si date au mauvais format
     * @author Stephane F.
     **/
    public static function date2Array($date)
    {
        if (preg_match(self::PATTERN, $date, $capture)) {
            return array(
                'year' => $capture[1],
                'month' => $capture[2],
                'day' => $capture[3],
                'hour' => $capture[4],
                'minute' => $capture[5],
                'time' => $capture[4] . ':' . $capture[5],
            );
        }
        return false;
    }

    /**
     * Méthode qui vérifie la validité de la date et de l'heure
     *
     * @param int        mois
     * @param int        jour
     * @param int        année
     * @param int        heure:minute
     * @return    boolean    vrai si la date est valide
     * @author    Amaury Graillat
     **/
    public static function checkDate($day, $month, $year, $time)
    {
        return (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])(0[1-9]|1[0-2])[1-2][0-9]{3}([0-1][0-9]|2[0-3])\:[0-5][0-9]$/", $day . $month . $year . $time)
            and checkdate($month, $day, $year));

    }

    /**
     * Fonction de conversion de date ISO en format RFC822
     *
     * @param string $date date à convertir
     * @return string date au format iso.
     * @author Amaury GRAILLAT
     **/
    public static function dateIso2rfc822($date)
    {
        $tmpDate = plxDate::date2Array($date);
        return date(DATE_RSS, mktime(substr($tmpDate['time'], 0, 2), substr($tmpDate['time'], 3, 2), 0, $tmpDate['month'], $tmpDate['day'], $tmpDate['year']));
    }

}
