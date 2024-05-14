<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Mining;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;

class AmplifyEnchantment extends Enchantment {

    /**
     * AmplifyEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Amplify", Rarity::RARE, "Increase the amount of xp received by mining.", self::BREAK, ItemFlags::DIG, 5);
        $this->callable = function(BlockBreakEvent $event, int $level) {
            $event->setXpDropAmount((int)round($event->getXpDropAmount() * (1 + ($level * 0.5))));
        };
    }
}