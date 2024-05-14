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
use pocketmine\world\sound\ChestCloseSound;
use Xekvern\Core\Server\World\Utils\GeneratorId;
use Xekvern\Core\Server\World\WorldHandler;

class GeneratorsBag extends ClickableItem {

    const GENERATORS_BAG = "GeneratorsBag";

    /**
     * GeneratorsBag constructor.
     */
    public function __construct() {
        $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Random Generator Bag";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Use this item to obtain a random ore generator.";
        parent::__construct(VanillaBlocks::ENDER_CHEST()->asItem(), $customName, $lore, [], [
            self::GENERATORS_BAG => new StringTag(self::GENERATORS_BAG),
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
        $rewards = [
            (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::COAL, true)))->getItemForm(),
            (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::LAPIS_LAZULI, false)))->getItemForm(),
            (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::IRON, true)))->getItemForm(),
            (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::DIAMOND, true)))->getItemForm(),
            (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::EMERALD, true)))->getItemForm(),
            (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::DIAMOND, false)))->getItemForm(),
            (new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::EMERALD, false)))->getItemForm(),
        ];
        $item = $rewards[array_rand($rewards)];
        if($player->getInventory()->canAddItem($item)) {
            $player->getInventory()->addItem($item);
        }
        else {
            $player->getWorld()->dropItem($player->getPosition(), $item);
        }
        $player->broadcastSound(new ChestCloseSound(), [$player]);
        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int {
        return 1;
    }   
}