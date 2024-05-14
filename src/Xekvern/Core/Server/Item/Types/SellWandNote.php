<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\Block;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Utils\ClickableItem;

class SellWandNote extends ClickableItem {

    const SELLS = "Sells";

    /**
     * SellWandNote constructor.
     *
     * @param int $sells
     */
    public function __construct(int $sells) {
        $customName = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . number_format($sells) . " Sell Wand Uses";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Tap anywhere to claim sell wand uses.";
        parent::__construct(VanillaItems::PAPER(), $customName, $lore, [], 
        [
            self::SELLS => new IntTag($sells)
        ]);
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     *
     * @throws TranslatonException
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        $sells = $tag->getInt(self::SELLS);
        $player->playXpLevelUpSound();
        $player->getDataSession()->addToSellWandUses($sells);
        /** @var PlayerInventory $inventory */
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}