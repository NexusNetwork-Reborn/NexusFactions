<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\World;

use Exception;
use pocketmine\block\Block;
use Xekvern\Core\Nexus;
use Xekvern\Core\Command\Forms\UpgradeSpecialGenerator;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Lava;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\block\Sponge as SpongeBlock;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use pocketmine\math\Facing;
use Xekvern\Core\Server\World\Tile\Generator;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\LuckyBlock;
use Xekvern\Core\Server\Item\Types\SacredStone;
use Xekvern\Core\Server\Item\Types\XPNote;
use Xekvern\Core\Command\Forms\UpgradeEnchantGenerator;
use Xekvern\Core\Server\Item\Types\EnchantmentScroll;
use Xekvern\Core\Server\Item\Types\MonsterSpawner;
use Xekvern\Core\Server\Item\Types\OreGenerator;
use Xekvern\Core\Server\World\Tile\MonsterSpawnerTile;
use Xekvern\Core\Utils\Utils;

class WorldEvents implements Listener
{

    /** @var Nexus */
    private $core;

    /** @var int */
    protected int $spam = 0;
    
    /**
     * WorldEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
    }

    /**
     * @priority HIGHEST
     * @param BlockBreakEvent $event
     *
     * @throws TranslatonException
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        if ($event->isCancelled()) {
            return;
        }
        $player = $event->getPlayer();
        $inventory = $player->getInventory();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if (!$player->isLoaded()) {
            return;
        }
        if (!$inventory->firstEmpty() === -1) {
            return;
        }
        $block = $event->getBlock();
        if ($block->getTypeId() === VanillaBlocks::STONE()->getTypeId()) {
            if ($player->getLuckyBlockToggle() === true) {
                if (Utils::weighted_mt_rand(1, 100, 3) === 100) {
                    $item = new LuckyBlock(mt_rand(25, 100));
                    $player->playDingSound();
                    $player->getDataSession()->addLuckyBlocksMined();
                    $player->sendTip(TextFormat::BOLD . TextFormat::YELLOW . "+ Lucky Block");
                    if (!$player->getInventory()->canAddItem($item->getItemForm())) {
                        $player->getDataSession()->addToInbox($item->getItemForm());
                        $player->sendMessage(Translation::AQUA . "Your inventory is full your item has been added to your /inbox");
                        return;
                    }
                    $player->getInventory()->addItem($item->getItemForm());
                }
            }
            if (Utils::weighted_mt_rand(1, 250, 3) === 250) {
                if ($player->getDataSession()->getInbox()->getInventory()->firstEmpty() === -1) {
                    return;
                }
                $event->cancel();
                $tile = $block->getPosition()->getWorld()->getTile($block->getPosition());
                if(!$tile instanceof TileChest) {
                    $nbt = new TileChest($player->getWorld(), $block->getPosition());
                    $items = [
                        VanillaBlocks::IRON()->asItem()->setCount(8),
                        VanillaBlocks::DIAMOND()->asItem()->setCount(8),
                        VanillaBlocks::GOLD()->asItem()->setCount(8),
                        VanillaBlocks::EMERALD()->asItem()->setCount(6),
                        VanillaItems::IRON_INGOT()->setCount(32),
                        VanillaItems::DIAMOND()->setCount(16),
                        VanillaItems::EMERALD()->setCount(16),
                        VanillaItems::GOLDEN_APPLE()->setCount(32),
                        (new XPNote(mt_rand(5000, 10000)))->getItemForm(),
                        (new EnchantmentBook(ItemHandler::getRandomEnchantment(), 100))->getItemForm(),
                        (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Prince")))->getItemForm(),
                        (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Spartan")))->getItemForm(),
                    ];
                    $inventory = $nbt->getRealInventory();
                    for ($x = 0; $x <= 26; $x++) {
                        if (rand(1, 3) == 2) {
                            $inventory->setItem($x, $items[array_rand($items)]);
                        }
                    };
                    $player->getWorld()->addTile($nbt);
                    $block->getPosition()->getWorld()->setBlock($block->getPosition(), VanillaBlocks::CHEST());
                    $player->playSound("mob.wither.spawn", 1, 1);
                    $player->sendTip(TextFormat::BOLD . TextFormat::GOLD . "+ Treasure Chest");
                    $player->sendTitle(TextFormat::BOLD . TextFormat::GOLD . "Treasure Chest", TextFormat::GRAY . "You have found a treasure chest!");
                }
            }
            if (Utils::weighted_mt_rand(1, 250, 3) === 250) {
                $item = new SacredStone();
                $player->playDingSound();
                $player->sendTip(TextFormat::BOLD . TextFormat::RED . "+ Sacred Stone");
                $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Sacred Stone", TextFormat::GRAY . "You have found a sacred stone!");
                if (!$player->getInventory()->canAddItem($item->getItemForm())) {
                    $player->getDataSession()->addToInbox($item->getItemForm());
                    $player->sendMessage(Translation::AQUA . "Your inventory is full your item has been added to your /inbox");
                    return;
                }
                $player->getInventory()->addItem($item->getItemForm());
            }
        }
        $item = $player->getInventory()->getItemInHand();
        $silkTouch = EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SILK_TOUCH);
        $world = $player->getWorld();
        $blockPos = $block->getPosition();
        $blockAt = $world->getBlock($blockPos);
        $tile = $world->getTile($blockPos);
        if ($tile instanceof MonsterSpawnerTile && ($blockAt->getTypeId() === VanillaBlocks::MONSTER_SPAWNER()->getTypeId())) {
            if ($item->hasEnchantment($silkTouch)) {
                $cost = (25/100) * WorldHandler::getSpawnerValue($tile->getEntityTypeId());
                if ($player->getDataSession()->getBalance() <= (int)$cost * $tile->getStacked()) {
                    $player->playErrorSound();
                    $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Failed to Break", TextFormat::GRAY . "Insufficient money to break this block.");
                    $player->sendMessage(TextFormat::BOLD . TextFormat::RED . "You do not have enough money to break this spawner.");
                    $player->sendMessage(TextFormat::YELLOW . "(This currently has x" . $tile->getStacked() . " of stack that costs $" . number_format($cost * $tile->getStacked()));
                    $event->cancel();
                    return;
                }
                $player->getDataSession()->subtractFromBalance((int)$cost * $tile->getStacked());
                $spawnerItem = (new MonsterSpawner($tile->getEntityTypeId()))->getItemForm();
                $spawnerItem->setCount($tile->getStacked());
                $world->dropItem($blockAt->getPosition(), $spawnerItem);
                $event->setXpDropAmount(0);
                $world->removeTile($tile);
            } 
        }
        if ($tile instanceof \Xekvern\Core\Server\World\Tile\Generator && ($block instanceof \Xekvern\Core\Server\World\Block\Generator)) {
            if ($block->getColor()->equals(DyeColor::BLACK())) { return; }
            $cost = (25/100) * WorldHandler::getGeneratorValue($block->getColor());
            if ($player->getDataSession()->getBalance() <= (int)$cost * $tile->getStack()) {
                $player->playErrorSound();
                $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Failed to Break", TextFormat::GRAY . "Insufficient money to break this block.");
                $player->sendMessage(Translation::RED . "You do not have enough money to break this generator.");
                $player->sendMessage(TextFormat::YELLOW . "(This currently has x" . $tile->getStack() . " of stack that costs $" . number_format((int)$cost * $tile->getStack()));
                $event->cancel();
                return;
            }
            $player->getDataSession()->subtractFromBalance((int)$cost * $tile->getStack());
            $generatorItem = (new OreGenerator($block->getColor()))->getItemForm();
            $generatorItem->setCount($tile->getStack());
            $world->dropItem($blockAt->getPosition(), $generatorItem);
            $event->setXpDropAmount(0);
            $world->removeTile($tile);
        }
    }

    /** 
     * @priority HIGHEST
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if ($block->getTypeId() === BlockTypeIds::ENDER_CHEST) {
            $event->cancel();
            $player->sendMessage(Translation::RED . "Ender chests are disabled! An alternative is /pv!");
            $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "NOTICE", TextFormat::GRAY . "Ender chests are disabled!");
            $player->playErrorSound();
            return;
        }
        $tile = $block->getPosition()->getWorld()->getTile($block->getPosition());
        $item = $event->getItem();
        if ($tile === null) {
            return;
        }
        if ($tile instanceof Generator) {
            if ($block instanceof \Xekvern\Core\Server\World\Block\Generator && WorldHandler::getGeneratorType($block->getColor()) === \Xekvern\Core\Server\World\Block\Generator::SPECIAL) {
                if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                    if (time() - $tile->getLastClicked() > 1) {
                        $tile->setLastClicked(time());
                        $player->sendForm(new UpgradeSpecialGenerator($player, $tile));
                    } else {
                        $event->cancel();
                    }
                }
            }
            if ($block instanceof \Xekvern\Core\Server\World\Block\Generator && WorldHandler::getGeneratorType($block->getColor()) === \Xekvern\Core\Server\World\Block\Generator::SPECIAL2) {
                if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                    if (time() - $tile->getLastClicked() > 1) {
                        $tile->setLastClicked(time());
                        $player->sendForm(new UpgradeEnchantGenerator($player, $tile));
                    } else {
                        $event->cancel();
                    }
                }
            } 
        }
        if ($tile instanceof Generator) {
            if ($event->isCancelled()) {
                return;
            }
            $claim = Nexus::getInstance()->getPlayerManager()->getFactionHandler()->getClaimInPosition($tile->getPosition());
            $level = $tile->getPosition()->getWorld();
            if ($level === null or $claim === null) {
                return;
            }
            $chunk = $level->getChunk($claim->getChunkX(), $claim->getChunkZ());
            if ($chunk !== null) {
                $claim->recalculateValue($chunk);
            }
        }
        try {
            if ($tile instanceof TileChest) {
                if ($block->getSide(Facing::UP)->isTransparent() || !$tile->canOpenWith($item->getCustomName()) || $player->isSneaking()) {
                    return;
                }
                $aboveBlock = $block->getSide(Facing::UP)->getPosition()->getWorld()->getTile($block->getSide(Facing::UP)->getPosition());
                if ($aboveBlock instanceof Generator) {
                    $player->setCurrentWindow($tile->getInventory());
                    return;
                }
            }
        } catch (Exception $exception) {}
    }

    /**
     * @param EntityTeleport $event
     * 
     * @throws UtilsException
     */
    public function onEntityTeleport(EntityTeleportEvent $event): void {
        $entity = $event->getEntity();
        if(!$entity instanceof NexusPlayer) {
            return;
        }
        if($entity->getWorld()->getDisplayName() == $entity->getServer()->getWorldManager()->getDefaultWorld()->getDisplayName()) {
            foreach ($entity->getFloatingTexts() as $floatingText) {
                $floatingText->spawn($entity);
            }
            foreach ($this->core->getServerManager()->getNPCHandler()->getNPCs() as $npc) {
                if (!$npc->isSpawned($entity)) {
                    $npc->spawnTo($entity);
                }
            }
        }
    }


    /**
     * @priority LOWEST
     * @param FurnaceSmeltEvent $event
     */
    public function onFurnaceSmelt(FurnaceSmeltEvent $event): void
    {
        $block = $event->getResult();
        if ($block >= VanillaBlocks::GLAZED_TERRACOTTA()->getColor()->equals(DyeColor::PURPLE()) and $block >= VanillaBlocks::GLAZED_TERRACOTTA()->getColor()->equals(DyeColor::BLACK())) {
            $event->cancel();
        }
    }

    public function onBlockSpread(BlockSpreadEvent $event): void
    {
        $source = $event->getSource();
        $block = $event->getBlock();
        $newState = $event->getNewState();
        $targetBlocks = [$source, $block, $newState];
        if ($source instanceof Water) {
            foreach ($targetBlocks as $targetBlock) {
                $this->findAndAbsorbWaterNearby($targetBlock);
            }
        }
        if ($source instanceof Lava) {
            foreach ($targetBlocks as $targetBlock) {
                $this->findAndAbsorbLavaNearby($targetBlock);
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $blockAgainst = $event->getBlockAgainst();
        $sponge = null;
        foreach ($event->getTransaction()->getBlocks() as [$x, $y, $z, $block]) {
            if ($block instanceof SpongeBlock) {
                $sponge = $block;
                break;
            }
        }
        /** @var SpongeBlock $sponge */
        if ($sponge !== null) {
            $underSponeBlock = $sponge->getPosition()->getWorld()->getBlock($sponge->getPosition()->down());
            $spongeBlockPosition = $sponge->getPosition();
            $world = $spongeBlockPosition->getWorld();
            if (!$sponge->isWet()) {
                if ($blockAgainst instanceof Water) {
                    $this->absorbWater($sponge, $spongeBlockPosition->getX(), $spongeBlockPosition->getY(), $spongeBlockPosition->getZ());
                } elseif ($blockAgainst instanceof Lava) {
                    $this->absorbLava($sponge, $spongeBlockPosition->getX(), $spongeBlockPosition->getY(), $spongeBlockPosition->getZ());
                } elseif (!$underSponeBlock->isSolid()) {
                    $this->absorbWater($sponge, $spongeBlockPosition->getX(), $spongeBlockPosition->getY(), $spongeBlockPosition->getZ());
                }
                foreach ($this->getNearBlocks($sponge) as $block) {
                    if ($block instanceof Water) {
                        $this->absorbWater($sponge, $spongeBlockPosition->getX(), $spongeBlockPosition->getY(), $spongeBlockPosition->getZ());
                    }
                }
            }
        }
    }

    public function findAndAbsorbWaterNearby(Block $targetBlock): void
    {
        foreach ($this->getNearBlocks($targetBlock) as $sponge) {
            if ($sponge instanceof SpongeBlock && !$sponge->isWet()) {
                $spongeBlockPosition = $sponge->getPosition();
                $this->absorbWater($sponge, $spongeBlockPosition->getX(), $spongeBlockPosition->getY(), $spongeBlockPosition->getZ());
                break;
            }
        }
    }
    public function findAndAbsorbLavaNearby(Block $targetBlock): void
    {
        foreach ($this->getNearBlocks($targetBlock) as $sponge) {
            if ($sponge instanceof SpongeBlock && !$sponge->isWet()) {
                $spongeBlockPosition = $sponge->getPosition();
                $this->absorbWater($sponge, $spongeBlockPosition->getX(), $spongeBlockPosition->getY(), $spongeBlockPosition->getZ());
                break;
            }
        }
    }

    public function getNearBlocks(Block $block): array
    {
        $blocks = [];
        $blockPosition = $block->getPosition();
        $world = $blockPosition->getWorld();
        $directions = [
            $blockPosition->down(),
            $blockPosition->up(),
            $blockPosition->west(),
            $blockPosition->east(),
            $blockPosition->north(),
            $blockPosition->south()
        ];
        foreach ($directions as $direction) {
            $blocks[] = $world->getBlock($direction);
        }
        return $blocks;
    }

    public function absorbWater(Block $sponge, float|int $spongeX, float|int $spongeY, float|int $spongeZ): void
    {
        /** @var SpongeBlock $sponge */
        $absorbedWaterCount = 0;
        $spongeBlockPosition = $sponge->getPosition();
        for ($x = $spongeX - 5; $x <= $spongeX + 5; $x++) {
            for ($y = $spongeY - 5; $y <= $spongeY + 5; $y++) {
                for ($z = $spongeZ - 5; $z <= $spongeZ + 5; $z++) {
                    $targetBlock = $spongeBlockPosition->getWorld()->getBlockAt($x, $y, $z);
                    if (
                        $targetBlock instanceof Water &&
                        (abs($x - $spongeX) + abs($y - $spongeY) + abs($z - $spongeZ)) <= 5
                    ) {
                        $sponge->getPosition()->getWorld()->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
                        $absorbedWaterCount++;
                        if ($absorbedWaterCount >= 65) {
                            break 3;
                        }
                    }
                }
            }
        }
        if ($absorbedWaterCount > 0) {
            $sponge->setWet(true);
        }
        $spongeBlockPosition->getWorld()->setBlockAt($spongeBlockPosition->getX(), $spongeBlockPosition->getY(), $spongeBlockPosition->getZ(), $sponge);
    }

    public function absorbLava(Block $sponge, float|int $spongeX, float|int $spongeY, float|int $spongeZ): void
    {
        /** @var SpongeBlock $sponge */
        $absorbedLavaCount = 0;
        $spongeBlockPosition = $sponge->getPosition();
        for ($x = $spongeX - 3; $x <= $spongeX + 3; $x++) {
            for ($y = $spongeY - 3; $y <= $spongeY + 3; $y++) {
                for ($z = $spongeZ - 3; $z <= $spongeZ + 3; $z++) {
                    $targetBlock = $spongeBlockPosition->getWorld()->getBlockAt($x, $y, $z);
                    if (
                        $targetBlock instanceof Lava &&
                        (abs($x - $spongeX) + abs($y - $spongeY) + abs($z - $spongeZ)) <= 3
                    ) {
                        $sponge->getPosition()->getWorld()->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
                        $absorbedLavaCount++;
                        if ($absorbedLavaCount >= 65) {
                            break 3;
                        }
                    }
                }
            }
        }
        if ($absorbedLavaCount > 0) {
            $sponge->setWet(true);
        }
        $spongeBlockPosition->getWorld()->setBlockAt($spongeBlockPosition->getX(), $spongeBlockPosition->getY(), $spongeBlockPosition->getZ(), $sponge);
    }
}
