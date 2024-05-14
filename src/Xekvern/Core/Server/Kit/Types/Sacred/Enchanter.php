<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Kit\Types\Sacred;

use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentRemover;
use Xekvern\Core\Server\Item\Types\XPNote;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Kit\SacredKit;

class Enchanter extends SacredKit
{

    /**
     * Enchanter constructor.
     */
    public function __construct()
    {
        $items =  [
            (new EnchantmentBook(ItemHandler::getRandomEnchantment(), mt_rand(1, 100)))->getItemForm(),
            (new EnchantmentBook(ItemHandler::getRandomEnchantment(), mt_rand(1, 100)))->getItemForm(),
            (new EnchantmentBook(ItemHandler::getRandomEnchantment(), mt_rand(1, 100)))->getItemForm(),
            (new EnchantmentRemover(100))->getItemForm(),
            (new XPNote(25000))->getItemForm()
        ];
        parent::__construct(4, "Enchanter", self::MYTHIC, $items, 345600);
    }

    /**
     * @param NexusPlayer $player
     * @param int $tier
     */
    public function giveTo(NexusPlayer $player, int $tier = 1): void {
        $items = [];
        for($i = 1; $i <= $tier; $i++) {
            $items = array_merge($items, [
                (new EnchantmentBook(ItemHandler::getRandomEnchantment(), mt_rand(1, 100)))->getItemForm(),
                (new EnchantmentBook(ItemHandler::getRandomEnchantment(), mt_rand(1, 100)))->getItemForm(),
                (new EnchantmentBook(ItemHandler::getRandomEnchantment(), mt_rand(1, 100)))->getItemForm(),
                (new EnchantmentRemover(100))->getItemForm(),
                (new XPNote(25000))->getItemForm()
            ]);
        }
        foreach($items as $item) {
            if($item instanceof CustomItem) {
                $item = $item->getItemForm();
            }
            if($player->getInventory()->canAddItem($item)) {
                $player->getInventory()->addItem($item);
            }
            else {
                $player->getWorld()->dropItem($player->getEyePos(), $item);
            }
        }
    }

    /**
     * @return string
     */
    public function getColoredName(): string
    {
        return TextFormat::RESET . TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "Enchanter" . TextFormat::RESET;
    }

}