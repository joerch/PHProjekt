<?php
/**
 * Class to hold a collection of dates. These dates can also be automatically
 * calculated from a iCal-rrule.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 */
class Phprojekt_Date_Collection
{
    /**
     * Array holding the elements of the collection.
     * Each Element is a timestamp
     *
     * @var array
     */
    private $_elements = array();

    /**
     * The highest value that should be allowed.
     * If a higher value is added it will be dropped.
     *
     * @var int
     */
    private $_maxDate = null;

    /**
     * The lowest value that should be allowed.
     * If a lower value is added it will be dropped.
     *
     * @var int
     */
    private $_minDate = null;

    /**
     * Zend_Date class
     *
     * @var Zend_Date
     */
    private $_date = null;

    /**
     * Create a new collection of dates.
     *
     * @param string $minDate The lowsest allowed value
     * @param string $maxDate The highest allowed value
     */
    public function __construct($minDate, $maxDate = null)
    {
        $this->_date    = new Zend_Date();

        $this->_minDate = $this->_getDate(strtotime($minDate));
        if (null != $maxDate) {
            $this->_maxDate = $this->_getDate(strtotime($maxDate));
        }
    }

    /**
     * Adds a date to the Collection.
     * If the date is higher/lower than maxDate/minDate it will not be added.
     *
     * @param int $element A timestamp date
     *
     * @return void
     */
    public function add($element)
    {
        if (!isset($this->_elements[$element])) {
            $this->_elements[$element] = $element;
        }
    }

    /**
     * Adds a date to the Collection.
     * If the date is higher/lower than maxDate/minDate it will not be added.
     *
     * @param array $elements An array of timestamp strings
     *
     * @return void
     */
    public function addArray(array $elements)
    {
        foreach ($elements as $e) {
            $this->add($e);
        }
    }

    /**
     * Fill the collection with all dates that can be calculated from rrule starting with minDate.
     * If there already are elements in the collection they will be dropped.
     *
     * @param string $rrule The rrule that should be parsed
     *
     * @return boolean TRUE if parsing was successfull, FALSE otherwise
     */
    public function applyRrule($rrule)
    {
        // Clear collection
        $this->_elements = array();
        // Parse RRule
        $rules = $this->_parseRrule($rrule);
        // Detect mathod to use for increment
        switch ($rules['FREQ']) {
            case 'YEARLY':
                $method = 'addYear';
                break;
            case 'MONTHLY':
                $method = 'addMonth';
                break;
            case 'WEEKLY':
                $method = 'addWeek';
                break;
            case 'DAILY':
                $method = 'addDay';
                break;
            default:
                // Frequence is not supported
                $method = null;
                return false;
        }
        $date  = $this->_minDate;
        $until = $this->_getDate($rules['UNTIL']);

        $dates = $this->_rruleByXXX($rules, $date);
        $this->addArray($dates);

        while ($date < $until) {
            $date = $this->_applyMethod($method, $rules['INTERVAL'], $date);
            if ($date < $until) {
                $dates = $this->_rruleByXXX($rules, $date);
            }
            $this->addArray($dates);
        }

        return true;
    }

    /**
     * Parse the RRULE of an iCal-file
     *
     * @param string $rrule RRULE to parse
     *
     * @return array Array containing the parsed rule
     */
    private function _parseRrule($rrule)
    {
        $rrule = explode(';', $rrule);
        $rules = array();

        // Needed to translate the Weekdays to a format compatible with Zend_Date
        $translateByDay = array(
            'MO' => 1,
            'TU' => 2,
            'WE' => 3,
            'TH' => 4,
            'FR' => 5,
            'SA' => 6,
            'SU' => 7
        );

        foreach ($rrule as $rule) {
            list($name, $value) = explode('=', $rule, 2);
            if ($value == '') {
                continue;
            }
            switch ($name) {
                case 'UNTIL':
                    $value = Phprojekt_Date_Converter::parseIsoDateTime($value);
                    $this->_maxDate = $value;
                    break;
                case 'BYDAY':
                    $value = explode(',', $value);
                    for ($i = 0; $i < count($value); $i++) {
                        $value[$i] = $translateByDay[$value[$i]];
                    }
                    break;
                case 'BYMONTH':
                case 'BYHOUR':
                case 'BYMINUTE':
                    $value = explode(',', $value);
                    break;
                case 'INTERVAL':
                    $value = (int) $value;
                    break;
            }
            $rules[$name] = $value;
        }

        if (!isset($rules['UNTIL'])) {
            $rules['UNTIL'] = $this->_minDate;
            $this->_maxDate = $this->_minDate;
        }

        return $rules;
    }

    /**
     * Calculate all Dates generated by a 'BYXXX' rule.
     *
     * @param array $rules Rrule as generated by _parseRrule
     * @param int   $date  Timestamp value of the date
     *
     * @return array Array with all the timestamp
     */
    private function _rruleByXXX($rules, $date)
    {
        $bys = array(
            'BYMONTH'    => 'setMonth',
            'BYWEEKNO'   => 'setWeek',
            'BYYEARDAY'  => 'setDayOfYear',
            'BYMONTHDAY' => 'setDay',
            'BYDAY'      => 'setWeekday',
            'BYHOUR'     => 'setHour',
            'BYMINUTE'   => 'setMinute',
            'BYSECOND'   => 'setSecond'
        );

        $dates = array($this->_getDate($date));

        foreach ($bys as $by => $setter) {
            if (isset($rules[$by])) {
                $res = array();
                foreach ($rules[$by] as $value) {
                    foreach ($dates as $date) {
                        $date->$setter($value);
                        $res[] = $this->_getDate($date);
                    }
                }
                $dates[] = $res;
            }
        }

        return $dates;
    }

    /**
     * Get the elements of the collection
     *
     * @return array Returns all the timestamp in an array
     */
    public function getValues()
    {
        ksort($this->_elements);

        return $this->_elements;
    }

    /**
     * Removes a series of dates from the collection
     *
     * @param array $exclude Array with Zend_Dates that should be removed from the collection
     */
    public function filter($exclude)
    {
        for ($dateIndex = 0; $dateIndex < count($this->_elements); $dateIndex++) {
            foreach ($exclude as $exDate) {
                if ($exDate->compare($this->_elements[$dateIndex]) == 0) {
                    unset($this->_elements[$dateIndex]);
                    continue;
                }
            }
        }
    }

    /**
     * Set the date in the Zend_Date and return it in date format
     *
     * @param int $date Timestamp of the date
     *
     * @return int The new timestamp
     */
    private function _getDate($date)
    {
        $this->_date->set($date);

        return $this->_date->get();
    }

    /**
     * Apply the rule method to one date
     *
     * @param string $method   The method name
     * @param int    $interval The interval
     * @param int    $date     Timestamp of the date
     *
     * @return int The new timestamp
     */
    private function _applyMethod($method, $interval, $date)
    {
        $this->_date->set($date);
        $this->_date->$method($interval);

        return $this->_date->get();
    }
}
