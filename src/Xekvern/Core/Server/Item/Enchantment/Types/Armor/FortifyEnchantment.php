<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;

class FortifyEnchantment extends Enchantment
{

    /**
     * FortifyEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Fortify", Rarity::RARE, "Obtain regeneration.", self::MOVE, ItemFlags::TORSO, 2, VanillaEffects::REGENERATION());
        $this->callable = function (PlayerMoveEvent $event, int $level) { };
    }
}
