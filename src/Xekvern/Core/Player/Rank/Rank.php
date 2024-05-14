<?php

declare(strict_types=1);

namespace Xekvern\Core\Player\Rank;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Nexus;

class Rank
{

    const PLAYER = 0;

    const SUBORDINATE = 1;

    const KNIGHT = 2;

    const HOPLITE = 3;

    const PRINCE = 4;

    const TITAN = 5;

    const SPARTAN = 6;

    const KING = 7;

    const DEITY = 8;

    const TRIAL_MODERATOR = 9;

    const MODERATOR = 10;

    const SENIOR_MODERATOR = 11;

    const ADMIN = 12;

    const SENIOR_ADMIN = 13;

    const MANAGER = 14;

    const OWNER = 15;

    const YOUTUBER = 16;

    const FAMOUS = 17;

    const BUILDER = 18;

    const SENIOR_BUILDER = 19;

    const INVESTOR = 21;

    const   COOWNER = 22;

    /** @var string */
    private $name;

    /** @var string */
    private $coloredName;

    /** @var int */
    private $identifier;

    /** @var string */
    private $chatFormat;

    /** @var string */
    private $tagFormat;

    /** @var array */
    private $permissions;

    /** @var int */
    private $homes;

    /** @var int */
    private $vaults;

    /** @var int */
    private $auctionLimit;

    /** @var string */
    private $chatColor;

    /**
     * Rank constructor.
     *
     * @param string $name
     * @param string $chatColor
     * @param string $coloredName
     * @param int $identifier
     * @param string $chatFormat
     * @param string $tagFormat
     * @param int $homes
     * @param int $vaults
     * @param int $auctionLimit
     * @param array $permissions
     */
    public function __construct(string $name, string $chatColor, string $coloredName, int $identifier, string $chatFormat, string $tagFormat, int $homes, int $vaults, int $auctionLimit, array $permissions = [])
    {
        $this->name = $name;
        $this->chatColor = $chatColor;
        $this->coloredName = $coloredName;
        $this->identifier = $identifier;
        $this->chatFormat = $chatFormat;
        $this->tagFormat = $tagFormat;
        $this->homes = $homes;
        $this->vaults = $vaults;
        $this->auctionLimit = $auctionLimit;
        $this->permissions = $permissions;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getColoredName(): string
    {
        return $this->coloredName;
    }

    /**
     * @return string
     */
    public function getChatColor(): string
    {
        return $this->chatColor;
    }

    /**
     * @return int
     */
    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @param NexusPlayer $player
     * @param string $message
     * @param array $args
     *
     * @return string
     */
    public function getChatFormatFor(NexusPlayer $player, string $message, array $args = []): string
    {
        $format = $this->chatFormat;
        foreach ($args as $arg => $value) {
            if (is_int($value)) {
                $value = (string)$value;
            }
            $format = str_replace("{" . $arg . "}", $value, $format);
        }
        $format = str_replace("{player}", TextFormat::RESET . TextFormat::WHITE . $player->getDisplayName(), $format);
        if ($player->getDataSession()->getRank()->getName() === "CoOwner") {
            $message = $this->applyGradientAmooora($message);
            return str_replace("{message}", $message, $format);
        } else {
            return str_replace("{message}", $message, $format);
        }
    }

    public function applyGradientAmooora(string $input): string
    {
        $colors = ["§9", "§3", "§b"];
        $partLength = (int)ceil(strlen($input) / 3);
        $parts = str_split($input, $partLength);
        $rainbowed = "";
        foreach ($parts as $index => $part) {
            $rainbowed .= $colors[$index] . $part;
        }
        return $rainbowed;
    }

    /**
     * @param NexusPlayer $player
     * @param array $args
     *
     * @return string
     */
    public function getTagFormatFor(NexusPlayer $player, array $args = []): string
    {
        $format = $this->tagFormat;
        foreach ($args as $arg => $value) {
            $format = str_replace("{" . strval($arg) . "}", strval($value), $format);
        }
        return str_replace("{player}", $player->getDataSession()->getCurrentTag() . TextFormat::RESET . TextFormat::WHITE . " " . $player->getDisplayName(), $format);
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return int
     */
    public function getHomeLimit(): int
    {
        return $this->homes;
    }

    /**
     * @return int
     */
    public function getVaultsLimit(): int
    {
        return $this->vaults;
    }

    /**
     * @return int
     */
    public function getAuctionLimit(): int
    {
        return $this->auctionLimit;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
