<?php

namespace Xekvern\Core\Server\Item\Types;

use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Server\Item\Utils\ClickableItem;

class GeneratorBucket extends ClickableItem {

    const BLOCK_TYPE = "BlockType";

    const TYPES = [
        BlockTypeIds::COBBLESTONE => "Cobblestone",
        BlockTypeIds::OBSIDIAN => "Obsidian",
        BlockTypeIds::BEDROCK => "Bedrock",
        BlockTypeIds::WATER => "Water",
        BlockTypeIds::LAVA => "Lava"
    ];

    const PRICES = [
        BlockTypeIds::COBBLESTONE => 50000,
        BlockTypeIds::OBSIDIAN => 250000,
        BlockTypeIds::BEDROCK => 5000000,
        BlockTypeIds::WATER => 5000000,
        BlockTypeIds::LAVA => 15000000
    ];

    /**
     * GeneratorBucket constructor.
     *
     * @param int $genBlock
     */
    public function __construct(int $genBlock) {
        $customName = TextFormat::RESET . TextFormat::YELLOW . TextFormat::BOLD . self::TYPES[$genBlock] . " Generator Bucket";
        $lore = [];
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Tap anywhere to use generator for " . TextFormat::BOLD . TextFormat::GREEN . "$" . number_format(self::PRICES[$genBlock]) . TextFormat::RESET . TextFormat::GRAY . " per use.";
        parent::__construct(VanillaItems::BUCKET(), $customName, $lore, 
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], 
        [
            self::BLOCK_TYPE => new IntTag($genBlock)
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
        $type = $tag->getInt(GeneratorBucket::BLOCK_TYPE);
        $price = self::PRICES[$type];
        if(!$player->isLoaded()) {
            return;
        }
        if($price > $player->getDataSession()->getBalance()) {
            $player->sendMessage(Translation::getMessage("notEnoughMoney"));
            return;
        }
        $x = $blockClicked->getPosition()->getFloorX();
        $y = $blockClicked->getPosition()->getFloorY();
        $z = $blockClicked->getPosition()->getFloorZ();
        switch($face) {
            case 2:
                $z--;
                break;
            case 3:
                $z++;
                break;
            case 4:
                $x--;
                break;
            case 5:
                $x++;
                break;
            case 1:
                $y++;
                break;
            case 0:
                $y--;
                break;
        }
        switch($type) {
            case BlockTypeIds::COBBLESTONE:
                $block = VanillaBlocks::COBBLESTONE();
                break;
            case BlockTypeIds::OBSIDIAN:
                $block = VanillaBlocks::OBSIDIAN();
                break;
            case BlockTypeIds::BEDROCK:
                $block = VanillaBlocks::BEDROCK();
                break;
            case BlockTypeIds::WATER:
                $block = VanillaBlocks::WATER();
                break;
            case BlockTypeIds::LAVA:
                $block = VanillaBlocks::LAVA();
                break;
        }
        $level = $player->getWorld();
        if($level === null) {
            return;
        }
        if($level->getDisplayName() !== Faction::CLAIM_WORLD) {
            return;
        }
        $player->getDataSession()->subtractFromBalance($price);
        while($y > 2) {
            if($level === null) {
                break;
            }
            if(!$level->getBlock(new Vector3($x, $y, $z))->isSolid()) {
                $level->setBlock(new Vector3($x, $y, $z), $block, false, false);
                $y--;
                continue;
            }
            else {
                break;
            }
        }
    }
}