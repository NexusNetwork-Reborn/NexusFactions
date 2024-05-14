<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Types\Soul;
class Head extends ClickableItem {

    const PLAYER = "Player";

    /**
     * Head constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $customName = TextFormat::RESET . TextFormat::DARK_RED . TextFormat::BOLD . "{$player->getName()}'s Head";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Interact to receive an amount of power from this head.";
        parent::__construct(VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadType::PLAYER())->asItem(), $customName, $lore, [], [
            self::PLAYER => new StringTag($player->getUniqueId()->toString()),
            "UniqueId" => new StringTag(uniqid())
        ], 3);
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
        $item = (new PowerNote(mt_rand(3, 5)))->getItemForm();
        if($inventory->canAddItem($item)) {
            $inventory->addItem($item);
        } else {
            $player->getWorld()->dropItem($player->getPosition(), $item);
        }
        /** @var PlayerInventory $inventory */
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}