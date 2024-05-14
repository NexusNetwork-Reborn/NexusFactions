<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\World;

use libs\muqsit\arithmexp\Util;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use Xekvern\Core\Server\World\Block\Generator;
use Xekvern\Core\Nexus;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\math\AxisAlignedBB;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\World;
use ReflectionException;
use Xekvern\Core\Server\Entity\Types\Spawner\Blaze;
use Xekvern\Core\Server\Entity\Types\Spawner\Cow;
use Xekvern\Core\Server\Entity\Types\Spawner\IronGolem;
use Xekvern\Core\Server\Entity\Types\Spawner\Pig;
use Xekvern\Core\Server\Entity\Types\Spawner\Squid;
use Xekvern\Core\Server\Entity\Types\Spawner\Zombie;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Server\World\Tile\MonsterSpawnerTile;
use Xekvern\Core\Server\World\Utils\GeneratorId;
use Xekvern\Core\Utils\Utils;

class WorldHandler {

    /** @var Nexus */
    private $core;

    /** @var Config */
    private static $setup;

    const MAX_SPAWNER_STACK = 64;

    /**
     * WorldHandler constructor.
     *
     * @param Nexus $core
     *
     * @throws ReflectionException
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        self::$setup = new Config($this->core->getDataFolder() . "setup.yml", Config::YAML);
        $core->getServer()->getPluginManager()->registerEvents(new WorldEvents($core), $core);
        $this->init();
    }

    /**
     * @throws ReflectionException
     */
    public function init(): void {
        $blockFactory  = RuntimeBlockStateRegistry::getInstance();
        $blockSerializer = GlobalBlockStateHandlers::getSerializer();
        $blockDeserializer = GlobalBlockStateHandlers::getDeserializer();
        $tileFactory = TileFactory::getInstance();
        (function () {
            /** @noinspection all */
            unset(
                $this->typeIndex[BlockTypeIds::HOPPER], 
                $this->typeIndex[BlockTypeIds::BEDROCK], 
                $this->typeIndex[BlockTypeIds::OBSIDIAN],
                $this->typeIndex[BlockTypeIds::GLAZED_TERRACOTTA], 
                $this->typeIndex[BlockTypeIds::MONSTER_SPAWNER], 
            );
        })->call($blockFactory);

        $unsetBlockSerializer = function () {
            /** @noinspection all */
            unset(
                $this->serializers[BlockTypeIds::MONSTER_SPAWNER]
            );
        };

        $unsetBlockDeserializer = function () {
            /** @noinspection all */
            unset(
                $this->deserializeFuncs[BlockTypeNames::MOB_SPAWNER]
            );
        };
        
        $unsetBlockSerializer->call($blockSerializer);
        $unsetBlockDeserializer->call($blockDeserializer);
        $blockFactory->register(new Generator(BlockTypeIds::GLAZED_TERRACOTTA), true);
        $tileFactory->register(\Xekvern\Core\Server\World\Tile\Generator::class, ["Generators"]);
        $tileFactory->register(\Xekvern\Core\Server\World\Tile\LuckyBlock::class, ["Luckyblocks"]);
        $tileFactory->register(MonsterSpawnerTile::class, ['MobSpawner', BlockTypeNames::MOB_SPAWNER]);
        Utils::registerSimpleBlock(BlockTypeNames::MOB_SPAWNER, ExtraVanillaItems::MONSTER_SPAWNER(), ["minecraft:mob_spawner", "mob_spawner", "monster_spawner"]);
    }

    /**
     * @param World $level
     * @param AxisAlignedBB $bb
     *
     * @return Tile[]
     */
    public static function getNearbyTiles(World $level, AxisAlignedBB $bb) : array{
        $nearby = [];
        $minX = ((int) floor($bb->minX - 2)) >> 4;
        $maxX = ((int) floor($bb->maxX + 2)) >> 4;
        $minZ = ((int) floor($bb->minZ - 2)) >> 4;
        $maxZ = ((int) floor($bb->maxZ + 2)) >> 4;
        for($x = $minX; $x <= $maxX; ++$x) {
            for($z = $minZ; $z <= $maxZ; ++$z) {
                foreach($level->getChunk($x, $z)->getTiles() as $ent) {
                    $entbb = $ent->getBlock()->getCollisionBoxes();
                    foreach ($entbb as $entb){
                        if($entb !== null) {
                            if($entb->intersectsWith($bb)) {
                                $nearby[] = $ent;
                            }
                        }
                    }
                }
            }
        }
        return $nearby;
    }

    /**
     * @param GeneratorId $id
     * @param bool $auto
     * 
     * @return DyeColor
     */
    public static function getGeneratorColorById(int $id, bool $auto = false) : DyeColor { 
        $lore = [];
        if($auto === true) { // Auto
            switch($id) {
                case GeneratorId::COAL:
                    $color = DyeColor::BLUE();
                    break;
                case GeneratorId::REDSTONE_DUST:
                    $color = DyeColor::LIGHT_BLUE();
                    break;
                case GeneratorId::IRON:
                    $color = DyeColor::GRAY();
                    break;
                case GeneratorId::DIAMOND:
                    $color = DyeColor::MAGENTA();
                    break;
                case GeneratorId::EMERALD:
                    $color = DyeColor::GREEN();
                    break;
                case GeneratorId::EMERALD:
                    $color = DyeColor::RED();
                    break;
            }   
        } else { // Mining
            switch($id) {
                case GeneratorId::COAL:
                    $color = DyeColor::BROWN();
                    break;
                case GeneratorId::LAPIS_LAZULI:
                    $color = DyeColor::CYAN();
                    break;
                case GeneratorId::IRON:
                    $color = DyeColor::LIGHT_GRAY();
                    break;
                case GeneratorId::DIAMOND:
                    $color = DyeColor::PINK();
                    break;
                case GeneratorId::AMETHYST:
                    $color = DyeColor::PURPLE();
                    break;
                case GeneratorId::EMERALD:
                    $color = DyeColor::LIME();
                    break;
            }
        }
        return $color;
    }


    /**
     * @param int $type
     * 
     * @return string
     */
    public static function getGeneratorTypeString(int $type): string { 
        switch($type) {
            case Generator::MINING:
                return "Mining";
                break;
            case Generator::AUTO:
                return "Automatic";
                break;
            case Generator::SPECIAL:
                return "Amethyst Special";
                break;
            case Generator::SPECIAL2:
                return "Enchantment Special";
                break;
            default:
                return "Mining";
                break;
        }
    }

    /**
     * @param DyeColor $color
     * 
     * @return int
     */
    public static function getGeneratorType(DyeColor $color): int
    {
        switch ($color) {
                //-- Mining
            case DyeColor::BROWN(): // Coal
                return Generator::MINING;
                break;
            case DyeColor::CYAN(): // Lapis
                return Generator::MINING;
                break;
            case DyeColor::LIGHT_GRAY(): // Iron
                return Generator::MINING;
                break;
            case DyeColor::PINK(): // Diamond
                return Generator::MINING;
                break;
            case DyeColor::LIME(): // Emerald
                return Generator::MINING;
                break;
                //-- Auto
            case DyeColor::BLUE(): // Coal
                return Generator::AUTO;
                break;
            case DyeColor::LIGHT_BLUE(): // Redstone
                return Generator::AUTO;
                break;
            case DyeColor::GRAY(): // Iron
                return Generator::AUTO;
                break;
            case DyeColor::MAGENTA(): // Diamond
                return Generator::AUTO;
                break;
            case DyeColor::GREEN(): // Emerald
                return Generator::AUTO;
                break;
            case DyeColor::BLACK(): // To fix terracotta luckyblock
                return Generator::MINING;
                break;
            case DyeColor::PURPLE(): // Amethyst
                return Generator::SPECIAL;
                break;
            case DyeColor::RED(): // Enchant
                return Generator::SPECIAL2;
                break;
            default:
                return Generator::MINING;
                break;
        }
    }

    /**
     * @param BlockTypeIds $id
     * 
     * @return Block
     */
    public static function getGeneratorOreByType(DyeColor $color): Block
    {
        switch ($color) {
            case DyeColor::BROWN():
                return VanillaBlocks::COAL_ORE();
                break;
            case DyeColor::CYAN():
                return VanillaBlocks::LAPIS_LAZULI_ORE();
                break;
            case DyeColor::LIGHT_GRAY():
                return VanillaBlocks::IRON_ORE();
                break;
            case DyeColor::PINK():
                return VanillaBlocks::DIAMOND_ORE();
                break;
            case DyeColor::PURPLE():
                return VanillaBlocks::AMETHYST();
                break;
            case DyeColor::LIME():
                return VanillaBlocks::EMERALD_ORE();
                break;
            case DyeColor::BLUE(): // Coal
                return VanillaBlocks::COAL();
                break;
            case DyeColor::LIGHT_BLUE(): // Redstone
                return VanillaBlocks::REDSTONE();
                break;
            case DyeColor::GRAY(): // Iron
                return VanillaBlocks::IRON();
                break;
            case DyeColor::MAGENTA(): // Diamond
                return VanillaBlocks::DIAMOND();
                break;
            case DyeColor::GREEN(): // Emerald
                return VanillaBlocks::EMERALD();
                break;
            default:
                return VanillaBlocks::AIR();
                break;
        }
    }

    /**
     * @param DyeColor $color
     * 
     * @return int
     */
    public static function getGeneratorValue(DyeColor $color) : int {
        return match ($color->id()) {
            DyeColor::BROWN()->id() => 100000, // MINING
            DyeColor::CYAN()->id() => 200000,
            DyeColor::LIGHT_GRAY()->id()  => 500000,
            DyeColor::PINK()->id() => 3000000,
            DyeColor::LIME()->id() => 5000000, 
            DyeColor::BLUE()->id()  => 160000, // AUTO
            DyeColor::LIGHT_BLUE()->id() => 300000,
            DyeColor::GRAY()->id() => 700000,
            DyeColor::MAGENTA()->id() => 4000000,
            DyeColor::PURPLE()->id() => 0,
            DyeColor::RED()->id() => 0,
            DyeColor::GREEN()->id()  => 7000000,
        };
    }

    /**
     * @param string $entityTypeId
     * 
     * @return int
     */
    public static function getSpawnerValue(string $entityTypeId) : int {
        return match ($entityTypeId) {
            Pig::getNetworkTypeId() => 500000,
            Cow::getNetworkTypeId() => 1000000,
            Zombie::getNetworkTypeId() => 1750000,
            Squid::getNetworkTypeId() => 2500000,
            Blaze::getNetworkTypeId() => 5000000,
            IronGolem::getNetworkTypeId() => 9000000,
        };
    }

    /**
     * @return Config
     */
    public static function getSetup() : Config {
        return self::$setup;
    }
}