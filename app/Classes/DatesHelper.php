<?php

namespace App\Classes;

use \DateTime;
use \Exception;

class DatesHelper
{
    /**
     * https://stackoverflow.com/questions/4312439/php-return-all-dates-between-two-dates-in-an-array.
     * @param $strDateFrom
     * @param $strDateTo
     * @return array
     */
    public static function createDateRangeArray($strDateFrom, $strDateTo)
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.

        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange = [];

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }

    /**
     * Returns true if given date is on a weekend, false otherwise.
     * @param $date
     * @return bool
     */
    public static function is_weekend($date) {

        // Get the day of the week (0 = Sunday, 6 = Saturday)
        $dayOfWeek = date('w', strtotime($date));

        // Check if the day is Saturday (6) or Sunday (0)
        return ($dayOfWeek == 0 || $dayOfWeek == 6);
    }


    /**
     * Returns a sample of dates, between a range of two dates ($date_from and $date_to). The method can return three
     * kinds of samples: (1) A sample where dates are spaced evenly with a predefined spacing, (2) a sample with a
     * fixed sample size where dates are evenly spaced, or (3) a sample with a fixed sample size where dates are picked
     * at random (a simple random sample). Usage examples:
     *
     * $sample = \App\Classes\DatesHelper::get_dates_sample("2022-01-01", "2023-12-31", ['spaced_days' => 10]);
     * $sample = \App\Classes\DatesHelper::get_dates_sample("2022-01-01", "2023-12-31", ['sample_size' => 24, "random" => false]);
     * $sample = \App\Classes\DatesHelper::get_dates_sample("2022-01-01", "2023-12-31", ['sample_size' => 24, "random" => true]);
 *
     * @param string $date_from The start date in 'Y-m-d' format.
     * @param string $date_to The end date in 'Y-m-d' format.
     * @param array $options An associative array of options. Possible keys are:
     *   'spaced_days': The number of days between each date in the output sample. Default is null.
     *   'sample_size': Desired sample size. If fraction is given, sample size is drawn as percentage of population.
     *   'random': Boolean indicating whether to return random dates. Default is false. (This only works for cases where you specify 'sample_size').
     * 
     * @return array An array of dates in 'Y-m-d' format.
     * @throws Exception
     */
    public static function get_dates_sample($date_from, $date_to, $options) {
        $dates = [];

        // Convert date strings to DateTime objects.
        $from = new DateTime($date_from);
        $to = new DateTime($date_to);

        // Calculate the interval between dates.
        $interval = $from->diff($to)->days;

        if (isset($options['spaced_days'])) {
            $spacing = $options['spaced_days'];
            for ($i = 0; $i <= $interval; $i += $spacing) {
                $dates[] = (clone $from)->modify("+$i days")->format('Y-m-d');
            }
        } elseif (isset($options['sample_size'])) {

            // Set sample_size.
            $sample_size = $options['sample_size'];
            if ($sample_size <= 1) {
                $sample_size = ceil($interval * $sample_size);
            }

            // Logically, sample size cannot be bigger than population. Throw Exception if sample is requested larger than population.
            if($sample_size > $interval) {
                throw new Exception("Cannot create a random sample (" . $sample_size . ") larger than the number of dates in the total set (" . $interval . ").");
            }

            $dates = [];
            if ($options['random'] ?? false) {
                $random_dates = [];
                while (count($random_dates) < $sample_size) {
                    $random_date = (clone $from)->modify('+' . mt_rand(0, $interval) . ' days')->format('Y-m-d');
                    if (!in_array($random_date, $random_dates)) {
                        $random_dates[] = $random_date;
                    }
                }
                $dates = $random_dates;
            } else {
                $spacing = floor($interval / $sample_size);
                $period = 0;
                for ($i = 0; $i < $sample_size; $i++) {
                    $dates[] = (clone $from)->modify("+$period days")->format('Y-m-d');
                    $period = $period + $spacing;
                }
            }
        }

        return $dates;
    }
}
