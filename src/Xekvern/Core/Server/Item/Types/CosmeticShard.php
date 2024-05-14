<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item\Types;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\Inventory;
use pocketmine\item\Axe;
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

class CosmeticShard extends CustomItem
{
    const EQUIPMENT = "Equipment";
    const EFFECT = "Effect";

    /**
     * CosmeticShard constructor.
     */
    public function __construct(int $equipment, int $effect)
    {
        $lore = [];
        switch ($effect) {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                $rarity = TextFormat::GRAY . "Common";
                break;
            case 7:
            case 8:
            case 9:
            case 10:
            case 11:
            case 12:
            case 13:
                $rarity = TextFormat::GREEN . "Uncommon";
                break;
            case 14:
            case 15:
            case 16:
            case 17:
            case 18:
                $rarity = TextFormat::BLUE . "Rare";
                break;
            case 19:
            case 20:
            case 21:
            case 22:
                $rarity = TextFormat::LIGHT_PURPLE . "Mythic";
                break;
        }

        $effectMap = [
            0 => "Green",
            1 => "Red",
            2 => "Blue",
            3 => "Yellow",
            4 => "Pink",
            5 => "White",
            6 => "Black",
            7 => "Dark Blue",
            8 => "Dark Green",
            9 => "Dark Aqua",
            10 => "Dark Red",
            11 => "Purple",
            12 => "Gold",
            13 => "Aqua",
            14 => "Critical",
            15 => "Portal",
            16 => "Ink",
            17 => "Lava",
            18 => "Water",
            19 => "Heart",
            20 => "Flame",
            21 => "Smoke",
            22 => "Lavabomb",
        ];
        switch ($equipment) {
            case 0:
                $customName = TextFormat::RESET . TextFormat::DARK_RED . TextFormat::BOLD . "Weapon Cosmetic Shard";
                $effectTypeParticle = "Hit Particles";
                break;
            case 1:
                $customName = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "Armor Cosmetic Shard";
                $effectTypeParticle = "Particle Trail";
                break;
            case 2:
                $customName = TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "Armor Cosmetic Shard";
                $effectTypeParticle = "Particle Halo";
                break;
        }
        $effectName = $effectMap[$effect];
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Rarity: " . TextFormat::BOLD . $rarity;
        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Effect: " . TextFormat::BOLD . TextFormat::AQUA . "$effectName $effectTypeParticle";
        $lore[] = "";
        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Drag n' Drop onto an item to apply.";
        parent::__construct(
            VanillaItems::PRISMARINE_SHARD(),
            $customName,
            $lore,
            [
                new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(50), 1)
            ],
            [
                self::EQUIPMENT => new IntTag($equipment),
                self::EFFECT => new IntTag($effect),
                "UniqueId" => new StringTag(uniqid())
            ]
        );
    }

    public static function execute(NexusPlayer $player, Inventory $inventory, Item $item, CompoundTag $tag, int $face, Block $blockClicked): void {
        return;
    }
}
