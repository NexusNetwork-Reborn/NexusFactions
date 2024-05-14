<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit\Types\Sacred;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\CreeperEgg;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentRemover;
use Xekvern\Core\Server\Item\Types\TNTLauncher;
use Xekvern\Core\Server\Item\Types\XPNote;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Kit\SacredKit;

class Raider extends SacredKit
{

    /**
     * Raider constructor.
     */
    public function __construct()
    {
        $items =  [
            VanillaBlocks::TNT()->asItem()->setCount(64),
            VanillaBlocks::TNT()->asItem()->setCount(64),
            VanillaBlocks::TNT()->asItem()->setCount(64),
            VanillaBlocks::TNT()->asItem()->setCount(64),
            (new CreeperEgg())->getItemForm()->setCount(32)
        ];
        parent::__construct(3, "Raider", self::MYTHIC, $items, 345600);
    }

    /**
     * @param NexusPlayer $player
     * @param int $tier
     */
    public function giveTo(NexusPlayer $player, int $tier = 1): void {
        $items = [];
        for ($i = 1; $i <= $tier; $i++) {
            $items = array_merge($items, [
                VanillaBlocks::TNT()->asItem()->setCount(64),
                VanillaBlocks::TNT()->asItem()->setCount(64),
                VanillaBlocks::TNT()->asItem()->setCount(64),
                VanillaBlocks::TNT()->asItem()->setCount(64),
                (new CreeperEgg())->getItemForm()->setCount(32)
            ]);
        }
        $items[] = (new TNTLauncher($tier, mt_rand(5, 25) * 5, "TNT", "Mid"))->getItemForm();
        foreach ($items as $item) {
            if ($item instanceof CustomItem) {
                $item = $item->getItemForm();
            }
            if ($player->getInventory()->canAddItem($item)) {
                $player->getInventory()->addItem($item);
            } else {
                $player->getWorld()->dropItem($player->getPosition(), $item);
            }
        }
    }

    /**
     * @return string
     */
    public function getColoredName(): string
    {
        return TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_PURPLE . "Raider" . TextFormat::RESET;
    }

}