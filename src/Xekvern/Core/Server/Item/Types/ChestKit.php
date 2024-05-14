<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Server\Kit\Kit;
use Xekvern\Core\Server\Kit\SacredKit;

class ChestKit extends ClickableItem {

    const KIT = "Kit";

    const TIER = "Tier";

    /**
     * ChestKit constructor.
     *
     * @param Kit $kit
     * @param int $tier
     */
    public function __construct(Kit $kit, int $tier = 1) {
        $customName = $kit->getColoredName() . TextFormat::RESET . TextFormat::GRAY . " Kit";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::WHITE .  "Rarity: " . TextFormat::BOLD . TextFormat::AQUA . Kit::rarityToString($kit->getRarity());
        if($kit instanceof SacredKit) {
            $lore[] = TextFormat::RESET . TextFormat::WHITE . "Tier: " . TextFormat::BOLD  . TextFormat::AQUA . ItemHandler::getRomanNumber($tier);    
        }
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Interact anywhere to redeem kit.";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "(Be sure to clear inventory!)";
        parent::__construct(VanillaBlocks::CHEST()->asItem(), $customName, $lore, [], [
            self::KIT => new StringTag($kit->getName()),
            self::TIER => new IntTag($tier)
        ]);
    }

    /**
     * @param NexusPlayer $player
     * @param Inventory $inventory
     * @param Item $item
     * @param CompoundTag $tag
     * @param int $face
     * @param Block $blockClicked
     */
    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        $kit = $tag->getString(ChestKit::KIT);
        $tier = $tag->getInt(ChestKit::TIER);
        $kit = $player->getCore()->getServerManager()->getKitHandler()->getKitByName($kit);
        $kit->giveTo($player, $tier);
        $player->getWorld()->addSound($player->getEyePos(), new \pocketmine\world\sound\BlazeShootSound());
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }
}