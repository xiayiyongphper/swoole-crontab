<?php

/**
 * Created by PhpStorm.
 * User: henryzhu
 * Date: 17-9-21
 * Time: 下午1:09
 */
class Pollard
{
    public function defaultPollard($n)
    {
        return $this->pollardRho($n, function ($x, $n) {
            return ($x * $x - 1) % $n;
        });
    }

    public function pollardRho($n, $f)
    {
        $x = 2;
        $y = 2;
        $d = 1;
        while ($d == 1) {
            $x = $f($x, $n);
            $y = $f($f($y, $n), $n);
            $d = $this->gcd(abs($x - $y), $n);
        }
        if ($d == $n) {
            return -1;
        }
        return $d;
    }

    public function gcd($a, $b)
    {
        if ($b == 0) {
            return $a;
        }
        return $this->gcd($b, $a % $b);
    }
}