<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\World\Block;

use Xekvern\Core\Nexus;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\GlazedTerracotta;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\tile\Tile;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\BlockTransaction;
use Xekvern\Core\Server\Item\Types\LuckyBlock;
use Xekvern\Core\Server\World\WorldHandler;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\EnchantmentCrystal;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Server\Item\Types\OreGenerator;
use Xekvern\Core\Server\World\Tile\Generator as TileGenerator;

class Generator extends GlazedTerracotta
{

    const AUTO = 0;

    const MINING = 1;

    const SPECIAL = 2;

    const SPECIAL2 = 3;

    /** @var Item */
    private $generatedItem;

    /** @var int */
    private $type;

    /**
     * Generator constructor.
     *
     * @param int $id
     * @param Item $generatedItem
     */
    public function __construct(int $id)
    {
        $idInfo = new BlockIdentifier($id, \Xekvern\Core\Server\World\Tile\Generator::class);
        $name = "Generator";
        $blockInfo = new BlockBreakInfo(1, BlockToolType::PICKAXE, 0, 1);
        $typeInfo = new BlockTypeInfo($blockInfo);
        parent::__construct($idInfo, $name, $typeInfo);
    }

    /**
     * @param Item $item
     * @param Player|null $player
     *
     * @return bool
     */
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if ($tile instanceof LuckyBlock) {
            return false;
        }
        if ($this->getColor() === DyeColor::BLACK()) {
            return false;
        }
        if($tile instanceof \Xekvern\Core\Server\World\Tile\Generator) {
            if($this->asItem()->getStateId() === $item->getStateId() and $tile->getStack() < 64) {
                $stack = $tile->getStack();
                $add = 1;
                if ($player->isSneaking()) {
                    $add = $item->getCount();
                }
                if ($add + $stack > 64) {
                    $add = 64 - $stack;
                }
                if ($tile->getStack() < 64) {
                    $stack += $add;
                    $tile->setStack($stack);
                    $player->getInventory()->setItemInHand($item->setCount($item->getCount() - $add));
                    return false;
                }
                $player->sendMessage(Translation::AQUA . "You have stacked " . TextFormat::GREEN . "+ " . $add . TextFormat::GRAY . " to this generator.");
            } else {
                $stack = $tile->getStack();
                $cost = (25/100) * WorldHandler::getGeneratorValue($this->getColor());
                $player->sendMessage(Translation::ORANGE . "There are " . TextFormat::YELLOW . "x" . $stack . TextFormat::GRAY . " stacked in this generator.");
                $player->sendMessage(TextFormat::YELLOW . "(This generator will cost atleast $" . number_format($cost) . " to break.)");
            }
            $bounds = $this->getCollisionBoxes();
            foreach ($bounds as $bound) {
                $bound->expandedCopy(5, 5, 5);
            }
            foreach (WorldHandler::getNearbyTiles($this->getPosition()->getWorld(), $bound) as $tile) {
                $block = $tile->getBlock();
                if ($block instanceof Generator and $tile instanceof \Xekvern\Core\Server\World\Tile\Generator) {
                    $claim = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaimInPosition($tile->getPosition());
                    if ($claim !== null) {
                        $chunk = $this->getPosition()->getWorld()->getChunk($claim->getChunkX(), $claim->getChunkZ());
                        if ($chunk !== null) {
                            $claim->recalculateValue($chunk);
                        }
                    }
                    return false;
                }
            }
        } else {
            $tile = new \Xekvern\Core\Server\World\Tile\Generator($this->getPosition()->getWorld(), $this->getPosition());
            $this->getPosition()->getWorld()->addTile($tile);
        }
        return true;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if ($this->getColor()->equals(DyeColor::BLACK())) {
            return false;
        }
        if($clickVector->getY() > 255) {
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "NOTICE", TextFormat::GRAY . "You cannot place this block more than 255 yaw");
            return false;
        }
        if (Nexus::getInstance()->getServerManager()->getFundHandler()->getMergeFund("Auto Generators")->isFunded() === false && WorldHandler::getGeneratorTypeString(WorldHandler::getGeneratorType($this->getColor())) === "Automatic") {
            /** @var NexusPlayer $player */
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "NOTICE", TextFormat::GRAY . "The goal for this feature has not be funded!");
            $player->sendMessage(Translation::getMessage("notReachedGoal"));
            $player->playErrorSound();
            return false;
        }
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if (!$tile instanceof \Xekvern\Core\Server\World\Tile\Generator) {
            $tile = new \Xekvern\Core\Server\World\Tile\Generator($this->getPosition()->getWorld(), $this->getPosition());
            $this->getPosition()->getWorld()->addTile($tile);
        } 
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onScheduledUpdate(): void
    {
        if ($this->getColor()->equals(DyeColor::BLACK())) {
            return;
        }
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        $block = $this->getPosition()->getWorld()->getBlock($this->getPosition());
        $count = 1;
        if ($tile instanceof \Xekvern\Core\Server\World\Tile\Generator) {
            $count = $tile->getStack();
        }
        if ($block instanceof GlazedTerracotta and WorldHandler::getGeneratorType($block->getColor()) === \Xekvern\Core\Server\World\Block\Generator::SPECIAL2) {
            $vector = $this->getSide(Facing::DOWN);
            $tile = $this->getPosition()->getWorld()->getTile($vector->getPosition());
            $generator = $this->getPosition()->getWorld()->getTile($block->getPosition());
            /** @var \Xekvern\Core\Server\World\Tile\Generator $generator */
            $xpamount = $generator->getEnchantGeneratorCostPerProduction($generator->getEnchantGeneratorProductionCostLevel());
            $xpstorage = $generator->getEnchantGeneratorXpStorage();
            if ($xpamount > $xpstorage) {
                $generator->getPosition()->getWorld()->scheduleDelayedBlockUpdate($generator->getPosition(), 200);
            } else {
                if ($tile instanceof Container) {
                    $inventory = $tile->getInventory();
                    $rarityLevel = $generator->getEnchantGeneratorRarityLevel();

                    // Set available rarities based on the rarity level
                    $availableRarities = [];
                    switch ($rarityLevel) {
                        case 1:
                            $availableRarities = [Rarity::COMMON];
                            break;
                        case 2:
                            $availableRarities = [Rarity::COMMON, Rarity::UNCOMMON];
                            break;
                        case 3:
                            $availableRarities = [Rarity::COMMON, Rarity::UNCOMMON, Rarity::RARE];
                            break;
                        case 4:
                            $availableRarities = [Rarity::COMMON, Rarity::UNCOMMON, Rarity::RARE, Rarity::MYTHIC];
                            break;
                        case 5:
                            $availableRarities = [Rarity::COMMON, Rarity::UNCOMMON, Rarity::RARE, Rarity::MYTHIC, Enchantment::RARITY_GODLY];
                            break;
                    }

                    // Set weights for each rarity
                    $weightedRarities = [
                        Rarity::COMMON => 5,
                        Rarity::UNCOMMON => 4,
                        Rarity::RARE => 3,
                        Rarity::MYTHIC => 2,
                        Enchantment::RARITY_GODLY => 1,
                    ];

                    // Filter out unavailable rarities from the weighted array
                    $filteredWeights = array_intersect_key($weightedRarities, array_flip($availableRarities));

                    // If no available rarities, use a default rarity
                    if (empty($filteredWeights)) {
                        $enchantment = ItemHandler::getRandomEnchantment(Rarity::COMMON);
                    } else {
                        $totalWeight = array_sum($filteredWeights);
                        $randomValue = mt_rand(1, $totalWeight);

                        $runningTotal = 0;
                        foreach ($filteredWeights as $rarity => $weight) {
                            $runningTotal += $weight;

                            if ($randomValue <= $runningTotal) {
                                $enchantment = ItemHandler::getRandomEnchantment($rarity);
                                break;
                            }
                        }
                    }
                    $item = (new EnchantmentCrystal($enchantment))->getItemForm();
                    if ($inventory->canAddItem($item)) {
                        $inventory->addItem($item);
                        $generator->setEnchantGeneratorXpStorage($xpstorage - $xpamount);
                        $tile->getPosition()->getWorld()->scheduleDelayedBlockUpdate($generator->getPosition(), 25);
                    }
                }
            }
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 2000);
        } elseif ($block instanceof GlazedTerracotta and WorldHandler::getGeneratorType($block->getColor()) === \Xekvern\Core\Server\World\Block\Generator::AUTO) {
            $fundHandler = Nexus::getInstance()->getServerManager()->getFundHandler();
            $vector = $this->getSide(Facing::DOWN);
            $tile = $this->getPosition()->getWorld()->getTile($vector->getPosition());
            if ($tile instanceof Container) {
                $inventory = $tile->getInventory();
                $item = $this->getGeneratorOreByTypeToItem($this->getColor())->setCount(1 + (int)(ceil(round($count / 4)))); // 16 count per ticks
                if ($inventory->canAddItem($item)) {
                    $inventory->addItem($item);
                    $tile->getPosition()->getWorld()->scheduleDelayedBlockUpdate($tile->getPosition(), 25);
                }
            }
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), (int)(130 - round($count / 2)));
        } elseif ($block instanceof GlazedTerracotta and WorldHandler::getGeneratorType($block->getColor()) === \Xekvern\Core\Server\World\Block\Generator::MINING) {
            if ($this->getPosition()->getWorld()->getBlock($this->getPosition()->add(0, 1, 0))->getTypeId() === VanillaBlocks::AIR()->getTypeId() && $this->getPosition()->getY() < 255) {
                $this->getPosition()->getWorld()->setBlock($this->getPosition()->add(0, 1, 0), $this->getGeneratorOreByType($block->getColor()));
            }
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), (int)round(130 - (int)round($count * 2)));
        } elseif ($block instanceof GlazedTerracotta and WorldHandler::getGeneratorType($block->getColor()) === \Xekvern\Core\Server\World\Block\Generator::SPECIAL) {
            if ($this->getPosition()->getWorld()->getBlock($this->getPosition()->add(0, 1, 0))->getTypeId() === VanillaBlocks::AIR()->getTypeId() && $this->getPosition()->getY() < 255) {
                if (Nexus::getInstance()->getServerManager()->getFundHandler()->getMergeFund("Auto Generators")->isFunded() === false) {
                    return;
                }
                $this->getPosition()->getWorld()->setBlock($this->getPosition()->add(0, 1, 0), WorldHandler::getGeneratorOreByType($block->getColor()));
            }
            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), (int)round(5 - (int)round($count * 2)));
        }
    }

    /**
     * @param Item $item
     * @param Player|null $player
     *
     * @return bool
     */
    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        /** @var Xekvern\Core\Server\World\Tile\Generator $tile */
        /** @var NexusPlayer $player */
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        $block = $this->getPosition()->getWorld()->getBlock($this->getPosition());
        if ($block instanceof \Xekvern\Core\Server\World\Block\Generator && WorldHandler::getGeneratorType($block->getColor()) === \Xekvern\Core\Server\World\Block\Generator::SPECIAL && $tile instanceof TIleGenerator) {
            $points = 0;

            // Check if $tile has the method getSpecialXpLocked
            if ($tile !== null && method_exists($tile, 'getSpecialXpLocked')) {
                if ($tile->getSpecialXpLocked() === 1) {
                    $points += $tile->getCostForUnlock();
                }
            }

            // Repeat the same check for other methods
            if ($tile !== null && method_exists($tile, 'getSpecialMoneyLocked')) {
                if ($tile->getSpecialMoneyLocked() === 1) {
                    $points += $tile->getCostForUnlock();
                }
            }

            // Repeat the same check for other methods
            if ($tile !== null && method_exists($tile, 'getSpecialXpLevel')) {
                if ($tile->getSpecialXpLevel() > 0) {
                    $totalCost = 0;
                    for ($level = 1; $level <= $tile->getSpecialXpLevel() - 1; $level++) {
                        $totalCost += $tile->getCostForLevel($level);
                    }
                    $points += $totalCost;
                }
            }

            // Repeat the same check for other methods
            if ($tile !== null && method_exists($tile, 'getSpecialMoneyLevel')) {
                if ($tile->getSpecialMoneyLevel() > 0) {
                    $totalCost = 0;
                    for ($level = 1; $level <= $tile->getSpecialMoneyLevel() - 1; $level++) {
                        $totalCost += $tile->getCostForLevel($level);
                    }
                    $points += $totalCost;
                }
            }
            $player->sendMessage(TextFormat::DARK_GREEN . "Added $" . number_format((int)$points) . " to balance");
            $player->getDataSession()->addToBalance($points);
        }
        if ($block instanceof \Xekvern\Core\Server\World\Block\Generator && WorldHandler::getGeneratorType($block->getColor()) === \Xekvern\Core\Server\World\Block\Generator::SPECIAL2 && $tile instanceof TileGenerator) {
            $points = 0;
            if ($tile !== null && method_exists($tile, 'getEnchantGeneratorCostPerLevel')) {
                if ($tile->getEnchantGeneratorRarityLevel() > 0) {
                    $totalCost = 0;
                    for ($level = 1; $level <= $tile->getEnchantGeneratorRarityLevel() - 1; $level++) {
                        $totalCost += $tile->getEnchantGeneratorCostPerLevel($level);
                    }
                    $points += $totalCost;
                }
            }
            if ($tile !== null && method_exists($tile, 'getEnchantGeneratorCostPerLevel')) {
                if ($tile->getEnchantGeneratorXpStorageLevel() > 0) {
                    $totalCost = 0;
                    for ($level = 1; $level <= $tile->getEnchantGeneratorXpStorageLevel() - 1; $level++) {
                        $totalCost += $tile->getEnchantGeneratorCostPerLevel($level);
                    }
                    $points += $totalCost;
                }
            }
            if ($tile !== null && method_exists($tile, 'getEnchantGeneratorCostPerLevel')) {
                if ($tile->getEnchantGeneratorProductionCostLevel() > 0) {
                    $totalCost = 0;
                    for ($level = 1; $level <= $tile->getEnchantGeneratorProductionCostLevel() - 1; $level++) {
                        $totalCost += $tile->getEnchantGeneratorCostPerLevel($level);
                    }
                    $points += $totalCost;
                }
            }
            $player->sendMessage(TextFormat::DARK_GREEN . "Added XP(" . number_format((int)$points) . ") to balance");
            $player->getXpManager()->addXp($points);
        }
        if ($tile !== null) {
            $this->getPosition()->getWorld()->removeTile($tile);
        }
        return parent::onBreak($item, $player, $returnedItems);
    }

    /**
     * @return int
     */
    public function getXpDropAmount(): int
    {
        return 0;
    }

    /**
     * @param Item $item
     *
     * @return Item[]
     */
    public function getDrops(Item $item): array
    {   
        return [];
       // if ($this->getColor()->equals(DyeColor::BLACK())) {
           //return [];
       // }
       // $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
       // $count = 1;
        //if ($tile instanceof \Xekvern\Core\Server\World\Tile\Generator) {
           // $count = $tile->getStack();
        //}
        ///$drop = (new OreGenerator($this->getColor()))->getItemForm()->setCount($count);
        //$drop = $this->asItem()->setCount($count);
        //return [$drop];
    }

    /**
     * @return Item
     */
    public function getGeneratedItem(): Item
    {
        return $this->generatedItem;
    }

    /**
     * @param BlockTypeIds $id
     * 
     * @return Block
     */
    public function getGeneratorOreByType(DyeColor $color): Block
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
     * @param BlockTypeIds $id
     * 
     * @return Item
     */
    public function getGeneratorOreByTypeToItem(DyeColor $color): Item
    {
        switch ($color) {
            case DyeColor::BLUE(): //auto
                return VanillaBlocks::COAL()->asItem();
                break;
            case DyeColor::LIGHT_BLUE():
                return VanillaBlocks::REDSTONE()->asItem();
                break;
            case DyeColor::GRAY():
                return VanillaBlocks::IRON()->asItem();
                break;
            case DyeColor::MAGENTA():
                return VanillaBlocks::DIAMOND()->asItem();
                break;
            case DyeColor::GREEN():
                return VanillaBlocks::EMERALD()->asItem();
                break;
            case DyeColor::BROWN(): //manual
                return VanillaItems::COAL();
                break;
            case DyeColor::CYAN():
                return VanillaItems::LAPIS_LAZULI();
                break;
            case DyeColor::LIGHT_GRAY():
                return VanillaItems::IRON_INGOT();
                break;
            case DyeColor::PINK():
                return VanillaItems::DIAMOND();
                break;
            case DyeColor::PURPLE():
                return VanillaItems::AMETHYST_SHARD();
                break;
            case DyeColor::LIME():
                return VanillaItems::EMERALD();
                break;
            default:
                return VanillaBlocks::STONE()->asItem();
                break;
        }
    }
}
