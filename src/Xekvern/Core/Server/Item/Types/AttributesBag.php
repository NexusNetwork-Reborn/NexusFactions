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

class AttributesBag extends ClickableItem {

    const ATTRIBUTE_BAG = "AttributesBag";

    /**
     * AttributesBag constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "Attributes Bag";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Use this item to obtain a random attribute.";
        parent::__construct(VanillaBlocks::ENDER_CHEST()->asItem(), $customName, $lore, [], [
            self::ATTRIBUTE_BAG => new StringTag(self::ATTRIBUTE_BAG),
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
        $item = (new AttributeShard(random_int(0,4)))->getItemForm()->setCount(1);
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