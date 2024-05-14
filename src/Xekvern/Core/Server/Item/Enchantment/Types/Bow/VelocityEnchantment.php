<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Bow;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;

class VelocityEnchantment extends Enchantment {

    /**
     * VelocityEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Velocity", Rarity::RARE, "Increase the speed of your arrow and make it travel straighter.", self::SHOOT, ItemFlags::BOW, 10);
        $this->callable = function(EntityShootBowEvent $event, int $level) {
            $event->setForce($event->getForce() + (1 + $level));
            return;
        };
    }
}
