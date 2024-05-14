<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Utils;

use pocketmine\{
    nbt\tag\CompoundTag,
    nbt\tag\StringTag,
    nbt\tag\Tag,
    utils\TextFormat,
    item\Item,
    item\enchantment\EnchantmentInstance
};
use pocketmine\item\Durable;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;

class CustomItem {

    const CUSTOM = "custom";
    const ITEM_CLASS = "ItemClass";

    /** @var Item */
    private $item;
    /** @var string */
    private $customName;
    /** @var string[] */
    private $lore = [];
    /** @var EnchantmentInstance[] */
    private $enchants = [];
     /** @var Tag[] */
    private $tags = [];

    /**
     * CustomItem constructor.
     *
     * @param Item $item
     * @param string $customName
     * @param string[] $lore
     * @param EnchantmentInstance[] $enchants
     * @param Tag[] $tags
     * @param int $meta
     */
    public function __construct(Item $item, string $customName, array $lore = [], array $enchants = [], array $tags = [], int $meta = 0) {
        $this->item = $item;
        $this->customName = $customName;
        $this->lore = $lore;
        $this->enchants = $enchants;
        $this->tags = $tags;
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int {
        return 1;
    }   

    /**
     * @return Item
     */
    public function getItemForm(): Item {
        $item = $this->item;
        $item->setCustomName($this->customName);
        foreach($this->enchants as $enchantment) {
            $item->addEnchantment($enchantment);
        }
        $item->setLore($this->lore);
        $lore = $item->getLore();
        foreach($item->getEnchantments() as $enchantment) {
            if($enchantment->getType() instanceof Enchantment) {
                $lore[] = TextFormat::RESET . ItemHandler::rarityToColor($enchantment->getType()->getRarity()) . $enchantment->getType()->getName() . " " . ItemHandler::getRomanNumber($enchantment->getLevel());
            }
        }
        $tag = $item->getNamedTag();
        if($item instanceof Durable) {   // "Unknown CE Prevention"
            if($tag !== null) {
                if(isset($tag->getValue()[EnchantmentScroll::SCROLL_AMOUNT])) {
                    $amount = $tag->getInt(EnchantmentScroll::SCROLL_AMOUNT);
                    $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Enchantments: " . TextFormat::RESET . TextFormat::YELLOW . count($item->getEnchantments()) . "/" . $amount;
                } else {
                    if(count($item->getEnchantments()) < 1) {
                        $tag->setInt(EnchantmentScroll::SCROLL_AMOUNT, 1);
                        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Enchantments: " . TextFormat::RESET . TextFormat::YELLOW . count($item->getEnchantments()) . "/1";
                    } else {
                        $tag->setInt(EnchantmentScroll::SCROLL_AMOUNT, count($item->getEnchantments()));
                        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Enchantments: " . TextFormat::RESET . TextFormat::YELLOW . count($item->getEnchantments()) . "/" . count($item->getEnchantments());
                    }
                }
            } else {
                if(count($item->getEnchantments()) < 1) {
                    $tag->setInt(EnchantmentScroll::SCROLL_AMOUNT, 1);
                    $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Enchantments: " . TextFormat::RESET . TextFormat::YELLOW . count($item->getEnchantments()) . "/1";
                } else {
                    $tag->setInt(EnchantmentScroll::SCROLL_AMOUNT, count($item->getEnchantments()));
                    $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Enchantments: " . TextFormat::RESET . TextFormat::YELLOW . count($item->getEnchantments()) . "/" . count($item->getEnchantments());
                }
            }
        }
        $item->setLore($lore);
        $item->getNamedTag()->setTag(self::CUSTOM, new StringTag("Custom"));
        $compoundTag = $item->getNamedTag();
        $compoundTag->setTag(self::CUSTOM, new CompoundTag());
        if(!empty($this->tags)) {
            $compoundTag->setTag(self::CUSTOM, new StringTag(self::CUSTOM));
            $compoundTag->setString(self::ITEM_CLASS, get_class($this));
            $item->setNamedTag($compoundTag);
            foreach($this->tags as $name => $tag) {
                $compoundTag->setTag($name, $tag);
            }
        }
        $item->setNamedTag($compoundTag);
        return $item;
    }
}  