<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Price;

use Xekvern\Core\Nexus;
use Xekvern\Core\Server\Item\Types\CreeperEgg;
use Xekvern\Core\Server\Item\Types\GeneratorBucket;
use Xekvern\Core\Server\Item\Types\SellWand;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use Xekvern\Core\Server\Item\Types\SellWandNote;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\Utils\GeneratorId;
use Xekvern\Core\Server\World\WorldHandler;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\Entity;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\Types\MonsterSpawner;
use Xekvern\Core\Server\Item\Types\OreGenerator;

class PriceHandler {

    /** @var Nexus */
    private $core;

    /** @var ShopPlace[] */
    private $places = [];

    /** @var PriceEntry[] */
    private $sellables = [];

    /**
     * PriceHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->init();
    }

    public function init() {
        $worldHandler = new WorldHandler($this->core);
        $this->places = [
            new ShopPlace("Blocks", VanillaBlocks::GRASS()->asItem(), [
                new PriceEntry(VanillaBlocks::OBSIDIAN()->asItem(), null, 25, 175),
                new PriceEntry(VanillaBlocks::BEDROCK()->asItem(), null, 200, 10000),
                new PriceEntry(VanillaBlocks::GLOWSTONE()->asItem(), null, null, 12),
                new PriceEntry(VanillaBlocks::SEA_LANTERN()->asItem(), null, null, 15),
                new PriceEntry(VanillaBlocks::DIRT()->asItem(), null, 2, 5),
                new PriceEntry(VanillaBlocks::GRASS()->asItem(), null, 5, 10),
                new PriceEntry(VanillaBlocks::COBBLESTONE()->asItem(), null, 3),
                new PriceEntry(VanillaBlocks::ANDESITE()->asItem(), null, 3),
                new PriceEntry(VanillaBlocks::DIORITE()->asItem(), null, 5),
                new PriceEntry(VanillaBlocks::GRANITE()->asItem(), null, 5),
                new PriceEntry(VanillaBlocks::ENCHANTING_TABLE()->asItem(), null, null, 100000),
                new PriceEntry(VanillaBlocks::OAK_WOOD()->asItem(), null, 6, 12),
                new PriceEntry(VanillaBlocks::SPRUCE_WOOD()->asItem(), null, 6, 12),
                new PriceEntry(VanillaBlocks::BIRCH_WOOD()->asItem(), null, 6, 12),
                new PriceEntry(VanillaBlocks::JUNGLE_WOOD()->asItem(), null, 6, 12),
                new PriceEntry(VanillaBlocks::STONE_BRICKS()->asItem(), null, null, 12),
                new PriceEntry(VanillaBlocks::MOSSY_STONE_BRICKS()->asItem(), null, null, 12),
                new PriceEntry(VanillaBlocks::CRACKED_STONE_BRICKS()->asItem(), null, null, 12),
                new PriceEntry(VanillaBlocks::END_STONE_BRICKS()->asItem(), null, null, 12),
                new PriceEntry(VanillaBlocks::PRISMARINE()->asItem(), null, null, 24),
                new PriceEntry(VanillaBlocks::DARK_PRISMARINE()->asItem(), null, null, 24),
                new PriceEntry(VanillaBlocks::GLASS()->asItem(), null, null, 6),
                new PriceEntry(VanillaBlocks::NETHER_BRICKS()->asItem(), null, null, 30),
                new PriceEntry(VanillaBlocks::NETHERRACK()->asItem(), null, null, 12),
                new PriceEntry(VanillaBlocks::QUARTZ()->asItem(), null, null, 60),
                new PriceEntry(VanillaBlocks::CHISELED_QUARTZ()->asItem(), null, null, 60),
                new PriceEntry(VanillaBlocks::SAND()->asItem(), null, null, 6),
                new PriceEntry(VanillaBlocks::RED_SAND()->asItem(), null, null, 8),
                new PriceEntry(VanillaBlocks::GRAVEL()->asItem(), null, null, 8)
            ]), 
            new ShopPlace("Wool", VanillaBlocks::WOOL()->setColor(DyeColor::BLUE())->asItem(), [
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::BLACK())->asItem(), "Black Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::RED())->asItem(), "Red Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::GREEN())->asItem(), "Green Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::BROWN())->asItem(), "Brown Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::PURPLE())->asItem(), "Purple Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::CYAN())->asItem(), "Cyan Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::LIGHT_GRAY())->asItem(), "Light Gray Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::GRAY())->asItem(), "Gray Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::PINK())->asItem(), "Pink Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::LIME())->asItem(), "Lime Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::YELLOW())->asItem(), "Dandelion Yellow Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::LIGHT_BLUE())->asItem(), "Light Blue Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::MAGENTA())->asItem(), "Magenta Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::ORANGE())->asItem(), "Orange Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::WHITE())->asItem(), "White Wool", 30, 60),
                new PriceEntry(VanillaBlocks::WOOL()->setColor(DyeColor::BLUE())->asItem(), "Blue Wool", 30, 60)
            ]),
            new ShopPlace("Combat", VanillaItems::DIAMOND_SWORD(), [
                new PriceEntry(VanillaItems::GOLDEN_APPLE(), null, null, 500),
                new PriceEntry(VanillaItems::ENCHANTED_GOLDEN_APPLE(), null, null, 5000),
            ]),
            new ShopPlace("Utilities", VanillaItems::WOODEN_HOE(), [
                new PriceEntry((new CreeperEgg())->getItemForm(), null, null, 10000),
                new PriceEntry((new GeneratorBucket(BlockTypeIds::COBBLESTONE))->getItemForm(), "Cobblestone Generator Bucket", null, 500000),
                new PriceEntry((new GeneratorBucket(BlockTypeIds::OBSIDIAN))->getItemForm(), "Obsidian Generator Bucket", null, 5000000),
                new PriceEntry((new GeneratorBucket(BlockTypeIds::BEDROCK))->getItemForm(), "Bedrock Generator Bucket", null, 25000000),
                new PriceEntry((new GeneratorBucket(BlockTypeIds::WATER))->getItemForm(), "Water Generator Bucket", null, 10000000),
                new PriceEntry((new GeneratorBucket(BlockTypeIds::LAVA))->getItemForm(), "Lava Generator Bucket", null, 50000000),
                new PriceEntry(VanillaItems::BOW(), null, null, 5000),
                new PriceEntry(VanillaItems::ARROW(), null, null, 5),
                new PriceEntry(VanillaBlocks::TNT()->asItem(), null, null, 2000),
                new PriceEntry(VanillaBlocks::SPONGE()->asItem(), null, null, 10000),
                new PriceEntry(VanillaBlocks::WATER()->getFlowingForm()->asItem(), null, null, 15000),
                new PriceEntry(VanillaBlocks::LAVA()->getFlowingForm()->asItem(), null, null, 30000),
                new PriceEntry(VanillaBlocks::CHEST()->asItem(), null, null, 1000),
                new PriceEntry(VanillaBlocks::HOPPER()->asItem(), null, null, 10000),
                new PriceEntry(VanillaItems::ENDER_PEARL(), null, null, 100),
                new PriceEntry(VanillaItems::STEAK(), null, null, 1),
                new PriceEntry(VanillaBlocks::TORCH()->asItem(), null, null, 5),
            ]),
            new ShopPlace("Mining Generators", VanillaItems::DIAMOND_PICKAXE(), [
                new PriceEntry((new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::COAL, false)))->getItemForm(), "Coal Ore Block Generator", null, 100000),
                new PriceEntry((new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::LAPIS_LAZULI, false)))->getItemForm(), "Lapis Lazuli Ore Block Generator", null, 200000, "permission.subordinate"),
                new PriceEntry((new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::IRON, false)))->getItemForm(), "Iron Ore Block Generator", null, 500000, "permission.knight"),
            ]),
            new ShopPlace("Auto Generators", VanillaBlocks::CHEST()->asItem(), [
                new PriceEntry((new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::COAL, true)))->getItemForm(), "Coal Auto Generator", null, 160000),
                new PriceEntry((new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::REDSTONE_DUST, true)))->getItemForm(), "Redstone Dust Auto Generator", null, 300000, "permission.knight"),
                new PriceEntry((new OreGenerator(WorldHandler::getGeneratorColorById(GeneratorId::IRON, true)))->getItemForm(), "Iron Auto Generator", null, 700000, "permission.hoplite"),
                new PriceEntry((new SellWand())->getItemForm(), null, null, 500000, "permission.knight"),
                new PriceEntry((new SellWandNote(100))->getItemForm(), "Sell Uses 100", null, 250000, "permission.prince"),
                new PriceEntry((new SellWandNote(1000))->getItemForm(), "Sell Uses 100", null, 2750000, "permission.prince"),
            ]),
            new ShopPlace("Spawners", VanillaBlocks::MONSTER_SPAWNER()->asItem(), [
                new PriceEntry((new MonsterSpawner(EntityIds::PIG))->getItemForm(), "Pig Spawner", null, 500000),
                new PriceEntry((new MonsterSpawner(EntityIds::COW))->getItemForm(), "Cow Spawner", null, 1000000, "permission.subordinate"),
                new PriceEntry((new MonsterSpawner(EntityIds::ZOMBIE))->getItemForm(), "Zombie Spawner", null, 1750000, "permission.knight"),
                new PriceEntry((new MonsterSpawner(EntityIds::SQUID))->getItemForm(), "Squid Spawner", null, 2500000, "permission.hoplite"),
            ]),
            new ShopPlace("Valuables", VanillaItems::DIAMOND(), [
                new PriceEntry(VanillaItems::COAL(), null, 7),
                new PriceEntry(VanillaItems::LAPIS_LAZULI(), null, 16),
                new PriceEntry(VanillaItems::REDSTONE_DUST(), null, 28),
                new PriceEntry(VanillaItems::RAW_IRON(), null, 36),
                new PriceEntry(VanillaItems::RAW_GOLD(), null, 44),
                new PriceEntry(VanillaItems::IRON_INGOT(), null, 75),
                new PriceEntry(VanillaItems::GOLD_INGOT(), null, 82),
                new PriceEntry(VanillaItems::DIAMOND(), null, 98),
                new PriceEntry(VanillaItems::EMERALD(), null, 132),

                new PriceEntry(VanillaBlocks::IRON()->asItem(), null, 675), // X9 
                new PriceEntry(VanillaBlocks::GOLD()->asItem(), null, 738),
                new PriceEntry(VanillaBlocks::DIAMOND()->asItem(), null, 882),
                new PriceEntry(VanillaBlocks::EMERALD()->asItem(), null, 1188),
            ]),
            new ShopPlace("Mob Drops", VanillaItems::ROTTEN_FLESH(), [
                new PriceEntry(VanillaItems::RAW_PORKCHOP(), null, 12),
                new PriceEntry(VanillaItems::LEATHER(), null, 24),
                new PriceEntry(VanillaItems::RAW_BEEF(), null, 38),
                new PriceEntry(VanillaItems::ROTTEN_FLESH(), null, 56),
                new PriceEntry(VanillaItems::INK_SAC(), null, 87),
                new PriceEntry(VanillaItems::BLAZE_ROD(), null, 175),
                new PriceEntry(VanillaItems::NETHER_STAR(), null, 255),
                new PriceEntry(VanillaBlocks::POPPY()->asItem(), null, 10),
            ])
        ];
        foreach($this->getAll() as $entry) {
            if($entry->getSellPrice() !== null) {
                $this->sellables[$entry->getItem()->getTypeId()] = $entry;
            }
        }
    }

    /**
     * @return PriceEntry[]
     */
    public function getAll(): array {
        $all = [];
        foreach($this->places as $place) {
            $all = array_merge($all, $place->getEntries());
        }
        return $all;
    }

    /**
     * @return PriceEntry[]
     */
    public function getSellables(): array {
        return $this->sellables;
    }

    /**
     * @return ShopPlace[]
     */
    public function getPlaces(): array {
        return $this->places;
    }

    /**
     * @param string $name
     *
     * @return ShopPlace|null
     */
    public function getPlace(string $name): ?ShopPlace {
        foreach($this->places as $place) {
            if($place->getName() === $name) {
                return $place;
            }
        }
        return null;
    }
}