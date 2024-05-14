<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\entity\effect\VanillaEffects;

class PerceptionEnchantment extends Enchantment {

    /**
     * PerceptionEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Perception", Rarity::COMMON, "Obtain night vision.", self::MOVE, ItemFlags::HEAD, 1, VanillaEffects::NIGHT_VISION());
        $this->callable = function(PlayerMoveEvent $event, int $level) { };
    }
}