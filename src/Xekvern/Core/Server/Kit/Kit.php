<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;
use Xekvern\Core\Server\Item\Utils\CustomItem;

abstract class Kit {

    const COMMON = 1;

    const UNCOMMON = 2;

    const RARE = 3;

    const EPIC = 4;

    const LEGENDARY = 5;

    const MYTHIC = 6;

    /** @var string */
    protected $name;

    /** @var Item[] */
    protected $items;

    /** @var int */
    protected $rarity;

    /** @var int */
    protected $cooldown;

    /**
     * Kit constructor.
     *
     * @param string $name
     * @param int $rarity
     * @param array $items
     * @param int $cooldown
     */
    public function __construct(string $name, int $rarity, array $items, int $cooldown) {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->items = $items;
        $this->cooldown = $cooldown;
    }

    /**
     * @param NexusPlayer $player
     * @param int $tier
     */
    public function giveTo(NexusPlayer $player, int $tier = 1): void {
        $maxTier = 1;
        if($this instanceof SacredKit) {
            $maxTier = $this->getMaxTier();
        }
        foreach($this->items as $item) {
            $item = clone $item;
            if($item->hasEnchantments() and $tier > 0 and $maxTier > 1) {
                $enchantments = $item->getEnchantments();
                foreach($enchantments as $enchantment) {
                    if($tier < $maxTier) {
                        $level = $enchantment->getLevel() * $tier;
                        if($level > $enchantment->getType()->getMaxLevel()) {
                            $level = $enchantment->getType()->getMaxLevel();
                        }
                        if(($maxTier - $tier) > 1) {
                            $maxLevel = $enchantment->getLevel() * ($tier + 1);
                        }
                        else {
                            $maxLevel = $enchantment->getLevel() * $maxTier;
                        }
                        if($maxLevel > $enchantment->getType()->getMaxLevel()) {
                            $maxLevel = $enchantment->getType()->getMaxLevel();
                        }
                        if($level < $maxLevel) {
                            $level = mt_rand($level, $maxLevel);
                        }
                    }
                    else {
                        $level = $enchantment->getLevel() * $tier;
                        if($level > $enchantment->getType()->getMaxLevel()) {
                            $level = $enchantment->getType()->getMaxLevel();
                        }
                    }
                    $enchantment = new EnchantmentInstance($enchantment->getType(), $level);
                    $item->addEnchantment($enchantment);
                }
                $tag = $item->getNamedTag();
                if(!isset($tag->getValue()[EnchantmentScroll::SCROLL_AMOUNT])) {
                    $tag->setInt(EnchantmentScroll::SCROLL_AMOUNT, 15);
                }
                $item = (new CustomItem($item, $item->getCustomName(), [], $item->getEnchantments()))->getItemForm();
            }
            if($item instanceof CustomItem) {
                $item = $item->getItemForm();
            }
            if($item->getTypeId() === ItemTypeIds::SPLASH_POTION) {
                $pot = clone $item;
                $pot->setCount(1);
                for($i = 1; $i <= $item->getCount(); $i++) {
                    if($player->getInventory()->canAddItem($pot)) {
                        $player->getInventory()->addItem($pot);
                    }
                    else {
                        $player->getWorld()->dropItem($player->getPosition(), $pot);
                    }
                }
                continue;
            }
            if($player->getInventory()->canAddItem($item)) {
                $player->getInventory()->addItem($item);
            }
            else {
                if($item->getCount() > 64) {
                    $item->setCount(64);
                }
                $player->getWorld()->dropItem($player->getPosition(), $item);
            }
        }
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getRarity(): int {
        return $this->rarity;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getCooldown(): int {
        return $this->cooldown;
    }

    /**
     * @param int $rarity
     *
     * @return string
     */
    public static function rarityToString(int $rarity): string {
        switch($rarity) {
            case self::COMMON:
                return "Common";
                break;
            case self::UNCOMMON:
                return "Uncommon";
                break;
            case self::RARE:
                return "Rare";
                break;
            case self::EPIC:
                return "Epic";
                break;
            case self::LEGENDARY:
                return "Legendary";
                break;
            case self::MYTHIC:
                return "Mythic";
                break;
            default:
                return "Unknown";
                break;
        }
    }

    /**
     * @return string
     */
    abstract public function getColoredName(): string;
}