<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\World\Tile;

use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class Generator extends Spawnable {

    /** @var string */
    public const TAG_STACK = "Stack";
    public const TAG_SPECIAL_XP_LOCKED = "Tag Special Xp Locked";
    public const TAG_SPECIAL_MONEY_LOCKED = "Tag Special Money Locked";

    public const TAG_SPECIAL_XP_LEVEL = "Tag Special Xp Level";

    public const TAG_SPECIAL_MONEY_LEVEL = "Tag Special Money Level";

    public const TAG_LAST_CLICKED = "Tag Last Clicked";

    public const TAG_ENCHANT_GENERATOR_RARITY_LEVEL = "tag enchant generator rarity level";

    public const TAG_ENCHANT_GENERATOR_XP_STORAGE_LEVEL = "tag enchant generator xp storage level";

    public const TAG_ENCHANT_GENERATOR_PRODUCTION_COST_LEVEL = "tag enchant generator production cost level";
    
    public const TAG_ENCHANT_GENERATOR_XP_STORAGE = "tag enchant generator xp storage";


    /** @var int */
    private $stack = 1;

    private $specialXpLocked = 0;

    private $specialMoneyLocked = 0;

    private $specialXpLevel = 1;

    private $specialMoneyLevel = 1;

    private $lastClicked = 0;


    private $enchantGeneratorRarityLevel = 1;

    private $enchantGeneratorXpStorageLevel = 1;
    
    private $enchantGeneratorProductionCostLevel = 1;

    private $enchantGeneratorXpStorage = 0;


    /**
     * Generator constructor.
     *
     * @param World $world
     * @param Vector3 $pos
     */
    public function __construct(World $level, Vector3 $position) {
        parent::__construct($level, $position);
    }

    /**
     * @return bool
     */
    public function onUpdate(): bool {
        return true;
    }

    /**
     * @return int
     */
    public function getStack(): int {
        return $this->stack;
    }

    public function getSpecialXpLocked(): int {
        return $this->specialXpLocked;
    }

    public function getSpecialMoneyLocked(): int {
        return $this->specialMoneyLocked;
    }

    public function getSpecialXpLevel(): int {
        return $this->specialXpLevel;
    }

    public function getSpecialMoneyLevel(): int {
        return $this->specialMoneyLevel;
    }

    public function getLastClicked(): int {
        return $this->lastClicked;
    }

    public function getCostForLevel(int $level): int{
        return [10000000, 45000000, 90000000, 130000000, 200000000, 325000000, 450000000, 600000000, 800000000, 1000000000][$level - 1];
    }

    public function getCostForUnlock(): int {
        return 300000000;
    }

    public function getEnchantGeneratorRarityLevel(): int {
        return $this->enchantGeneratorRarityLevel;
    }

    public function getEnchantGeneratorXpStorageLevel(): int {
        return $this->enchantGeneratorXpStorageLevel;
    }

    public function getEnchantGeneratorProductionCostLevel(): int {
        return $this->enchantGeneratorProductionCostLevel;
    }

    public function getEnchantGeneratorXpStorage(): int {
        return $this->enchantGeneratorXpStorage;
    }

    public function getEnchantGeneratorMaxXpStorage(int $level): int {
        return [100000, 500000, 1000000, 2500000, 5000000, 7500000, 10000000, 12500000, 15000000, 20000000][$level - 1];
    }

    public function getEnchantGeneratorCostPerLevel(int $level): int {
        return [100000, 500000, 1000000, 2000000, 5000000, 7500000, 10000000, 15000000, 20000000, 30000000][$level - 1];
    }

    public function getEnchantGeneratorCostPerProduction(int $level){
        return [20000, 18500, 17000, 15500, 14000, 12500, 11000, 9500, 7000, 5500][$level - 1];
    }

    /**
     * @param int $stack
     */
    public function setStack(int $stack): void {
        $this->stack = $stack;
    }

    public function setSpecialXpLocked(int $specialXpLocked): void {
        $this->specialXpLocked = $specialXpLocked;
    }

    public function setSpecialMoneyLocked(int $specialMoneyLocked): void {
        $this->specialMoneyLocked = $specialMoneyLocked;
    }

    public function setSpecialXpLevel(int $specialXpLevel): void {
        $this->specialXpLevel = $specialXpLevel;
    }

    public function setSpecialMoneyLevel(int $specialMoneyLevel): void {
        $this->specialMoneyLevel = $specialMoneyLevel;
    }

    public function setLastClicked(int $lastClicked): void {
        $this->lastClicked = $lastClicked;
    }

    public function setEnchantGeneratorRarityLevel(int $level): void {
        $this->enchantGeneratorRarityLevel = $level;
    }

    public function setEnchantGeneratorXpStorageLevel(int $level): void {
        $this->enchantGeneratorXpStorageLevel = $level;
    }

    public function setEnchantGeneratorProductionCostLevel(int $level): void {
        $this->enchantGeneratorProductionCostLevel = $level;
    }

    public function setEnchantGeneratorXpStorage(int $amount): void {
        $this->enchantGeneratorXpStorage = $amount;
    }

    /**
     * @param CompoundTag $nbt
     */
    public function readSaveData(CompoundTag $nbt): void {
        if(!$nbt->getTag(self::TAG_STACK) === null) {
            $nbt->setInt(self::TAG_STACK, $this->stack);
        }
        if(!$nbt->getTag(self::TAG_SPECIAL_XP_LOCKED) === null) {
            $nbt->setInt(self::TAG_SPECIAL_XP_LOCKED, $this->specialXpLocked);
        }
        if(!$nbt->getTag(self::TAG_SPECIAL_MONEY_LOCKED) === null) {
            $nbt->setInt(self::TAG_SPECIAL_MONEY_LOCKED, $this->specialMoneyLocked);
        }
        if(!$nbt->getTag(self::TAG_SPECIAL_XP_LEVEL) === null) {
            $nbt->setInt(self::TAG_SPECIAL_XP_LEVEL, $this->specialXpLevel);
        }
        if(!$nbt->getTag(self::TAG_SPECIAL_MONEY_LEVEL) === null) {
            $nbt->setInt(self::TAG_SPECIAL_MONEY_LEVEL, $this->specialMoneyLevel);
        }
        if(!$nbt->getTag(self::TAG_LAST_CLICKED) === null) {
            $nbt->setInt(self::TAG_LAST_CLICKED, $this->lastClicked);
        }
        if(!$nbt->getTag(self::TAG_ENCHANT_GENERATOR_RARITY_LEVEL) === null) {
            $nbt->setInt(self::TAG_ENCHANT_GENERATOR_RARITY_LEVEL, $this->enchantGeneratorRarityLevel);
        }
        if(!$nbt->getTag(self::TAG_ENCHANT_GENERATOR_XP_STORAGE_LEVEL) === null) {
            $nbt->setInt(self::TAG_ENCHANT_GENERATOR_XP_STORAGE_LEVEL, $this->enchantGeneratorXpStorageLevel);
        }
        if(!$nbt->getTag(self::TAG_ENCHANT_GENERATOR_PRODUCTION_COST_LEVEL) === null) {
            $nbt->setInt(self::TAG_ENCHANT_GENERATOR_PRODUCTION_COST_LEVEL, $this->enchantGeneratorProductionCostLevel);
        }
        if(!$nbt->getTag(self::TAG_ENCHANT_GENERATOR_XP_STORAGE) === null) {
            $nbt->setInt(self::TAG_ENCHANT_GENERATOR_XP_STORAGE, $this->enchantGeneratorXpStorage);
        }
        $this->stack = $nbt->getInt(self::TAG_STACK, $this->stack);
        $this->specialXpLocked = $nbt->getInt(self::TAG_SPECIAL_XP_LOCKED, $this->specialXpLocked);
        $this->specialMoneyLocked = $nbt->getInt(self::TAG_SPECIAL_MONEY_LOCKED, $this->specialMoneyLocked);
        $this->specialXpLevel = $nbt->getInt(self::TAG_SPECIAL_XP_LEVEL, $this->specialXpLevel);
        $this->specialMoneyLevel = $nbt->getInt(self::TAG_SPECIAL_MONEY_LEVEL, $this->specialMoneyLevel);
        $this->lastClicked = $nbt->getInt(self::TAG_LAST_CLICKED, $this->lastClicked);
        $this->enchantGeneratorRarityLevel = $nbt->getInt(self::TAG_ENCHANT_GENERATOR_RARITY_LEVEL, $this->enchantGeneratorRarityLevel);
        $this->enchantGeneratorXpStorageLevel = $nbt->getInt(self::TAG_ENCHANT_GENERATOR_XP_STORAGE_LEVEL, $this->enchantGeneratorXpStorageLevel);
        $this->enchantGeneratorProductionCostLevel = $nbt->getInt(self::TAG_ENCHANT_GENERATOR_PRODUCTION_COST_LEVEL, $this->enchantGeneratorProductionCostLevel);
        $this->enchantGeneratorXpStorage = $nbt->getInt(self::TAG_ENCHANT_GENERATOR_XP_STORAGE, $this->enchantGeneratorXpStorage);
    }

    /**
     * @param CompoundTag $nbt
     */
    protected function writeSaveData(CompoundTag $nbt): void {
        $nbt->setInt(self::TAG_STACK, $this->getStack());
        $nbt->setInt(self::TAG_SPECIAL_XP_LOCKED, $this->getSpecialXpLocked());
        $nbt->setInt(self::TAG_SPECIAL_MONEY_LOCKED, $this->getSpecialMoneyLocked());
        $nbt->setInt(self::TAG_SPECIAL_XP_LEVEL, $this->getSpecialXpLevel());
        $nbt->setInt(self::TAG_SPECIAL_MONEY_LEVEL, $this->getSpecialMoneyLevel());
        $nbt->setInt(self::TAG_LAST_CLICKED, $this->getLastClicked());
        $nbt->setInt(self::TAG_ENCHANT_GENERATOR_RARITY_LEVEL, $this->getEnchantGeneratorRarityLevel());
        $nbt->setInt(self::TAG_ENCHANT_GENERATOR_XP_STORAGE_LEVEL, $this->getEnchantGeneratorXpStorageLevel());
        $nbt->setInt(self::TAG_ENCHANT_GENERATOR_PRODUCTION_COST_LEVEL, $this->getEnchantGeneratorProductionCostLevel());
        $nbt->setInt(self::TAG_ENCHANT_GENERATOR_XP_STORAGE, $this->getEnchantGeneratorXpStorage());
    }

    /**
     * @param CompoundTag $nbt
     */
    protected function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->setInt(self::TAG_STACK, $this->getStack());
        $nbt->setInt(self::TAG_SPECIAL_XP_LOCKED, $this->getSpecialXpLocked());
        $nbt->setInt(self::TAG_SPECIAL_MONEY_LOCKED, $this->getSpecialMoneyLocked());
        $nbt->setInt(self::TAG_SPECIAL_XP_LEVEL, $this->getSpecialXpLevel());
        $nbt->setInt(self::TAG_SPECIAL_MONEY_LEVEL, $this->getSpecialMoneyLevel());
        $nbt->setInt(self::TAG_LAST_CLICKED, $this->getLastClicked());
        $nbt->setInt(self::TAG_ENCHANT_GENERATOR_RARITY_LEVEL, $this->getEnchantGeneratorRarityLevel());
        $nbt->setInt(self::TAG_ENCHANT_GENERATOR_XP_STORAGE_LEVEL, $this->getEnchantGeneratorXpStorageLevel());
        $nbt->setInt(self::TAG_ENCHANT_GENERATOR_PRODUCTION_COST_LEVEL, $this->getEnchantGeneratorProductionCostLevel());
        $nbt->setInt(self::TAG_ENCHANT_GENERATOR_XP_STORAGE, $this->getEnchantGeneratorXpStorage());
    }
}