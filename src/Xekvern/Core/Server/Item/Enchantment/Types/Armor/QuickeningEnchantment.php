<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;

class QuickeningEnchantment extends Enchantment {

    /**
     * QuickeningEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Quickening", Rarity::UNCOMMON, "Obtain speed boost.", self::MOVE, ItemFlags::FEET, 4, VanillaEffects::SPEED());
        $this->callable = function(PlayerMoveEvent $event, int $level) { };
    }
}