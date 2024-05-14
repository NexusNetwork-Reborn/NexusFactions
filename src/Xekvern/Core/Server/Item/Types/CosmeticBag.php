<?php

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\utils\TextFormat;

class CosmeticBag extends ClickableItem {

    const COSMETIC_BAG = "CosmeticBag";

    /**
     * CosmeticBag constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::BLUE . "Cosmetic Bag";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Use this item to obtain a random cosmetic.";
        parent::__construct(VanillaBlocks::CHEST()->asItem(), $customName, $lore, [], [
            self::COSMETIC_BAG => new StringTag(self::COSMETIC_BAG),
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
        $ps = $blockClicked->getPosition();
        $item = (new CosmeticShard(random_int(0,1), random_int(0,22)))->getItemForm()->setCount(1);
        if($player->getInventory()->canAddItem($item)) {
            $player->getInventory()->addItem($item);
        }
        else {
            $player->getWorld()->dropItem($player->getPosition(), $item);
        }
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int {
        return 1;
    }   
}