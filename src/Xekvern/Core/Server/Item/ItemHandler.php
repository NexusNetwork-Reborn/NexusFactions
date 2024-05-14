<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item;

// ENCHANTMENTS 

use pocketmine\block\utils\DyeColor;
use pocketmine\item\Pickaxe;
use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\data\bedrock\block\BlockTypeNames;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Utils\Utils;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
// ITEMS
use Xekvern\Core\Nexus;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaArmorMaterials;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Durable;
use pocketmine\item\Dye;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\FireAspectEnchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\ProtectionEnchantment;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\enchantment\SharpnessEnchantment;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Sword;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\world\particle\CriticalParticle;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\InkParticle;
use pocketmine\world\particle\LavaDripParticle;
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\particle\PortalParticle;
use pocketmine\world\particle\WaterParticle;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\HopsEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\PerceptionEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\QuickeningEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Bow\VelocityEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Mining\AmplifyEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Mining\HasteEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Mining\JackpotEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\WitherEnchantment;
use Xekvern\Core\Server\Item\Enchantment\EnchantmentEvents;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\BlessEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\DeflectEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\DivineProtectionEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\EvadeEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\FortifyEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\ImmunityEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\NourishEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Armor\RejuvenateEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Bow\LightEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Bow\ParalyzeEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Bow\PierceEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Mining\CharmEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Mining\DrillerEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Mining\FossilizationEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Mining\LuckEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Mining\SmeltingEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\AnnihilationEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\BerserkEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\BleedEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\ContaminateEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\DoublestrikeEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\DrainEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\FlingEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\GuillotineEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\ImprisonEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\LifestealEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\LustEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\NauseateEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\PassiveEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\PyrokineticEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\ShatterEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\SlaughterEnchantment;
use Xekvern\Core\Server\Item\Enchantment\Types\Weapon\ThunderEnchantment;
use Xekvern\Core\Server\Item\Types\AttributesBag;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Item\Types\CrateKeyNote;
use Xekvern\Core\Server\Item\Types\CreeperEgg;
use Xekvern\Core\Server\Item\Types\CustomTag;
use Xekvern\Core\Server\Item\Types\Drops;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;
use Xekvern\Core\Server\Item\Types\GeneratorBucket;
use Xekvern\Core\Server\Item\Types\Head;
use Xekvern\Core\Server\Item\Types\HolyBox;
use Xekvern\Core\Server\Item\Types\KOTHLootbag;
use Xekvern\Core\Server\Item\Types\KOTHStarter;
use Xekvern\Core\Server\Item\Types\Lootbox;
use Xekvern\Core\Server\Item\Types\MoneyNote;
use Xekvern\Core\Server\Item\Types\MonthlyCrate;
use Xekvern\Core\Server\Item\Types\Recon;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Server\Item\Types\SellWand;
use Xekvern\Core\Server\Item\Types\SellWandNote;
use Xekvern\Core\Server\Item\Types\TNTLauncher;
use Xekvern\Core\Server\Item\Types\Vanilla\CreeperSpawnEgg;
use Xekvern\Core\Server\Item\Types\XPNote;
use Xekvern\Core\Server\Item\Types\AttributeShard;
use Xekvern\Core\Server\Item\Types\CosmeticBag;
use Xekvern\Core\Server\Item\Types\CosmeticShard;
use Xekvern\Core\Server\Item\Types\AbilityScroll;
use Xekvern\Core\Server\Item\Types\BossEgg;
use Xekvern\Core\Server\Item\Types\GeneratorsBag;
use Xekvern\Core\Server\Item\Types\MysterySpawnerBag;
use Xekvern\Core\Server\Item\Types\PowerNote;
use Xekvern\Core\Server\Item\Types\RankNote;
use Xekvern\Core\Server\Item\Utils\CustomItem;

class ItemHandler
{

    /** @var Nexus */
    private $core;

    /** @var Enchantment[] */
    private static $enchantments = [];

    /** @var array */
    private static $classifiedEnchantments = [];

    /** @var array string[] */
    private $items = [];

    /** @var int[] */
    private $redeemed = [];

    const DEFAULT_ENCHANT_LIMIT = 5;
    const MAX_ENCHANT_LIMIT = 10;

    /**
     * ItemHandler constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
        $core->getServer()->getPluginManager()->registerEvents(new ItemEvents($core), $core);
        $core->getServer()->getPluginManager()->registerEvents(new EnchantmentEvents($core), $core);
        $this->init();
        self::registerExtraItems();
        $core->getServer()->getAsyncPool()->addWorkerStartHook(function (int $worker): void {
            Nexus::getInstance()->getServer()->getAsyncPool()->submitTaskToWorker(new class extends AsyncTask
            {
                public function onRun(): void
                {
                    ItemHandler::registerExtraItems();
                }
            }, $worker);
        });
    }

    public function init()
    {
        $this->registerItems();
        $this->registerEnchantments();
    }

    private function registerItems()
    {
        $items = [
            MoneyNote::class,
            XPNote::class,
            ChestKit::class,
            SacredStone::class,
            Head::class,
            HolyBox::class,
            SellWandNote::class,
            MonthlyCrate::class,
            Recon::class,
            CustomTag::class,
            Lootbox::class,
            Drops::class,
            GeneratorBucket::class,
            KOTHStarter::class,
            KOTHLootbag::class,
            CrateKeyNote::class,
            AttributeShard::class,
            CosmeticShard::class,
            AbilityScroll::class,
            CosmeticBag::class,
            AttributesBag::class,
            MysterySpawnerBag::class,
            GeneratorsBag::class,
            PowerNote::class,
            RankNote::class,
            BossEgg::class,
        ];
        foreach ($items as $itemClass) {
            $this->registerItem($itemClass);
        }
    }

    private function registerEnchantments()
    {
        $stringToEnchParser = StringToEnchantmentParser::getInstance();
        $enchIdMap = EnchantmentIdMap::getInstance();
        $enchantments = [
            // Armor
            [new HopsEnchantment(), Enchantment::HOPS],
            [new PerceptionEnchantment(), Enchantment::PERCEPTION],
            [new QuickeningEnchantment(), Enchantment::QUICKENING],
            [new BlessEnchantment(), Enchantment::BLESS],
            [new DeflectEnchantment(), Enchantment::DEFLECT],
            [new DivineProtectionEnchantment(), Enchantment::DIVINE_PROTECTION],
            [new EvadeEnchantment(), Enchantment::EVADE],
            [new FortifyEnchantment(), Enchantment::FORTIFY],
            [new ImmunityEnchantment(), Enchantment::IMMUNITY],
            [new NourishEnchantment(), Enchantment::NOURISH],
            [new RejuvenateEnchantment(), Enchantment::REJUVENATE],
            // Bow
            [new VelocityEnchantment(), Enchantment::VELOCITY],
            [new PierceEnchantment(), Enchantment::PIERCE],
            [new ParalyzeEnchantment(), Enchantment::PARALYZE],
            [new LightEnchantment(), Enchantment::LIGHT],
            // Pickaxe
            [new AmplifyEnchantment(), Enchantment::AMPLIFY],
            [new CharmEnchantment(), Enchantment::CHARM],
            [new LuckEnchantment(), Enchantment::LUCK],
            [new HasteEnchantment(), Enchantment::HASTE],
            [new JackpotEnchantment(), Enchantment::JACKPOT],
            [new SmeltingEnchantment(), Enchantment::SMELTING],
            [new FossilizationEnchantment(), Enchantment::FOSSILIZATION],
            // Sword & Axe
            [new WitherEnchantment(), Enchantment::WITHER],
            [new ThunderEnchantment(), Enchantment::THUNDER],
            [new SlaughterEnchantment(), Enchantment::SLAUGHTER],
            [new ShatterEnchantment(), Enchantment::SHATTER],
            [new PyrokineticEnchantment(), Enchantment::PYROKINETIC],
            [new PassiveEnchantment(), Enchantment::PASSIVE],
            [new NauseateEnchantment(), Enchantment::NAUSEATE],
            [new LustEnchantment(), Enchantment::LUST],
            [new LifestealEnchantment(), Enchantment::LIFESTEAL],
            [new ImprisonEnchantment(), Enchantment::IMPRISON],
            [new GuillotineEnchantment(), Enchantment::GUILLOTINE],
            [new FlingEnchantment(), Enchantment::FLING],
            [new DrainEnchantment(), Enchantment::DRAIN],
            [new DoublestrikeEnchantment(), Enchantment::DOUBLE_STRIKE],
            [new ContaminateEnchantment(), Enchantment::CONTAMINATE],
            [new BleedEnchantment(), Enchantment::BLEED],
            [new BerserkEnchantment(), Enchantment::BERSERK],
            [new AnnihilationEnchantment(), Enchantment::ANNIHILATION]
        ];
        foreach ($enchantments as [$enchantment, $id]) {
            self::registerEnchantment($enchantment, $id);
        }
        EnchantmentIdMap::getInstance()->register(50, (new \pocketmine\item\enchantment\Enchantment("UnknownCE", \pocketmine\item\enchantment\Rarity::COMMON, 0, 0, 1)));
        $sharpness = new SharpnessEnchantment(KnownTranslationFactory::enchantment_damage_all(), Rarity::COMMON, ItemFlags::SWORD, ItemFlags::AXE, 15);
        $enchIdMap->register(EnchantmentIds::SHARPNESS, $sharpness);
        $stringToEnchParser->override("sharpness", fn () => $sharpness);
        self::$enchantments[EnchantmentIds::SHARPNESS] = $sharpness;
        self::$enchantments["Sharpness"] = $sharpness;
        self::$classifiedEnchantments[Rarity::COMMON][] = $sharpness;

        $protection = new ProtectionEnchantment(KnownTranslationFactory::enchantment_protect_all(), Rarity::COMMON, ItemFlags::ARMOR, ItemFlags::NONE, 15, 0.75, null);
        $enchIdMap->register(EnchantmentIds::PROTECTION, $protection);
        $stringToEnchParser->override("protection", fn () => $protection);
        self::$enchantments[EnchantmentIds::PROTECTION] = $protection;
        self::$enchantments["Protection"] = $protection;
        self::$classifiedEnchantments[Rarity::COMMON][] = $protection;

        $unbreaking = new \pocketmine\item\enchantment\Enchantment(KnownTranslationFactory::enchantment_durability(), Rarity::UNCOMMON, ItemFlags::ALL, ItemFlags::NONE, 15);
        $enchIdMap->register(EnchantmentIds::UNBREAKING, $unbreaking);
        $stringToEnchParser->override("unbreaking", fn () => $unbreaking);
        self::$enchantments[EnchantmentIds::UNBREAKING] = $unbreaking;
        self::$enchantments["Unbreaking"] = $unbreaking;
        self::$classifiedEnchantments[Rarity::UNCOMMON][] = $unbreaking;

        $efficiency = new \pocketmine\item\enchantment\Enchantment(KnownTranslationFactory::enchantment_digging(), Rarity::UNCOMMON, ItemFlags::DIG, ItemFlags::TOOL, 10);
        $enchIdMap->register(EnchantmentIds::EFFICIENCY, $efficiency);
        $stringToEnchParser->override("efficiency", fn () => $efficiency);
        self::$enchantments[EnchantmentIds::EFFICIENCY] = $efficiency;
        self::$enchantments["Efficiency"] = $efficiency;
        self::$classifiedEnchantments[Rarity::UNCOMMON][] = $efficiency;

        $silk_touch = new \pocketmine\item\enchantment\Enchantment(KnownTranslationFactory::enchantment_untouching(), Rarity::UNCOMMON, ItemFlags::DIG, ItemFlags::TOOL, 1);
        $enchIdMap->register(EnchantmentIds::SILK_TOUCH, $silk_touch);
        $stringToEnchParser->override("silk_touch", fn () => $silk_touch);
        self::$enchantments[EnchantmentIds::SILK_TOUCH] = $silk_touch;
        self::$enchantments["Silk Touch"] = $silk_touch;
        self::$classifiedEnchantments[Rarity::UNCOMMON][] = $silk_touch;

        $power = new \pocketmine\item\enchantment\Enchantment(KnownTranslationFactory::enchantment_arrowDamage(), Rarity::COMMON, ItemFlags::BOW, ItemFlags::NONE, 5);
        $enchIdMap->register(EnchantmentIds::POWER, $power);
        $stringToEnchParser->override("power", fn () => $power);
        self::$enchantments[EnchantmentIds::POWER] = $power;
        self::$enchantments["Power"] = $power;
        self::$classifiedEnchantments[Rarity::UNCOMMON][] = $power;
    }

    public static function registerExtraItems(): void
    {
        Utils::registerSimpleItem(ItemTypeNames::END_CRYSTAL, ExtraVanillaItems::END_CRYSTAL(), ["end_crystal"]);
        Utils::registerSimpleItem(ItemTypeNames::CREEPER_SPAWN_EGG, ExtraVanillaItems::CREEPER_SPAWN_EGG(), ["creeper_spawn_egg"]);
        //Utils::registerSimpleItem(ItemTypeNames::NAME_TAG, ExtraVanillaItems::NAME_TAG(), ["name_tag"]);
        Utils::registerSimpleItem(ItemTypeNames::ENDER_EYE, ExtraVanillaItems::ENDER_EYE(), ["ender_eye"]);
        Utils::registerSimpleItem(ItemTypeNames::FIREWORK_ROCKET, ExtraVanillaItems::FIREWORKS(), ["fireworks"]);
        Utils::registerSimpleItem(ItemTypeNames::EMPTY_MAP, ExtraVanillaItems::MAP(), ["map"]);
    }

    public static function getIdentifier(int $id): ?string
    {
        return self::$animationIDs[$id] ?? null;
    }

    private static array $animationIDs = [];

    /**
     * @return Enchantment[]
     */
    public static function getEnchantments(): array
    {
        return self::$enchantments;
    }

    /**
     * @param $identifier
     *
     * @return \pocketmine\item\enchantment\Enchantment|null
     */
    public static function getEnchantment($identifier): ?\pocketmine\item\enchantment\Enchantment
    {
        return self::$enchantments[$identifier] ?? null;
    }

    /**
     * @param int|null $rarity
     *
     * @return \pocketmine\item\enchantment\Enchantment
     */
    public static function getRandomEnchantment(?int $rarity = null): \pocketmine\item\enchantment\Enchantment
    {
        if ($rarity !== null) {
            /** @var \pocketmine\item\enchantment\Enchantment[] $enchantments */
            try {
                $enchantments = self::$classifiedEnchantments[$rarity];
                return $enchantments[array_rand($enchantments)];
            } catch (\ErrorException) {
            }
        }
        return self::$enchantments[array_rand(self::$enchantments)];
    }

    /**
     * @param \pocketmine\item\enchantment\Enchantment $enchantment
     */
    public static function registerEnchantment(\pocketmine\item\enchantment\Enchantment $enchantment, int $id): void
    {
        EnchantmentIdMap::getInstance()->register($id, $enchantment);
        self::$enchantments[$id] = $enchantment;
        self::$enchantments[$enchantment->getName()] = $enchantment;
        self::$classifiedEnchantments[$enchantment->getRarity()][] = $enchantment;
    }

    /**
     * @param int $integer
     *
     * @return string
     */
    public static function getRomanNumber(int $integer): string
    {
        $characters = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];
        $romanString = "";
        while ($integer > 0) {
            foreach ($characters as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $romanString .= $rom;
                    break;
                }
            }
        }
        return $romanString;
    }

    /**
     * @param Item $item
     * @param \pocketmine\item\enchantment\Enchantment $enchantment
     *
     * @return bool
     */
    public static function canEnchant(Item $item, \pocketmine\item\enchantment\Enchantment $enchantment): bool
    {
        if ($item->hasEnchantment($enchantment)) {
            if ($item->getEnchantmentLevel($enchantment) < $enchantment->getMaxLevel()) {
                return true;
            }
            return false;
        }
        switch ($enchantment->getPrimaryItemFlags()) {
            case ItemFlags::ALL:
                if ($item instanceof Durable) {
                    return true;
                }
                break;
            case ItemFlags::FEET:
                if (
                    $item->getTypeId() === ItemTypeIds::LEATHER_BOOTS or
                    $item->getTypeId() === ItemTypeIds::CHAINMAIL_BOOTS or
                    $item->getTypeId() === ItemTypeIds::GOLDEN_BOOTS or
                    $item->getTypeId() === ItemTypeIds::IRON_BOOTS or
                    $item->getTypeId() === ItemTypeIds::DIAMOND_BOOTS
                ) {
                    return true;
                }
                break;
            case ItemFlags::HEAD:
                if (
                    $item->getTypeId() === ItemTypeIds::LEATHER_CAP or
                    $item->getTypeId() === ItemTypeIds::CHAINMAIL_CHESTPLATE or
                    $item->getTypeId() === ItemTypeIds::GOLDEN_HELMET or
                    $item->getTypeId() === ItemTypeIds::IRON_HELMET or
                    $item->getTypeId() === ItemTypeIds::DIAMOND_HELMET
                ) {
                    return true;
                }
                break;
            case ItemFlags::ARMOR:
                if (
                    $item->getTypeId() === ItemTypeIds::LEATHER_TUNIC or
                    $item->getTypeId() === ItemTypeIds::CHAINMAIL_CHESTPLATE or
                    $item->getTypeId() === ItemTypeIds::GOLDEN_CHESTPLATE or
                    $item->getTypeId() === ItemTypeIds::IRON_CHESTPLATE or
                    $item->getTypeId() === ItemTypeIds::DIAMOND_CHESTPLATE
                    or $item instanceof Armor
                ) {
                    return true;
                }
                break;
            case ItemFlags::SWORD:
                if (
                    $item->getTypeId() === ItemTypeIds::WOODEN_SWORD or
                    $item->getTypeId() === ItemTypeIds::STONE_SWORD or
                    $item->getTypeId() === ItemTypeIds::IRON_SWORD or
                    $item->getTypeId() === ItemTypeIds::GOLDEN_SWORD or
                    $item->getTypeId() === ItemTypeIds::DIAMOND_SWORD or
                    $item->getTypeId() === ItemTypeIds::WOODEN_AXE or
                    $item->getTypeId() === ItemTypeIds::STONE_AXE or
                    $item->getTypeId() === ItemTypeIds::IRON_AXE or
                    $item->getTypeId() === ItemTypeIds::GOLDEN_AXE or
                    $item->getTypeId() === ItemTypeIds::DIAMOND_AXE
                ) {
                    return true;
                }
                break;
            case ItemFlags::BOW:
                if ($item->getTypeId() === ItemTypeIds::BOW) {
                    return true;
                }
                break;
            case ItemFlags::DIG:
                if (
                    $item->getTypeId() === ItemTypeIds::WOODEN_PICKAXE or
                    $item->getTypeId() === ItemTypeIds::STONE_PICKAXE or
                    $item->getTypeId() === ItemTypeIds::IRON_PICKAXE or
                    $item->getTypeId() === ItemTypeIds::GOLDEN_PICKAXE or
                    $item->getTypeId() === ItemTypeIds::DIAMOND_PICKAXE
                ) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * @param \pocketmine\item\enchantment\Enchantment $enchantment
     * @param int $level
     * @param bool $bolded
     * @param bool $levelRequired
     * 
     * @return string
     */
    public static function getEnchantmentFormat(\pocketmine\item\enchantment\Enchantment $enchantment, string $format, bool $bolded = false): string
    {
        $name = $enchantment->getName() instanceof Translatable ? self::enchantToString($enchantment->getName()->getText()) : $enchantment->getName();
        return $format . ($bolded ? TextFormat::BOLD : "") . $name;
    }

    /**
     * @param string $enchant
     * 
     * @return string
     */
    public static function enchantToString(string $enchant): string
    {
        return match ($enchant) {
            "enchantment.protect.all" => "Protection",
            "enchantment.protect.fire" => "Fire Protection",
            "enchantment.protect.fall" => "Feather Falling",
            "enchantment.protect.explosion" => "Blast Protection",
            "enchantment.protect.projectile" => "Projectile Protection",
            "enchantment.thorns" => "Thorns",
            "enchantment.oxygen" => "Respiration",
            "enchantment.damage.all" => "Sharpness",
            "enchantment.knockback" => "Knockback",
            "enchantment.fire" => "Fire_Aspect",
            "enchantment.digging" => "Efficiency",
            "enchantment.untouching" => "Silk Touch",
            "enchantment.durability" => "Unbreaking",
            "enchantment.arrowDamage" => "Power",
            "enchantment.arrowKnockback" => "Punch",
            "enchantment.arrowFire" => "Flame",
            "enchantment.arrowInfinite" => "Infinity",
            "enchantment.mending" => "Mending",
            "enchantment.curse.vanishing" => "Vanishing",
            "enchantment.swift.sneak" => "Swift Sneak",
            default => $enchant,
        };
    }

    /**
     * @param int $flag
     *
     * @return string
     */
    public static function flagToString(int $flag): string
    {
        return match ($flag) {
            ItemFlags::FEET => "Boots",
            ItemFlags::TORSO => "Chestplate",
            ItemFlags::ARMOR => "Armor",
            ItemFlags::HEAD => "Helmet",
            ItemFlags::SWORD => "Sword",
            ItemFlags::BOW => "Bow",
            ItemFlags::DIG => "Tools",
            default => "None",
        };
    }

    /**
     * @param int $rarity
     *
     * @return string
     */
    public static function rarityToString(int $rarity): string
    {
        return match ($rarity) {
            default => "Common",
            Rarity::UNCOMMON => "Uncommon",
            Rarity::RARE => "Rare",
            Rarity::MYTHIC => "Legendary",
            Enchantment::RARITY_GODLY => "Godly"
        };
    }

    /**
     * @param int $rarity
     *
     * @return string
     */
    public static function rarityToColor(int $rarity): string
    {
        return match ($rarity) {
            default => TextFormat::BLUE,
            Rarity::UNCOMMON => TextFormat::DARK_BLUE,
            Rarity::RARE => TextFormat::LIGHT_PURPLE,
            Rarity::MYTHIC => TextFormat::AQUA,
            Enchantment::RARITY_GODLY => TextFormat::RED
        };
    }

    /**
     * @param Item $item
     *
     * @return Item
     */
    public static function setLoreForItem(Item $item): Item
    {
        $enchantmentsByRarity = [
            Rarity::COMMON => [],
            Rarity::UNCOMMON => [],
            Rarity::RARE => [],
            Rarity::MYTHIC => [],
            Enchantment::RARITY_GODLY => []
        ];
        foreach ($item->getEnchantments() as $enchantment) {
            $type = $enchantment->getType();
            if ($type instanceof Enchantment) {
                $enchantmentsByRarity[$type->getRarity()][] = $enchantment;
            }
        }
        foreach ($enchantmentsByRarity as &$enchantments) {
            usort($enchantments, function ($a, $b) {
                return $a->getLevel() - $b->getLevel();
            });
        }
        $enchantments = array_merge(...array_values($enchantmentsByRarity));
        $lore = [];
        foreach ($enchantments as $enchantment) {
            $enchantmentType = $enchantment->getType();
            $lore[] = TextFormat::RESET . ItemHandler::rarityToColor($enchantmentType->getRarity()) . $enchantmentType->getName() . " " . ItemHandler::getRomanNumber($enchantment->getLevel());
        }
        $tag = $item->getNamedTag();
        if ($item instanceof Durable) {   // "Unknown CE Prevention"
            $scrollAmount = $tag !== null && isset($tag->getValue()[EnchantmentScroll::SCROLL_AMOUNT]) ? $tag->getInt(EnchantmentScroll::SCROLL_AMOUNT) : self::DEFAULT_ENCHANT_LIMIT;
            $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Enchantments: " . TextFormat::RESET . TextFormat::YELLOW . count($item->getEnchantments()) . "/" . $scrollAmount;
        }
        if ($item instanceof Armor && $item->getMaterial() === VanillaArmorMaterials::DIAMOND()) {
            $attributes = [
                "hasDamagerAttribute" => ["color" => TextFormat::DARK_RED, "name" => "Damager", "description" => "Increases damage by 5%"],
                "hasSprinterAttribute" => ["color" => TextFormat::DARK_AQUA, "name" => "Sprinter", "description" => "Increases speed by 15%"],
                "hasDefenderAttribute" => ["color" => TextFormat::GREEN, "name" => "Defender", "description" => "Reduces incoming damage by 4%"],
                "hasBossAttribute" => ["color" => TextFormat::RED, "name" => "Boss Killer", "description" => "Increases damage against bosses by 10%"],
                "hasKothAttribute" => ["color" => TextFormat::RED, "name" => "Koth", "description" => "Increases outgoing damage by 4% and decreases incoming damage by 3%"]
            ];
            $hasAttributes = false;
            foreach ($attributes as $tagName => $attr) {
                $tag = $item->getNamedTag()->getTag($tagName);
                if ($tag && $tag->getValue() === "true") {
                    if (!$hasAttributes) {
                        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "Attributes:";
                        $lore[] = TextFormat::RESET . TextFormat::ITALIC . TextFormat::GRAY . "(Must be on every piece to activate)";
                        $hasAttributes = true;
                    }
                    $lore[] = TextFormat::RESET . TextFormat::BOLD . $attr["color"] . " " . $attr["name"];
                    $lore[] = TextFormat::RESET . $attr["color"] . " * " . $attr["description"];
                }
            }
        }
        $effectMap = [
            0 => ["color" => TextFormat::GRAY, "name" => "Green", "type" => "Particles"],
            1 => ["color" => TextFormat::GRAY, "name" => "Red", "type" => "Particles"],
            2 => ["color" => TextFormat::GRAY, "name" => "Blue", "type" => "Particles"],
            3 => ["color" => TextFormat::GRAY, "name" => "Yellow", "type" => "Particles"],
            4 => ["color" => TextFormat::GRAY, "name" => "Pink", "type" => "Particles"],
            5 => ["color" => TextFormat::GRAY, "name" => "White", "type" => "Particles"],
            6 => ["color" => TextFormat::GRAY, "name" => "Black", "type" => "Particles"],
            7 => ["color" => TextFormat::GREEN, "name" => "Dark Blue", "type" => "Particles"],
            8 => ["color" => TextFormat::GREEN, "name" => "Dark Green", "type" => "Particles"],
            9 => ["color" => TextFormat::GREEN, "name" => "Dark Aqua", "type" => "Particles"],
            10 => ["color" => TextFormat::GREEN, "name" => "Dark Red", "type" => "Particles"],
            11 => ["color" => TextFormat::GREEN, "name" => "Purple", "type" => "Particles"],
            12 => ["color" => TextFormat::GREEN, "name" => "Gold", "type" => "Particles"],
            13 => ["color" => TextFormat::GREEN, "name" => "Aqua", "type" => "Particles"],
            14 => ["color" => TextFormat::BLUE, "name" => "Critical", "type" => "Particles"],
            15 => ["color" => TextFormat::BLUE, "name" => "Portal", "type" => "Particles"],
            16 => ["color" => TextFormat::BLUE, "name" => "Ink", "type" => "Particles"],
            17 => ["color" => TextFormat::BLUE, "name" => "Lava", "type" => "Particles"],
            18 => ["color" => TextFormat::BLUE, "name" => "Water", "type" => "Particles"],
            19 => ["color" => TextFormat::LIGHT_PURPLE, "name" => "Heart", "type" => "Particles"],
            20 => ["color" => TextFormat::LIGHT_PURPLE, "name" => "Flame", "type" => "Particles"],
            21 => ["color" => TextFormat::LIGHT_PURPLE, "name" => "Smoke", "type" => "Particles"],
            22 => ["color" => TextFormat::LIGHT_PURPLE, "name" => "Lavabomb", "type" => "Particles"],
        ];
        foreach ($effectMap as $effectValue => $effect) {
            if (($item instanceof Sword && $item->getTypeId() === ItemTypeIds::DIAMOND_SWORD) ||
                ($item instanceof Armor && $item->getTypeId() === ItemTypeIds::DIAMOND_BOOTS) ||
                ($item instanceof Armor && $item->getTypeId() === ItemTypeIds::DIAMOND_HELMET)
            ) {
                if ($item instanceof Sword && $item->getTypeId() === ItemTypeIds::DIAMOND_SWORD) {
                    $particleType = "Hit";
                } elseif ($item instanceof Armor && $item->getTypeId() === ItemTypeIds::DIAMOND_BOOTS) {
                    $particleType = "Trail";
                } elseif ($item instanceof Armor && $item->getTypeId() === ItemTypeIds::DIAMOND_HELMET) {
                    $particleType = "Halo";
                }
                if ($item->getNamedTag()->getTag("hasCosmeticEffect")) {
                    $tagValue = $item->getNamedTag()->getTag("hasCosmeticEffect")->getValue();
                    if ($tagValue === $effectValue) {
                        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::GOLD . "Effect: " . TextFormat::RESET . $effect["color"] . $effect["name"] . " " . $particleType . " " . $effect["type"];
                    }
                }
            }
        }
        $item->setLore($lore);
        return $item;
    }

    /**
     * @param string $item
     */
    public function registerItem(string $item): void
    {
        $this->items[$item] = $item;
    }

    /**
     * @param CompoundTag $tag
     *
     * @return string|null
     */
    public function matchItem(CompoundTag $tag): ?string
    {
        if ($tag->getTag("ItemClass") instanceof Tag) {
            $class = $tag->getString("ItemClass");
            if (isset($this->items[$class])) {
                $item = $this->items[$class];
                return $item;
            }
        }
        return null;
    }

    /**
     * @param int $rarity
     *
     * @return float
     */
    public static function rarityToMultiplier(int $rarity): float
    {
        return match ($rarity) {
            Rarity::COMMON => 1,
            Rarity::UNCOMMON => 1.25,
            Rarity::RARE => 1.5,
            Rarity::MYTHIC => 2,
            default => 0,
        };
    }

    /**
     * @param int 
     * @param string 
     * 
     * @return int 
     */
    public static function getFuelAmountByTier(int $tier, string $fuelType): int
    {
        switch ($fuelType) {
            case 'tnt':
                return match ($tier) {
                    1 => 2,
                    2 => 4,
                    3 => 8,
                    default => 1,
                };
            case 'sponge':
                return match ($tier) {
                    1 => 2,
                    2 => 4,
                    3 => 6,
                    default => 1,
                };
            case 'water':
                return match ($tier) {
                    1 => 1,
                    2 => 3,
                    3 => 5,
                    default => 1,
                };
            case 'lava':
                return match ($tier) {
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    default => 1,
                };
            default:
                return 1;
        }
    }

    /**
     * @param int
     * 
     * @return ?Particle 
     */
    public static function getParticleForEffectId(int $effectId): ?Particle
    {
        $particleMapping = [
            0 => new DustParticle(new Color(85, 255, 85)),
            1 => new DustParticle(new Color(255, 85, 85)),
            2 => new DustParticle(new Color(85, 85, 255)),
            3 => new DustParticle(new Color(255, 255, 85)),
            4 => new DustParticle(new Color(255, 85, 255)),
            5 => new DustParticle(new Color(255, 255, 255)),
            6 => new DustParticle(new Color(0, 0, 0)),
            7 => new DustParticle(new Color(0, 0, 170)),
            8 => new DustParticle(new Color(0, 170, 0)),
            9 => new DustParticle(new Color(0, 170, 170)),
            10 => new DustParticle(new Color(170, 0, 0)),
            11 => new DustParticle(new Color(170, 0, 170)),
            12 => new DustParticle(new Color(255, 170, 0)),
            13 => new DustParticle(new Color(85, 255, 255)),
            14 => new CriticalParticle(1),
            15 => new PortalParticle(),
            16 => new InkParticle(1),
            17 => new LavaDripParticle(),
            18 => new WaterParticle(),
            19 => new HeartParticle(0),
            20 => new FlameParticle(),
            21 => new AngryVillagerParticle(),
            22 => new LavaParticle(),
        ];
        return $particleMapping[$effectId] ?? null;
    }
}
