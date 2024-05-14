<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;

class HopsEnchantment extends Enchantment {

    /**
     * HopsEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Hops", Rarity::UNCOMMON, "Obtain jump boost.", self::MOVE, ItemFlags::FEET, 5, VanillaEffects::JUMP_BOOST());
        $this->callable = function(PlayerMoveEvent $event, int $level) {
        };
    }
}