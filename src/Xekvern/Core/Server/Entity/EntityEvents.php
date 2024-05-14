<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Entity;

use pocketmine\block\Block;
use Xekvern\Core\Server\Entity\Types\Creeper;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\GlazedTerracotta;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\PrimedTNT as ObjectPrimedTNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Axe;
use pocketmine\item\Sword;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use Random\Randomizer;
use Xekvern\Core\Server\Entity\Data\DurabilityBlockData;
use Xekvern\Core\Server\Entity\Types\PrimedTNT;
use Xekvern\Core\Server\Entity\Types\SpawnerEntity;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\World\Tile\Generator;

class EntityEvents implements Listener
{

    /** @var Nexus */
    private $core;

    /** @var Entity[] */
    private $entities = [];

    /** @var string[] */
    private $ids = [];

    const OBSIDIAN_DURABILITY = 30;
    const BEDROCK_DURABILITY = 80;

    /**
     * @priority HIGHEST
     *
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        if ($event->isCancelled()) {
            return;
        }
        $block = $event->getBlock();
        $drops = $event->getDrops();
        $player = $event->getPlayer();
        $inventory = $player->getInventory();
        foreach ($drops as $drop) {
            if($block->getTypeId() === VanillaBlocks::RAW_GOLD()->getTypeId()) {
                $event->setDrops([]);
                return;
            }
            if (!$inventory->canAddItem($drop)) {
                $player->sendTitle(TextFormat::DARK_RED . "Full Inventory", TextFormat::RED . "Clear out your inventory!");
                $event->cancel();
                return;
            }
        }
        switch ($block->getTypeId()) {
            case BlockTypeIds::COAL_ORE:
                $blockBeneath = $block->getPosition()->getWorld()->getBlock($block->getSide(Facing::DOWN)->getPosition());
                $tileBeneath = $block->getPosition()->getWorld()->getTile($block->getSide(Facing::DOWN)->getPosition());
                if ($blockBeneath instanceof GlazedTerracotta and $blockBeneath->getColor()->equals(DyeColor::BROWN()) and $tileBeneath instanceof Generator) {
                    $count = $tileBeneath->getStack();
                }
                break;
            case BlockTypeIds::DIAMOND_ORE:
                $blockBeneath = $block->getPosition()->getWorld()->getBlock($block->getSide(Facing::DOWN)->getPosition());
                $tileBeneath = $block->getPosition()->getWorld()->getTile($block->getSide(Facing::DOWN)->getPosition());
                if ($blockBeneath instanceof GlazedTerracotta and $blockBeneath->getColor()->equals(DyeColor::PINK()) and $tileBeneath instanceof Generator) {
                    $count = $tileBeneath->getStack();
                }
                break;
            case BlockTypeIds::EMERALD_ORE:
                $blockBeneath = $block->getPosition()->getWorld()->getBlock($block->getSide(Facing::DOWN)->getPosition());
                $tileBeneath = $block->getPosition()->getWorld()->getTile($block->getSide(Facing::DOWN)->getPosition());
                if ($blockBeneath instanceof GlazedTerracotta and $blockBeneath->getColor()->equals(DyeColor::LIME()) and $tileBeneath instanceof Generator) {
                    $count = $tileBeneath->getStack();
                }
                break;
            case BlockTypeIds::LAPIS_LAZULI_ORE:
                $blockBeneath = $block->getPosition()->getWorld()->getBlock($block->getSide(Facing::DOWN)->getPosition());
                $tileBeneath = $block->getPosition()->getWorld()->getTile($block->getSide(Facing::DOWN)->getPosition());
                if ($blockBeneath instanceof GlazedTerracotta and $blockBeneath->getColor()->equals(DyeColor::CYAN()) and $tileBeneath instanceof Generator) {
                    $count = $tileBeneath->getStack();
                }
                break;
            case BlockTypeIds::IRON_ORE:
                $blockBeneath = $block->getPosition()->getWorld()->getBlock($block->getSide(Facing::DOWN)->getPosition());
                $tileBeneath = $block->getPosition()->getWorld()->getTile($block->getSide(Facing::DOWN)->getPosition());
                if ($blockBeneath instanceof GlazedTerracotta and $blockBeneath->getColor()->equals(DyeColor::LIGHT_GRAY()) and $tileBeneath instanceof Generator) {
                    $count = $tileBeneath->getStack();
                }
                break;
            case BlockTypeIds::AMETHYST:
                $blockBeneath = $block->getPosition()->getWorld()->getBlock($block->getSide(Facing::DOWN)->getPosition());
                $tileBeneath = $block->getPosition()->getWorld()->getTile($block->getSide(Facing::DOWN)->getPosition());
                if ($blockBeneath instanceof GlazedTerracotta and $blockBeneath->getColor()->equals(DyeColor::PURPLE()) and $tileBeneath instanceof Generator) {
                    if($player instanceof NexusPlayer && $player->isLoaded()){
                    $count = 0;
                    $xpamount = 0;
                    $moneyamount = 0;
                    if($tileBeneath->getSpecialXpLocked() === 1){
                        $xpamount = random_int(1, $tileBeneath->getSpecialXpLevel()*100);
                    }
                    if($tileBeneath->getSpecialMoneyLocked() === 1){
                        $moneyamount = random_int(1, $tileBeneath->getSpecialMoneyLevel()*1500);
                    }
                    $event->getPlayer()->getXpManager()->addXp((int)ceil($xpamount));
                    $event->getPlayer()->getDataSession()->addToBalance($moneyamount);
                }
                }
                break;
            default:
                break;
        }
        $xp = $event->getXpDropAmount();
        if (isset($count)) {
            $xp *= ($count * 0.35);
        }
        $event->getPlayer()->getXpManager()->addXp((int)ceil($xp));
        $event->setXpDropAmount(0);
        foreach ($drops as $drop) {
            if (isset($count)) {
                $drop->setCount((int)ceil(($count / 5)));
            }
            $inventory->addItem($drop);
        }
        $event->setDrops([]);
    }

    /**
     * @priority HIGHEST
     *
     * @param EntitySpawnEvent $event
     */
    public function onEntitySpawn(EntitySpawnEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof ExperienceOrb) {
            $entity->flagForDespawn();
            return;
        }
        if ($entity instanceof Human) {
            return;
        }
        if ($entity instanceof Creeper) {
            return;
        }
        $uuid = uniqid();
        if ($entity instanceof Living or $entity instanceof ItemEntity) {
            if (count($this->entities) > 250) {
                $despawn = array_shift($this->entities);
                if (!$despawn->isClosed()) {
                    $despawn->flagForDespawn();
                }
            }
            $this->ids[$entity->getId()] = $uuid;
            $this->entities[$uuid] = $entity;
        }
    }

    /**
     * @priority HIGHEST
     *
     * @param EntityDespawnEvent $event
     */
    public function onEntityDespawn(EntityDespawnEvent $event): void
    {
        $entity = $event->getEntity();
        if (!isset($this->ids[$entity->getId()])) {
            return;
        }
        $uuid = $this->ids[$entity->getId()];
        unset($this->ids[$entity->getId()]);
        if (isset($this->entities[$uuid])) {
            unset($this->entities[$uuid]);
        }
    }

    /**
     * @param EntityExplodeEvent $event
     * @priority MONITOR
     * @return void
     */
    public function onEntityExplode(EntityExplodeEvent $event): void {
        $entity = $event->getEntity();
        $world = $entity->getWorld();
        $position = $entity->getPosition();
        $block_at_position = $world->getBlock($position);
        if(Nexus::getInstance()->isInGracePeriod()) {
            $event->cancel();
            return;
        }
        if ($block_at_position instanceof Water) {
            return;
        }
        $block_list = [];
        $radius = 4;
        for ($x = $position->getFloorX() - $radius; $x <= $position->getFloorX() + $radius; ++$x) {
            for ($y = $position->getFloorY() - $radius; $y <= $position->getFloorY() + $radius; ++$y) {
                for ($z = $position->getFloorZ() - $radius; $z <= $position->getFloorZ() + $radius; ++$z) {
                    $block = $world->getBlockAt($x, $y, $z);
                    if ($block->getTypeId() !== BlockTypeIds::OBSIDIAN and $block->getTypeId() !== BlockTypeIds::BEDROCK and $block->getTypeId() !== BlockTypeIds::ENCHANTING_TABLE) continue;
                    $block_list[] = $block;
                }
            }
        }
        $block_data_manager = Nexus::getInstance()->getBlockDataManager();
        foreach ($block_list as $block) {
            if($block ->getTypeId() === BlockTypeIds::ENCHANTING_TABLE) {
                [$x, $y, $z] = [$block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ()];
                $world->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
            }
            if ($block->getTypeId() !== BlockTypeIds::OBSIDIAN and $block->getTypeId() !== BlockTypeIds::BEDROCK) continue;
            [$x, $y, $z] = [$block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ()];
            $block_data_world = $block_data_manager->get($world);
            $block_data = $block_data_world->getBlockDataAt($x, $y, $z);
            $durability = $block->getTypeId() === BlockTypeIds::OBSIDIAN ? self::OBSIDIAN_DURABILITY : self::BEDROCK_DURABILITY;
            if ($block_data === null or !$block_data instanceof DurabilityBlockData) {
                $block_data = new DurabilityBlockData(--$durability);
                $block_data_world->setBlockDataAt($x, $y, $z, $block_data);
                return;
            }
            $current_durability = $block_data->getDurability();
            if ($current_durability <= 0) {
                $world->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
                $block_data_world->setBlockDataAt($x, $y, $z, null);
                return;
            }
            $block_data = $block_data->setDurability(--$current_durability);
            $block_data_world->setBlockDataAt($x, $y, $z, $block_data);
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @priority MONITOR
     * @return void
     */
    public function onBlockBreak2(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        $position = $block->getPosition();
        $world = $position->getWorld();

        $block_data_manager = Nexus::getInstance()->getBlockDataManager();
        $block_data_world = $block_data_manager->get($world);
        $block_data = $block_data_world->getBlockDataAt($position->getX(), $position->getY(), $position->getZ());
        if ($block_data === null or !$block_data instanceof DurabilityBlockData) return;

        $block_data_world->setBlockDataAt($position->getX(), $position->getY(), $position->getZ(), null);
    }

    /**
     * @param PlayerInteractEvent $event
     * @priority MONITOR
     * @return void
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $position = $event->getBlock()->getPosition();
        $world = $position->getWorld();

        $block_data_manager = Nexus::getInstance()->getBlockDataManager();
        $block_data_world = $block_data_manager->get($world);
        $block_data = $block_data_world->getBlockDataAt($position->getX(), $position->getY(), $position->getZ());
        if (!in_array($event->getBlock()->getTypeId(), [BlockTypeIds::OBSIDIAN, BlockTypeIds::BEDROCK], true)) return;
        if ($block_data === null or !$block_data instanceof DurabilityBlockData) {
            $durability = $event->getBlock()->getTypeId() === BlockTypeIds::OBSIDIAN ? self::OBSIDIAN_DURABILITY : self::BEDROCK_DURABILITY;
            $block_data = new DurabilityBlockData($durability);
            $block_data_world->setBlockDataAt($position->getX(), $position->getY(), $position->getZ(), $block_data);
        }
        $event->getPlayer()->sendTip(TextFormat::RED . "Durability: " . $block_data->getDurability());
    }

    /**
     * @priority LOWEST
     *
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        $world = $entity->getPosition()->getWorld();
        if ($entity instanceof SpawnerEntity) {
            if ($entity->getHealth() <= $event->getFinalDamage()) {
                $damager = null;
                $size = 1;
                if ($event instanceof EntityDamageByEntityEvent) {
                    $damager = $event->getDamager();
                    if ($damager instanceof NexusPlayer) {
                        $damager->addXp($entity->getXpDropAmount());
                    }
                }
                if ($event instanceof EntityDamageByBlockEvent) {
                    $size = $entity->getStack();
                    $size = $size > 50 ? 50 : $size;
                }
                if($entity->getStack() > 1) {
                    $currentSize = $entity->getStack();
                    $decr = min($size, $currentSize);
                    $inventory = null;
                    if ($damager instanceof NexusPlayer) {
                        $inventory = $damager->getInventory();
                    }
                    if ($inventory !== null) {
                        for ($i = 0; $i < $decr; ++$i) {
                            foreach ($entity->getDrops() as $item) {
                                if ($inventory->canAddItem($item)) {
                                    $inventory->addItem($item);
                                    continue;
                                }
                            }
                        }
                    } else {
                        foreach ($entity->getDrops() as $item) {
                            $count = $item->getCount() + mt_rand((int)ceil($decr * 0.75), $decr);
                            for ($i = $count; $i > 0; $i -= 64) {
                                $world->dropItem($entity->getPosition(), $item->setCount($i));
                            }
                            $world->dropItem($entity->getPosition(), $item->setCount($count % 64));
                        }
                    }
                    $entity->kill();
                    $event->cancel();
                }
                return;
            }
            if ($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();
                if (!$damager instanceof NexusPlayer) {
                    return;
                }
                if ($damager->getInventory()->getItemInHand() instanceof Sword or $damager->getInventory()->getItemInHand() instanceof Axe) {
                    $event->setKnockBack(0);
                }
            }
        }
    }
}
