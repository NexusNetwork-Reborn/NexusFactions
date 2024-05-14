<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Mining;

use pocketmine\block\BlockTypeIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\VanillaItems;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;

class SmeltingEnchantment extends Enchantment
{

    /**
     * SmeltingEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Smelting", Rarity::RARE, "Automatically turn a ore that you mine into their mineral forms.", self::BREAK, ItemFlags::DIG, 1);
        $this->callable = function (BlockBreakEvent $event, int $level) {
            $block = $event->getBlock();
            $player = $event->getPlayer();
            switch ($block->getTypeId()) {
                case BlockTypeIds::IRON_ORE:
                    $item = VanillaItems::IRON_INGOT();
                    break;
                case BlockTypeIds::GOLD_ORE:
                    $item = VanillaItems::GOLD_INGOT();
                    break;
                case BlockTypeIds::COPPER_ORE:
                    $item = VanillaItems::COPPER_INGOT();
                    break;
                default:
                    return;
            }
            $player->getInventory()->removeItem($block->asItem());
            $player->getInventory()->addItem($item);
        };
    }
}
