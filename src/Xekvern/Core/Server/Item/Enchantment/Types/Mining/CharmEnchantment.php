<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Mining;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;

class CharmEnchantment extends Enchantment {

    /**
     * CharmEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Charm", Rarity::MYTHIC, "Increase your chance of getting a lucky reward by mining a lucky block.", self::BREAK, ItemFlags::DIG, 10);
        $this->callable = function(BlockBreakEvent $event, int $level) {
        };
    }
}