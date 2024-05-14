<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\sound\BlazeShootSound;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Utils\ClickableItem;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use pocketmine\nbt\tag\IntTag;

class AttributeShard extends CustomItem {
    const TYPE = "Type";
    /**
     * AttributeShard constructor.
     */
    public function __construct(int $type) {
        switch ($type) {
            case 0:
                $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_RED . "Damager Attribute";
                $lore = [];
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "You must have this attribute on all your";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "pieces of your armor to activate.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "ABILITY:";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "Deals " . TextFormat::WHITE . "5%" . TextFormat::GRAY . " of damage against";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "your opponent.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::AQUA . "Hint: " . TextFormat::GRAY . "Drag n' Drop onto an item to apply.";
                break;
            case 1:
                $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::DARK_AQUA . "Sprinter Attribute";
                $lore = [];
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "You must have this attribute on all your";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "pieces of your armor to activate.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "ABILITY:";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "Adds " . TextFormat::WHITE . "15%" . TextFormat::GRAY . " of your";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "speed when applied.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::AQUA . "Hint: " . TextFormat::GRAY . "Drag n' Drop onto an item to apply.";
                break;
            case 2:
                $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::GREEN . "Defender Attribute";
                $lore = [];
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "You must have this attribute on all your";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "pieces of your armor to activate.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "ABILITY:";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "Adds " . TextFormat::WHITE . "4%" . TextFormat::GRAY . " of damage";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "reduction when applied.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::AQUA . "Hint: " . TextFormat::GRAY . "Drag n' Drop onto an item to apply.";
                break;
            case 3:
                $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Boss Attribute Shard";
                $lore = [];
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "You must have this attribute on all your";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "pieces of your armor to activate.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "ABILITY:";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "Adds " . TextFormat::WHITE . "10%" . TextFormat::GRAY . " of damage";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "against any type of bosses.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::AQUA . "Hint: " . TextFormat::GRAY . "Drag n' Drop onto an item to apply.";
                break;
            case 4:
                $customName = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "Koth Attribute Shard";
                $lore = [];
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "You must have this attribute on all your";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "pieces of your armor to activate.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "ABILITY:";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "Adds " . TextFormat::WHITE . "4%" . TextFormat::GRAY . " of damage";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "against opponents and " . TextFormat::WHITE . "3%";
                $lore[] = TextFormat::RESET . TextFormat::GRAY . "of damage reduction.";
                $lore[] = "";
                $lore[] = TextFormat::RESET . TextFormat::AQUA . "Hint: " . TextFormat::GRAY . "Drag n' Drop onto an item to apply.";
                break;
        }
        parent::__construct(VanillaItems::NETHER_STAR(), $customName, $lore, 
        [
            new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
        ], 
        [
            self::TYPE => new IntTag($type),
            "UniqueId" => new StringTag(uniqid())
        ]);
    }

    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        return;
    }
}