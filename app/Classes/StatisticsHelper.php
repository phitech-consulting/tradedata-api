<?php

namespace app\Classes;

class StatisticsHelper
{


    /**
     * Calculate the softmax value for an array of values. (Ab)used in this application for getting a measure of how
     * different two values are, in order to perform an automatic quality check. See: \app\Classes\QualityCheck.php.
     * https://stats.stackexchange.com/questions/643321/getting-relative-measure-of-difference-between-two-numbers-reference-close-to
     * https://github.com/raymondjplante
     * @param array $v
     * @return array
     */
    public static function softmax(array $v){

        // Just in case values are passed in as string, apply floatval
        $v = array_map('exp',array_map('floatval',$v));
        $sum = array_sum($v);

        foreach($v as $index => $value) {
            $v[$index] = $value/$sum;
        }

        return $v;
    }
}
