<?php

declare(strict_types=1);

namespace Xekvern\Core\Utils;

use DateTime;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use TypeError;
use Xekvern\Core\NexusException;

class Utils
{

    const N = 'N';

    const NE = '/';

    const E = 'E';

    const SE = '\\';

    const S = 'S';

    const SW = '/';

    const W = 'W';

    const NW = '\\';

    const HEX_SYMBOL = "e29688";

    /**
     * @param int $degrees
     *
     * @return string|null
     */
    public static function getCompassPointForDirection(int $degrees): ?string
    {
        $degrees = ($degrees - 180) % 360;
        if ($degrees < 0) {
            $degrees += 360;
        }
        if (0 <= $degrees and $degrees < 22.5) {
            return self::N;
        }
        if (22.5 <= $degrees and $degrees < 67.5) {
            return self::NE;
        }
        if (67.5 <= $degrees and $degrees < 112.5) {
            return self::E;
        }
        if (112.5 <= $degrees and $degrees < 157.5) {
            return self::SE;
        }
        if (157.5 <= $degrees and $degrees < 202.5) {
            return self::S;
        }
        if (202.5 <= $degrees and $degrees < 247.5) {
            return self::SW;
        }
        if (247.5 <= $degrees and $degrees < 292.5) {
            return self::W;
        }
        if (292.5 <= $degrees and $degrees < 337.5) {
            return self::NW;
        }
        if (337.5 <= $degrees and $degrees < 360.0) {
            return self::N;
        }
        return null;
    }

    /**
     * @param string $time
     *
     * @return int|null
     */
    public static function stringToTime(string $time): ?int
    {
        $array = str_split($time);
        if (count($array) % 2 === 0) {
            return null;
        }
        $newTime = 0;
        for ($i = 0; $i < count($array); $i++) {
            if ($i % 2 === 0) {
                if (!is_numeric($array[$i])) {
                    return null;
                }
                $value = (int)$array[$i];
                switch (strtolower($array[$i + 1])) {
                    case "s":
                        $newTime += $value;
                        break;
                    case "h":
                        $newTime += ($value * 3600);
                        break;
                    case "d":
                        $newTime += ($value * 86400);
                        break;
                    case "w":
                        $newTime += ($value * 604800);
                        break;
                    case "m":
                        $newTime += ($value * 2419200);
                        break;
                }
            }
        }
        return $newTime;
    }

    /**
     * @param string $text
     * @param int $length
     * @param int $time
     *
     * @return string
     */
    public static function scrollText(string $text, int $length, int $time = null): string
    {
        if ($time === null) {
            $time = time();
        }
        $start = ($time % strlen($text));
        $newText = substr($text, -$start, $length);
        if ($start < $length) {
            $newText .= substr($text, 0, $length - strlen($newText));
        }
        return $newText;
    }

    /**
     * @param float $deg
     *
     * @return string
     */
    public static function getCompassDirection(float $deg): string
    {
        $deg %= 360;
        if ($deg < 0) {
            $deg += 360;
        }
        if (22.5 <= $deg and $deg < 67.5) {
            return "NW";
        } elseif (67.5 <= $deg and $deg < 112.5) {
            return "N";
        } elseif (112.5 <= $deg and $deg < 157.5) {
            return "NE";
        } elseif (157.5 <= $deg and $deg < 202.5) {
            return "E";
        } elseif (202.5 <= $deg and $deg < 247.5) {
            return "SW";
        } elseif (247.5 <= $deg and $deg < 292.5) {
            return "S";
        } elseif (292.5 <= $deg and $deg < 337.5) {
            return "SW";
        } else {
            return "W";
        }
    }

    /**
     * @return string
     */
    public static function getMapBlock(): string
    {
        return hex2bin(self::HEX_SYMBOL);
    }

    /**
     * @param int $degrees
     * @param string $colorActive
     * @param string $colorDefault
     *
     * @return array
     */
    public static function getASCIICompass(int $degrees, string $colorActive, string $colorDefault): array
    {
        $ret = [];
        $point = self::getCompassPointForDirection($degrees);
        $row = "";
        $row .= ($point === self::NW ? $colorActive : $colorDefault) . self::NW;
        $row .= ($point === self::N ? $colorActive : $colorDefault) . self::N;
        $row .= ($point === self::NE ? $colorActive : $colorDefault) . self::NE;
        $ret[] = $row;
        $row = "";
        $row .= ($point === self::W ? $colorActive : $colorDefault) . self::W;
        $row .= $colorDefault . "+";
        $row .= ($point === self::E ? $colorActive : $colorDefault) . self::E;
        $ret[] = $row;
        $row = "";
        $row .= ($point === self::SW ? $colorActive : $colorDefault) . self::SW;
        $row .= ($point === self::S ? $colorActive : $colorDefault) . self::S;
        $row .= ($point === self::SE ? $colorActive : $colorDefault) . self::SE;
        $ret[] = $row;
        return $ret;
    }

    /**
     * Check if classes in an array are a block of class
     *
     * @param array $array
     * @param string $class
     *
     * @return bool
     *
     * @throws TypeError
     */
    public static function validateObjectArray(array $array, string $class): bool
    {
        foreach ($array as $key => $item) {
            if (!($item instanceof $class)) {
                throw new TypeError("Element \"$key\" is not an instance of $class");
            }
        }
        return true;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getSkinDataFromPNG(string $path): string
    {
        $image = imagecreatefrompng($path);
        $data = "";
        for ($y = 0, $height = imagesy($image); $y < $height; $y++) {
            for ($x = 0, $width = imagesx($image); $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $data .= pack("c", ($color >> 16) & 0xFF)
                    . pack("c", ($color >> 8) & 0xFF)
                    . pack("c", $color & 0xFF)
                    . pack("c", 255 - (($color & 0x7F000000) >> 23));
            }
        }
        return $data;
    }

    /**
     * @param string $skinData
     *
     * @return Skin
     */
    public static function createSkin(string $skinData)
    {
        return new Skin("Standard_Custom", $skinData, "", "geometry.humanoid.custom");
    }

    /**
     * @param string $message
     *
     * @return int
     */
    public static function colorCount(string $message): int
    {
        $colors = "abcdef0123456789lo";
        $colors_ = str_split($colors);
        $count = 0;
        for ($i = 0; $i < count($colors_); $i++) {
            $count += substr_count($message, "§" . $colors_[$i]);
        }
        return $count;
    }

    /**
     * @param int $n
     * @param int $precision
     *
     * @return string
     */
    public static function shrinkNumber(int $n, int $precision = 1): string
    {
        if ($n < 900) {
            $n_format = number_format($n, $precision);
            $suffix = '';
        } else {
            if ($n < 900000) {
                $n_format = number_format($n / 1000, $precision);
                $suffix = 'K';
            } else {
                if ($n < 900000000) {
                    $n_format = number_format($n / 1000000, $precision);
                    $suffix = 'M';
                } else {
                    if ($n < 900000000000) {
                        $n_format = number_format($n / 1000000000, $precision);
                        $suffix = 'B';
                    } else {
                        $n_format = number_format($n / 1000000000000, $precision);
                        $suffix = 'T';
                    }
                }
            }
        }
        if ($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }
        return $n_format . $suffix;
    }

    /**
     * @param int $seconds
     *
     * @return string
     */
    public static function secondsToTime(int $seconds): string
    {
        $dtF = new DateTime('@0');
        $dtT = new DateTime("@$seconds");
        $hours = floor($seconds / 3600);
        if ($hours >= 24) {
            return $dtF->diff($dtT)->format('%ad %hh %im %ss');
        }
        if ($hours >= 1) {
            return $dtF->diff($dtT)->format('%hh %im %ss');
        }
        return $dtF->diff($dtT)->format('%im %ss');
    }

    /**
     * @param int $int
     *
     * @return string
     */
    public static function secondsToCD(int $int): string
    {
        $m = floor($int / 60);
        $s = floor($int % 60);
        return (($m < 10 ? "0" : "") . $m . ":" . ($s < 10 ? "0" : "") . $s);
    }

    /**
     * @param array $array
     *
     * @return string
     * @throws NexusException
     */
    public static function encodeArray(array $array): string
    {
        $parts = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                throw new NexusException("Unable to encode a multi-dimensional array.");
            }
            $parts[] = "$key:$value";
        }
        return implode(",", $parts);
    }

    /**
     * @param string $encryption
     *
     * @return array
     */
    public static function decodeArray(string $encryption): array
    {
        if (empty($encryption)) {
            return [];
        }
        $array = [];
        foreach (explode(",", $encryption) as $section) {
            $parts = explode(":", $section);
            if (!isset($parts[0]) or !isset($parts[1])) {
                continue;
            }
            $array[$parts[0]] = is_numeric($parts[1]) ? (int)$parts[1] : (string)$parts[1];
        }
        return $array;
    }

    /**
     * @param bool[] $array
     *
     * @return string
     * @throws NexusException
     */
    public static function encodeBoolArray(array $array): string
    {
        $parts = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                throw new NexusException("Unable to encode a multi-dimensional array.");
            }
            $value = (int)$value;
            $parts[] = "$key:$value";
        }
        return implode(",", $parts);
    }

    /**
     * @param string $encryption
     *
     * @return array
     */
    public static function decodeBoolArray(string $encryption): array
    {
        if (empty($encryption)) {
            return [];
        }
        $array = [];
        foreach (explode(",", $encryption) as $section) {
            $parts = explode(":", $section);
            if (!isset($parts[0]) or !isset($parts[1])) {
                continue;
            }
            $array[$parts[0]] = (bool)$parts[1];
        }
        return $array;
    }

    /**
     * @param string $color
     * @param string $text
     * @param string $endingColor
     *
     * @return string
     */
    public static function createPrefix(string $color, string $text, string $endingColor = TextFormat::GRAY): string
    {
        return TextFormat::DARK_GRAY . "[" . TextFormat::BOLD . $color . $text . TextFormat::RESET . TextFormat::DARK_GRAY . "] " . $endingColor;
    }


    public static function registerSimpleItem(string $id, Item $item, array $stringToItemParserNames): void
    {
        GlobalItemDataHandlers::getDeserializer()->map($id, fn () => clone $item);
        GlobalItemDataHandlers::getSerializer()->map($item, fn () => new SavedItemData($id));
        foreach ($stringToItemParserNames as $name) {
            StringToItemParser::getInstance()->register($name, fn () => clone $item);
        }
    }

    public static function registerSimpleBlock(string $id, Block $block, array $stringToItemParserNames): void
    {
        RuntimeBlockStateRegistry::getInstance()->register($block);

        GlobalBlockStateHandlers::getDeserializer()->mapSimple($id, fn () => clone $block);
        GlobalBlockStateHandlers::getSerializer()->mapSimple($block, $id);

        array_map(function ($name) use ($block) {
            StringToItemParser::getInstance()->override($name, fn () => clone $block->asItem());
        }, $stringToItemParserNames);
    }

    public static function addEffect(Player $player, int $id, int $seconds, int $amp = 1): void
    {
        $effFromId = EffectIdMap::getInstance()->fromId($id);
        $player->getEffects()->add(new EffectInstance($effFromId, 20 * $seconds, $amp));
    }

    /**
     * @param string $text
     * @param int $length
     *
     * @return string
     */
    public static function centerAlignText(string $text, int $length): string
    {
        $textLength = strlen(TextFormat::clean($text));
        $length -= $textLength;
        $times = (int)floor($length / 2);
        $times = $times > 0 ? $times : 1;
        return str_repeat(" ", $times) . $text . str_repeat(" ", $times);
    }

    /**
     * @param int $min
     * @param int $max
     * @param int $rarityFactor
     * 
     * @return int
     */
    public static function weighted_mt_rand(int $min, int $max, int $rarityFactor = 2): int
    {
        return (int) round($min + pow(mt_rand() / mt_getrandmax(), $rarityFactor) * ($max - $min));
    }

    /**
     * @param int $ping
     * 
     * @return string
     */
    public static function formatPing(int $ping): string {
        if ($ping >= 0 && $ping <= 100) {
            return TextFormat::GREEN . $ping;
        } elseif ($ping >= 101 && $ping <= 250) {
            return TextFormat::YELLOW . $ping;
        } else {
            return TextFormat::RED . $ping;
        }
    }
}
