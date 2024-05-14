<?php

namespace Xekvern\Core\Utils;

use pocketmine\math\Vector3;

class MathUtils
{

    /**
     * Gets the circumference for a given radius
     *
     * @param float $radius - Radius to calculate
     *
     * @return float
     */
    public static function circumference(float $radius): float
    {
        return (pi() * ($radius * 2));
    }

    /**
     * Gets the radius from a circumference
     *
     * @param float $circumference - The circumference to calculate
     *
     * @return float
     */
    public static function radiusFromCircumference(float $circumference): float
    {
        return ($circumference / (pi() * 2));
    }

    /**
     * Gets the total fall distance
     *
     * @param Vector3 $from - Vector position 1
     * @param Vector3 $to - Vector position 2
     *
     * @return float - Total fall distance
     */
    public static function getFallDistance(Vector3 $from, Vector3 $to): float
    {
        $from = clone $from;
        $to = clone $to;
        $fall = $from->y;
        $fall2 = $to->y;
        return ($fall - $fall2);
    }

    /**
     * Gets the distance from two points on a circle
     *
     * @param int $a - First position
     * @param int $b - Second position
     *
     * @return int - The distance
     */
    public static function getDifferenceFrom360(int $a, int $b): int
    {
        $diff = abs($a - $b);
        return ($diff > 180) ? (360 - $diff) : $diff;
    }
}
