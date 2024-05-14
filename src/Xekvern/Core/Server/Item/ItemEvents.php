<?php

declare(strict_types=1);

namespace Xekvern\Core\Server\Item;

use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\TranslatonException;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\GlazedTerracotta;
use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Container;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\enchantment\Rarity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\Position;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Server\Item\Types\EnchantmentBook;
use Xekvern\Core\Server\Item\Types\EnchantmentCrystal;
use Xekvern\Core\Server\Item\Types\LuckyBlock;
use Xekvern\Core\Server\Item\Types\Soul;
use Xekvern\Core\Server\Item\Utils\CustomItem;
use Xekvern\Core\Server\Item\Utils\ExtraVanillaItems;
use Xekvern\Core\Translation\Translation;
use pocketmine\color\Color;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\world\sound\AnvilBreakSound;
use Xekvern\Core\Player\Faction\Faction;
use Xekvern\Core\Server\Entity\Types\PrimedTNT;
use Xekvern\Core\Server\Item\Types\SellWand;
use Xekvern\Core\Server\Item\Types\TNTLauncher;
use Xekvern\Core\Server\Price\Event\ItemSellEvent;
use pocketmine\item\VanillaItems;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Sword;
use pocketmine\item\VanillaArmorMaterials;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\world\sound\AnvilUseSound;
use Xekvern\Core\Player\Combat\Boss\ArtificialIntelligence;
use Xekvern\Core\Server\Crate\Crate;
use Xekvern\Core\Server\Item\Enchantment\Utils\EnchantmentIdentifiers;
use Xekvern\Core\Server\Item\Types\AttributeShard;
use Xekvern\Core\Server\Item\Types\ChestKit;
use Xekvern\Core\Server\Item\Types\CosmeticShard;
use Xekvern\Core\Server\Item\Types\CrateKeyNote;
use Xekvern\Core\Server\Item\Types\MoneyNote;
use Xekvern\Core\Server\Item\Types\SellWandNote;
use Xekvern\Core\Server\Item\Types\XPNote;
use Xekvern\Core\Server\Item\Utils\InteractiveItem;

class ItemEvents implements Listener
{

    /** @var Nexus */
    private $core;

    /** @var int */
    private $itemCooldowns = [];

    /**
     * ItemEvents constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core)
    {
        $this->core = $core;
    }

    /**
     * @priority LOWEST
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $item = $player->getInventory()->getItemInHand();
        $name = TextFormat::RESET . TextFormat::WHITE . $item->getName();
        if ($item->hasCustomName()) {
            $name = $item->getCustomName();
        }
        $replace = TextFormat::DARK_GRAY . "[" . $name . TextFormat::RESET . TextFormat::GRAY . " * " . TextFormat::WHITE . $item->getCount() . TextFormat::DARK_GRAY . "]" . TextFormat::RESET;
        $message = $event->getMessage();
        $message = str_replace("[item]", $replace, $message);
        $event->setMessage($message);
    }

    /**
     * @priority LOWEST
     * @param ItemSpawnEvent $event
     */
    public function onThrow(ItemSpawnEvent $event)
    {
        $entity = $event->getEntity();
        $item = $entity->getItem();
        $name = $item->getCustomName();
        $count = $item->getCount();
        if (!$item->hasCustomName()) {
            return;
        }
        $entity->setNameTag(TextFormat::YELLOW . "x" . $count . " " . TextFormat::RESET . TextFormat::WHITE . $name);
        $entity->setNameTagVisible(true);
        $entity->setNameTagAlwaysVisible(true);
    }

    /**
     * @priority HIGH
     * 
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $tile = $block->getPosition()->getWorld()->getTile($block->getPosition());
        if ($tile === null || !($block instanceof GlazedTerracotta && $block->getColor()->equals(DyeColor::BLACK()))) {
            return;
        }
        $goodRewards = [
            function (NexusPlayer $player, Position $position): void {
                $item = VanillaItems::GOLDEN_APPLE()->setCount(32);
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new ChestKit(Nexus::getInstance()->getServerManager()->getKitHandler()->getKitByName("Subordinate")))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new MoneyNote(mt_rand(1000, 7500)))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new CrateKeyNote(Crate::ULTRA, mt_rand(1, 3)))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new CrateKeyNote(Crate::EPIC, mt_rand(1, 3)))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new XPNote(mt_rand(1000, 10000)))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new SellWandNote(mt_rand(5, 10)))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new SellWand())->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new TNTLauncher(1, 15, "TNT", ["Short", "Mid", "Long"][array_rand(["Short", "Mid", "Long"])]))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new EnchantmentBook(ItemHandler::getRandomEnchantment(), mt_rand(20, 60)))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_HELMET(), TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Lucky Helmet", [], [
                    new EnchantmentInstance((VanillaEnchantments::PROTECTION()), 3),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 4),
                ]))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_CHESTPLATE(), TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Lucky Chestplate", [], [
                    new EnchantmentInstance((VanillaEnchantments::PROTECTION()), 3),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 4),
                ]))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_LEGGINGS(), TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Lucky Leggings", [], [
                    new EnchantmentInstance((VanillaEnchantments::PROTECTION()), 3),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 4),
                ]))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_BOOTS(), TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Lucky Boots", [], [
                    new EnchantmentInstance((VanillaEnchantments::PROTECTION()), 3),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 4),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::QUICKENING), 1),
                ]))->getItemForm();
                $position->world->dropItem($position, $item);
            },
            function (NexusPlayer $player, Position $position): void {
                $item = (new CustomItem(VanillaItems::DIAMOND_PICKAXE(), TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "Lucky Pickaxe", [], [
                    new EnchantmentInstance((VanillaEnchantments::EFFICIENCY()), 2),
                    new EnchantmentInstance((VanillaEnchantments::UNBREAKING()), 4),
                    new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(Enchantment::CHARM), 1),
                ]))->getItemForm();
                $position->world->dropItem($position, $item);
            },
        ];
        $badRewards = [
            function (NexusPlayer $player): void {
                $player->setHealth(6);
            },
            function (NexusPlayer $player): void {
                $player->getHungerManager()->setFood(0);
            },
            function (NexusPlayer $player): void {
                $effects = [
                    new EffectInstance(VanillaEffects::POISON(), 600, 1),
                    new EffectInstance(VanillaEffects::BLINDNESS(), 200, 1)
                ];
                $player->getEffects()->add($effects[array_rand($effects)]);
            },
            function (NexusPlayer $player): void {
                $effects = [
                    new EffectInstance(VanillaEffects::NIGHT_VISION(), 600, 1),
                    new EffectInstance(VanillaEffects::BLINDNESS(), 600, 1)
                ];
                foreach ($effects as $effect) {
                    $player->getEffects()->add($effect);
                }
            }
        ];
        $item = $player->getInventory()->getItemInHand();
        $enchantment = EnchantmentIdMap::getInstance()->fromId(EnchantmentIdentifiers::CHARM);
        $add = $item->getEnchantmentLevel($enchantment) * 5;
        if ($tile instanceof \Xekvern\Core\Server\World\Tile\LuckyBlock) {
            $luck = $tile->getLuck() + $add;
            $block->getPosition()->getWorld()->removeTile($tile);
        } else {
            $luck = mt_rand(0, 100) + $add;
        }
        if (mt_rand(0, 100) <= $luck) {
            $reward = $goodRewards[array_rand($goodRewards)];
            $pk = new LevelSoundEventPacket();
            $pk->position = $player->getPosition();
            $pk->sound = LevelSoundEvent::BLAST;
        } else {
            $reward = $badRewards[array_rand($badRewards)];
            $pk = new LevelSoundEventPacket();
            $pk->position = $player->getEyePos();
            $pk->sound = LevelSoundEvent::RAID_HORN;
        }
        $player->getNetworkSession()->sendDataPacket($pk);
        $reward($player, $block->getPosition());
    }

    /**
     * @priority HIGHEST
     * @param PlayerInteractEvent $event
     *
     * @throws TranslatonException
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $inventory = $player->getInventory();
        $tag = $item->getNamedTag();
        if ($item->getTypeId() === ExtraVanillaItems::CREEPER_SPAWN_EGG()->getTypeId()) {
            $position = $player->getPosition();
            $area = $this->core->getServerManager()->getAreaHandler()->getAreaByPosition($position);
            if ($this->core->isInGracePeriod()) {
                $event->cancel();
                $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "NOTICE", TextFormat::GRAY . "You can't do this action while on grace period!");
                $player->sendMessage(Translation::RED . "You can't use this while the server is on grace period!");
                $player->playErrorSound();
                return;
            }
            if ($area !== null) {
                $event->cancel();
                $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "NOTICE", TextFormat::GRAY . "You are not in the wilderness to use this!");
                $player->sendMessage(Translation::RED . "You can only use a creeper egg in the wilderness!");
                $player->playErrorSound();
                return;
            }
        }
        if ($tag === null) {
            return;
        }
        if ($tag instanceof CompoundTag) {
            if (isset($this->itemCooldowns[$player->getUniqueId()->toString()]) and (time() - $this->itemCooldowns[$player->getUniqueId()->toString()]) < 1) {
                $event->cancel();
                return;
            }
            if (!$tag->getTag(Soul::SOUL) === null) {
                $event->cancel();
                return;
            }
            if (!$tag->getTag(EnchantmentBook::ENCHANT) === null) {
                $event->cancel();
                return;
            }
            if ($tag->getTag(CustomItem::ITEM_CLASS) === null) {
                $tag->setString(CustomItem::ITEM_CLASS, CustomItem::ITEM_CLASS);
            }
            $matchedItem = $this->core->getServerManager()->getItemHandler()->matchItem($tag);
            if ($matchedItem !== null) {
                $event->cancel();
                call_user_func($matchedItem . '::execute', $player, $inventory, $item, $tag, $event->getFace(), $event->getBlock());
                $this->itemCooldowns[$player->getUniqueId()->toString()] = time();
            } else {
                if (isset($tag->getValue()[LuckyBlock::LUCK])) {
                    $event->cancel();
                    $world = $player->getWorld();
                    if ($world === null) {
                        return;
                    }
                    if ($world->getDisplayName() !== Faction::CLAIM_WORLD) {
                        $player->sendMessage(Translation::getMessage("notInClaimWorld"));
                        return;
                    }
                    $position = $player->getPosition();
                    $area = $this->core->getServerManager()->getAreaHandler()->getAreaByPosition($position);
                    if ($area !== null) {
                        $player->sendMessage(Translation::RED . "You can only use this in the wilderness!");
                        return;
                    }
                    $luck = $tag->getInt(LuckyBlock::LUCK);
                    $tile = $block->getPosition()->getWorld()->getTile($block->getPosition());
                    $position = Position::fromObject($event->getBlock()->getPosition()->add(0, 1, 0), $player->getWorld());
                    if (!$tile instanceof \Xekvern\Core\Server\World\Tile\LuckyBlock) {
                        if ($block->getTypeId() !== BlockTypeIds::AIR && $block->getPosition()->getY() < 255) {
                            $position = Position::fromObject($event->getBlock()->getPosition()->add(0, 1, 0), $player->getWorld());
                            if ($player->getWorld()->getBlock($position)->getTypeId() === BlockTypeIds::AIR) {
                                $inventory->setItemInHand($item->setCount($item->getCount() - 1));
                                $position->getWorld()->setBlock($position, VanillaBlocks::GLAZED_TERRACOTTA()->setColor(DyeColor::BLACK()));
                            }
                        }
                        $tile = new \Xekvern\Core\Server\World\Tile\LuckyBlock($block->getPosition()->getWorld(), $position);
                        $tile->setLuck($luck);
                        if (!$block->getPosition()->getWorld()->getTile($tile->getPosition())) {
                            $block->getPosition()->getWorld()->addTile($tile);
                        }
                    }
                }
                if (isset($tag->getValue()[SellWand::SELL_WAND])) {
                    if ($player->getDataSession()->getSellWandUses() <= 0) {
                        $player->sendMessage(Translation::getMessage("noSellWandUses"));
                        return;
                    }
                    if ($event->isCancelled()) {
                        $player->sendMessage(Translation::getMessage("blockProtected"));
                        return;
                    }
                    $block = $event->getBlock();
                    $tile = $block->getPosition()->getWorld()->getTile($block->getPosition());
                    if (!$tile instanceof Container) {
                        $player->sendMessage(Translation::getMessage("invalidBlock"));
                        return;
                    }
                    if ($block instanceof Chest) {
                        $player->getWorld()->scheduleDelayedBlockUpdate($block->getPosition(), 1);
                    }
                    $content = $tile->getInventory()->getContents();
                    /** @var Item[] $items */
                    $items = [];
                    $sellable = false;
                    $sellables = $this->core->getServerManager()->getPriceHandler()->getSellables();
                    $entries = [];
                    foreach ($content as $i) {
                        if (!isset($sellables[$i->getTypeId()])) {
                            continue;
                        }
                        $entry = $sellables[$i->getTypeId()];
                        if (!$entry->equal($i)) {
                            continue;
                        }
                        if ($sellable === false) {
                            $sellable = true;
                        }
                        if (!isset($entries[$entry->getName()])) {
                            $entries[$entry->getName()] = $entry;
                            $items[$entry->getName()] = $i;
                        } else {
                            $items[$entry->getName()]->setCount($items[$entry->getName()]->getCount() + $i->getCount());
                        }
                    }
                    if ($sellable === false) {
                        $event->cancel();
                        return;
                    }
                    $price = 0;
                    foreach ($entries as $entry) {
                        $i = $items[$entry->getName()];
                        $price += $i->getCount() * $entry->getSellPrice();
                        $tile->getInventory()->removeItem($i);
                        $ev = new ItemSellEvent($player, $i, $price);
                        $ev->call();
                        $player->sendMessage(Translation::getMessage("sell", [
                            "amount" => TextFormat::GREEN . number_format($i->getCount()),
                            "item" => TextFormat::DARK_GREEN . $entry->getName(),
                            "price" => TextFormat::LIGHT_PURPLE . "$" . number_format((int)$i->getCount() * $entry->getSellPrice())
                        ]));
                    }
                    $player->getDataSession()->addToBalance($price);
                    $player->playXpLevelUpSound();
                    $player->getDataSession()->subtractFromSellWandUses(1);
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerItemUseEvent $event
     *
     * @throws UtilsException
     */
    public function onPlayerItemUse(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        $inventory = $player->getInventory();
        $level = $player->getWorld();
        $tag = $item->getNamedTag();
        if ($tag === null) {
            return;
        }
        if ($tag instanceof CompoundTag) {
            if (isset($this->itemCooldowns[$player->getUniqueId()->toString()]) and (time() - $this->itemCooldowns[$player->getUniqueId()->toString()]) < 1) {
                $event->cancel();
                return;
            }
            $matchedItem = $this->core->getServerManager()->getItemHandler()->matchItem($tag);
            if ($matchedItem !== null) {
                $event->cancel();
                $this->itemCooldowns[$player->getUniqueId()->toString()] = time();
            } else {
                if (isset($tag->getValue()[TNTLauncher::USES]) and isset($tag->getValue()[TNTLauncher::TIER]) and isset($tag->getValue()[TNTLauncher::TYPE])) {
                    $level = $player->getWorld();
                    if ($this->core->isInGracePeriod()) {
                        $player->sendTitle(TextFormat::BOLD . TextFormat::RED . "Grace Period", TextFormat::GRAY . "You can't do this action while on grace period!");
                        $player->sendMessage(Translation::RED . "You can't use this while the server is on grace period!");
                        $player->playErrorSound();
                        return;
                    }
                    if ($level === null) {
                        return;
                    }
                    if ($level->getDisplayName() !== Faction::CLAIM_WORLD) {
                        $player->sendMessage(Translation::getMessage("notInClaimWorld"));
                        return;
                    }
                    $position = $player->getPosition();
                    $area = $this->core->getServerManager()->getAreaHandler()->getAreaByPosition($position);
                    if ($area !== null) {
                        $player->sendMessage(Translation::RED . "You can only use Launchers in the wilderness!");
                        return;
                    }
                    $amount = $tag->getInt(TNTLauncher::USES);
                    $tier = $tag->getInt(TNTLauncher::TIER);
                    $range = $tag->getString(TNTLauncher::RANGE);
                    $fuelAmount = ItemHandler::getFuelAmountByTier($tier, "tnt");
                    if ($inventory->contains(VanillaBlocks::TNT()->asItem()->setCount($fuelAmount)) === false) {
                        $player->sendMessage(Translation::getMessage("notEnoughFuel"));
                        return;
                    }
                    $directionVector = $player->getDirectionVector();
                    $nbt = new CompoundTag();
                    $nbt->setShort("Force", $fuelAmount);
                    $entity = new PrimedTNT($player->getLocation(), $nbt);
                    $multiplicationFactor = match ($range) {
                        "Short" => 1.5,
                        "Mid" => 3,
                        "Long" => 6,
                        default => 3,
                    };
                    $entity->setMotion($entity->getDirectionVector()->normalize()->multiply($multiplicationFactor));
                    $entity->spawnToAll();
                    --$amount;
                    if ($amount <= 0) {
                        $player->getWorld()->addSound($player->getEyePos(), new AnvilBreakSound());
                        $inventory->setItemInHand($item->setCount($item->getCount() - 1));
                    } else {
                        $tag->setInt(TNTLauncher::USES, $amount);
                        $lore = [];
                        $lore[] = TextFormat::RESET . TextFormat::GRAY . "Use this and fire " . TextFormat::RED . "TNT" . TextFormat::GRAY . " where";
                        $lore[] = TextFormat::RESET . TextFormat::GRAY . "you are pointing on and the greater the";
                        $lore[] = TextFormat::RESET . TextFormat::GRAY . "tier, the large the radius!.";
                        $lore[] = "";
                        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Uses: " . TextFormat::RED . number_format((int)$amount);
                        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Tier: " . TextFormat::RED . $tier;
                        $lore[] = TextFormat::RESET . TextFormat::WHITE . "Range: " . TextFormat::RED . $range;
                        $lore[] = "";
                        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::RED . "TNT Required";
                        $lore[] = TextFormat::RESET . TextFormat::WHITE . ItemHandler::getFuelAmountByTier($tier, "tnt") . TextFormat::GRAY . " TNT.";
                        $lore[] = "";
                        $lore[] = TextFormat::RESET . TextFormat::BOLD . TextFormat::WHITE . "USE: " . TextFormat::RESET . TextFormat::WHITE . "Right-Click" . TextFormat::GRAY . " to fire";
                        $item->setLore($lore);
                        $inventory->setItemInHand($item);
                    }
                    $event->cancel();
                    $inventory->removeItem(VanillaBlocks::TNT()->asItem()->setCount($fuelAmount));
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param EntityDamageByEntityEvent $event
     */
    public function onEntityDamage(EntityDamageByEntityEvent $event): void
    {
        $damager = $event->getDamager();
        $player = $event->getEntity();

        if ($damager instanceof NexusPlayer && $player instanceof NexusPlayer) {
            $this->applyDamageBoosts($event, $damager, $player);
            $this->applyDamageReduction($event, $damager, $player);
            $this->addCosmeticParticleEffect($event, $damager);
        }

        if ($damager instanceof NexusPlayer && $player instanceof ArtificialIntelligence) {
            $this->applyArtificialDamageBoost($event, $damager, $player);
            $this->addCosmeticParticleEffect($event, $damager);
        }
    }

    public function applyDamageBoosts(EntityDamageByEntityEvent $event, NexusPlayer $damager, NexusPlayer $player): void {
        $damageBoosts = [
            "hasDamagerAttribute" => 5,
            "hasKothAttribute" => 4
        ];
    
        foreach ($damageBoosts as $attributeType => $percentage) {
            $boost = $this->getAttributeArmorBoost($damager, $attributeType, $percentage);
            $event->setBaseDamage($event->getBaseDamage() * (1 + $boost / 100));
        }
    }

    public function applyDamageReduction(EntityDamageByEntityEvent $event, NexusPlayer $damager, NexusPlayer $player): void {
        $damageReductions = [
            "hasDefenderAttribute" => 4,
            "hasKothAttribute" => 3
        ];
    
        foreach ($damageReductions as $attributeType => $percentage) {
            $boost = $this->getAttributeArmorBoost($player, $attributeType, $percentage);
            $event->setBaseDamage($event->getBaseDamage() * (1 - $boost / 100));
        }
    }

    public function applyArtificialDamageBoost(EntityDamageByEntityEvent $event, NexusPlayer $damager,  ArtificialIntelligence $entity): void {
        $damageBoost = [
            "hasBossAttribute" => $entity instanceof ArtificialIntelligence ? 10 : 0
        ];
    
        foreach ($damageBoost as $attributeType => $percentage) {
            $boost = $this->getAttributeArmorBoost($damager, $attributeType, $percentage);
            $event->setBaseDamage($event->getBaseDamage() * (1 + $boost / 100));
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerToggleSprintEvent $event
     */
    public function onPlayerSprintEvent(PlayerToggleSprintEvent $event): void {
        $player = $event->getPlayer();
    
        if (!$player instanceof NexusPlayer) {
            return;
        }
       // $baseMovementSpeed = 0.1;
       // $player->setMovementSpeed($baseMovementSpeed);
        
       // $wearingSprinterArmor = $this->isWearingAttributeArmor($player, "hasSprinterAttribute");
    
       // if ($wearingSprinterArmor) {
           // $armorPiecesCount = $this->getWornAttributeArmorCount($player, "hasSprinterAttribute");
           // $speedBoostPercentage = $armorPiecesCount * 15;
           // $currentSpeed = $player->getMovementSpeed();
           // $boostedSpeed = $currentSpeed * (1 + $speedBoostPercentage / 100);
           // $player->setMovementSpeed($boostedSpeed);
        //}
    }

    private function getAttributeArmorBoost(NexusPlayer $player, string $attributeType, int $percentagePerPiece): float {
        $armorPiecesCount = $this->getWornAttributeArmorCount($player, $attributeType);
        return $percentagePerPiece * $armorPiecesCount;
    }

    private function getWornAttributeArmorCount(NexusPlayer $player, string $attributeType): int {
        $armorContents = $player->getArmorInventory()->getContents();
        $requiredArmorTypes = [
            ItemTypeIds::DIAMOND_HELMET,
            ItemTypeIds::DIAMOND_CHESTPLATE,
            ItemTypeIds::DIAMOND_LEGGINGS,
            ItemTypeIds::DIAMOND_BOOTS,
        ];
    
        $foundArmorTypes = 0;
    
        foreach ($armorContents as $item) {
            if ($item instanceof Armor && in_array($item->getTypeId(), $requiredArmorTypes) && $item->getMaterial() === VanillaArmorMaterials::DIAMOND()) {
                if ($item->getNamedTag()->getTag($attributeType) && $item->getNamedTag()->getTag($attributeType)->getValue() === "true") {
                    $foundArmorTypes++;
                }
            }
        }
    
        return ($foundArmorTypes == count($requiredArmorTypes)) ? 1 : 0;
    }
    
    private function isWearingAttributeArmor(NexusPlayer $player, string $attributeType): bool {
        $armorContents = $player->getArmorInventory()->getContents();
        $requiredArmorTypes = [
            ItemTypeIds::DIAMOND_HELMET,
            ItemTypeIds::DIAMOND_CHESTPLATE,
            ItemTypeIds::DIAMOND_LEGGINGS,
            ItemTypeIds::DIAMOND_BOOTS,
        ];
        
        foreach ($armorContents as $item) {
            if ($item instanceof Armor && in_array($item->getTypeId(), $requiredArmorTypes) && $item->getMaterial() === VanillaArmorMaterials::DIAMOND()) {
                if ($item->getNamedTag()->getTag($attributeType)) {
                    if ($item->getNamedTag()->getTag($attributeType)->getValue() === "true") {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * @priority HIGHEST
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if (!($player instanceof NexusPlayer)) {
            return;
        }

        $armor = $player->getArmorInventory()->getBoots();
        if (!($armor instanceof Armor && $armor->getTypeId() === ItemTypeIds::DIAMOND_BOOTS)) {
            return;
        }

        $namedTag = $armor->getNamedTag();
        if (!$namedTag->getTag("hasCosmeticEffect")) {
            return;
        }

        if ($event->getFrom()->distance($event->getTo()) < 0.1) {
            return;
        }

        $effectId = $namedTag->getTag("hasCosmeticEffect")->getValue();
        $particle = ItemHandler::getParticleForEffectId($effectId);
        if ($particle === null) {
            return;
        }

        $playerPosition = $player->getPosition();
        $particlePosition = new Vector3($playerPosition->x, $playerPosition->y + 0.25, $playerPosition->z);
        $player->getWorld()->addParticle($particlePosition, $particle);
    }

    /**
     * @priority HIGHEST
     * @param PlayerJumpEvent $event
     */
    public function onPlayerJump(PlayerJumpEvent $event): void
    {
        $player = $event->getPlayer();
        if (!($player instanceof NexusPlayer)) {
            return;
        }

        $armor = $player->getArmorInventory()->getBoots();
        if (!($armor instanceof Armor && $armor->getTypeId() === ItemTypeIds::DIAMOND_BOOTS)) {
            return;
        }

        $namedTag = $armor->getNamedTag();
        if (!$namedTag->getTag("hasCosmeticEffect")) {
            return;
        }

        $effectId = $namedTag->getTag("hasCosmeticEffect")->getValue();
        $particle = ItemHandler::getParticleForEffectId($effectId);

        if ($particle === null) {
            return;
        }

        $particleCount = 6;
        $radius = 0.5;
        $playerPosition = $player->getPosition();
        $world = $player->getWorld();

        for ($i = 0; $i < $particleCount; ++$i) {
            $angle = $i * (2 * M_PI / $particleCount);
            $x = $playerPosition->x + $radius * cos($angle);
            $y = $playerPosition->y + 1;
            $z = $playerPosition->z + $radius * sin($angle);
            $particlePosition = new Vector3($x, $y, $z);
            $world->addParticle($particlePosition, $particle);
        }
    }

    /**
     * @priority LOWEST
     * @param InventoryTransactionEvent $event
     */
    public function onInventoryTransaction(InventoryTransactionEvent $event)
    {
        $transaction = $event->getTransaction();
        if ($event->isCancelled()) {
            return;
        }
        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                $sourceItem = $action->getSourceItem();
                $tag = $sourceItem->getNamedTag(CustomItem::CUSTOM);
                if ($sourceItem->getTypeId() === VanillaItems::NETHER_STAR()->getTypeId() && isset($tag->getValue()[AttributeShard::TYPE])) {
                    if ($tag instanceof CompoundTag) {
                        $type = $tag->getValue()[AttributeShard::TYPE]->getValue();
                        $attributeShardAction = $action;
                    }
                } elseif ($sourceItem->getTypeId() === VanillaItems::PRISMARINE_SHARD()->getTypeId() && isset($tag->getValue()[CosmeticShard::EFFECT]) && isset($tag->getValue()[CosmeticShard::EQUIPMENT])) {
                    if ($tag instanceof CompoundTag) {
                        $equipment = $tag->getValue()[CosmeticShard::EQUIPMENT]->getValue();
                        $effect = $tag->getValue()[CosmeticShard::EFFECT]->getValue();
                        $cosmeticShardAction = $action;
                    }
                } elseif (!$sourceItem->isNull()) {
                    $equipmentSourceAction = $action;
                }
            }
        }
        $player = $transaction->getSource();
        if (!$event->isCancelled()) {
            if (isset($equipmentSourceAction, $attributeShardAction, $type)) {
                $attributeShard = $attributeShardAction->getSourceItem();
                $equipment = $equipmentSourceAction->getSourceItem();
                if ($equipment instanceof Armor && $equipment->getMaterial() === VanillaArmorMaterials::DIAMOND()) {
                    $attributeTag = '';
                    switch ($type) {
                        case 0:
                            $attributeTag = "hasDamagerAttribute";
                            break;
                        case 1:
                            $attributeTag = "hasSprinterAttribute";
                            break;
                        case 2:
                            $attributeTag = "hasDefenderAttribute";
                            break;
                        case 3:
                            $attributeTag = "hasBossAttribute";
                            break;
                        default:
                            break;
                    }
                    if (!empty($attributeTag) && !$equipment->getNamedTag()->getTag($attributeTag)) {
                        $event->cancel();
                        $equipmentSourceAction->getInventory()->removeItem($equipment);
                        $attributeShardAction->getInventory()->removeItem($attributeShard);
                        $equipmentTag = $equipment->getNamedTag();
                        $equipmentTag->setString($attributeTag, "true");
                        $equipmentSourceAction->getInventory()->addItem(ItemHandler::setLoreForItem($equipment));
                        $player->sendMessage(Translation::GREEN . "The attribute has successfully been applied to the item.");
                        $player->getWorld()->addSound($player->getPosition(), new AnvilUseSound());
                    } else {
                        $player->sendMessage(Translation::RED . "You already have this attribute on this equipment!");
                    }
                }
            } elseif (isset($equipmentSourceAction, $cosmeticShardAction, $equipment, $effect)) {
                $cosmeticShardSource = $cosmeticShardAction->getSourceItem();
                $equipmentSource = $equipmentSourceAction->getSourceItem();
                if (!($equipmentSource instanceof Sword || $equipmentSource instanceof Armor)) {
                    return;
                }
                switch ($equipment) {
                    case 0:
                        if (!($equipmentSource instanceof Sword && $equipmentSource->getTypeId() === ItemTypeIds::DIAMOND_SWORD)) {
                            return;
                        }
                        break;
                    case 1:
                        if (!($equipmentSource instanceof Armor && $equipmentSource->getTypeId() === ItemTypeIds::DIAMOND_BOOTS)) {
                            return;
                        }
                        break;
                    case 2:
                        if (!($equipmentSource instanceof Armor && $equipmentSource->getTypeId() === ItemTypeIds::DIAMOND_HELMET)) {
                            return;
                        }
                        break;
                    default:
                        return;
                }
                $event->cancel();
                $equipmentSourceAction->getInventory()->removeItem($equipmentSource);
                $cosmeticShardAction->getInventory()->removeItem($cosmeticShardSource);
                $equipmentTag = $equipmentSource->getNamedTag();
                $equipmentTag->setInt("hasCosmeticEffect", $effect);
                $equipmentSourceAction->getInventory()->addItem(ItemHandler::setLoreForItem($equipmentSource));
                if ($equipmentTag->getTag("hasCosmeticEffect")) {
                    $player->sendMessage(Translation::GREEN . "You have successfully replaced the previous cosmetic on the item.");
                } else {
                    $player->sendMessage(Translation::GREEN . "A cosmetic has successfully been applied to the item.");
                }
                $player->getWorld()->addSound($player->getPosition(), new AnvilUseSound());
            }
        }
    }
    
    public function addCosmeticParticleEffect(EntityDamageByEntityEvent $event, NexusPlayer $damager): void {
        $weapon = $damager->getInventory()->getItemInHand();
        if (!($weapon instanceof Sword && $weapon->getTypeId() === ItemTypeIds::DIAMOND_SWORD)) {
            return;
        }
    
        $namedTag = $weapon->getNamedTag();
        if (!$namedTag->getTag("hasCosmeticEffect")) {
            return;
        }
    
        $effectId = $namedTag->getTag("hasCosmeticEffect")->getValue();
        $particle = ItemHandler::getParticleForEffectId($effectId);
    
        if ($particle === null) {
            return;
        }
    
        $damagedPosition = $event->getEntity()->getPosition();
        $particlePosition = new Vector3($damagedPosition->x, $damagedPosition->y + 0.75, $damagedPosition->z);
        $damager->getWorld()->addParticle($particlePosition, $particle);
    }

    /**
     * @priority NORMAL
     * @param PlayerItemHeldEvent $event
     */
    public function onPlayerItemHeld(PlayerItemHeldEvent $event)
    {
        $player = $event->getPlayer();
        if (!$player instanceof NexusPlayer) {
            return;
        }
        if (!$player->isLoaded()) {
            return;
        }
        $this->core->getScheduler()->scheduleDelayedTask(new class($player) extends Task
        {

            /** @var NexusPlayer */
            private $player;

            /**
             *  constructor.
             *
             * @param NexusPlayer $player
             */
            public function __construct(NexusPlayer $player)
            {
                $this->player = $player;
            }

            /**
             * @param int $currentTick
             */
            public function onRun(): void
            {
                if ($this->player->isOnline() === false or (!$this->player->isLoaded())) {
                    return;
                }
                $this->player->getCESession()->setActiveHeldItemEnchantments();
            }
        }, 1);
    }

    /**
     * @priority HIGHEST
     */
    public function onEntityArmorChange(SlotChangeAction $action)
    {
        foreach ($action->getInventory()->getViewers() as $viewer) {
            $entity = $viewer;
        }
        if ($entity instanceof NexusPlayer) {
            $oldItem = $action->getSourceItem();
            $newItem = $action->getTargetItem();
            if ($oldItem->equals($newItem, false, true)) {
                return;
            }
            if ($entity->isLoaded()) {
                $this->core->getScheduler()->scheduleDelayedTask(new class($entity) extends Task
                {

                    /** @var NexusPlayer */
                    private $player;

                    /**
                     *  constructor.
                     *
                     * @param NexusPlayer $player
                     */
                    public function __construct(NexusPlayer $player)
                    {
                        $this->player = $player;
                    }

                    /**
                     * @param int $currentTick
                     */
                    public function onRun(): void
                    {
                        if ($this->player->isOnline() === false or (!$this->player->isLoaded())) {
                            return;
                        }
                        $this->player->getCESession()->setActiveArmorEnchantments();
                        $this->player->getCESession()->reset();
                    }
                }, 1);
            }
        }
    }

    /**
     * @priority LOW
     * @param BlockFormEvent $event
     */
    public function onBlockForm(BlockFormEvent $event): void
    {
        $block = $event->getNewState();
        if ($block->getTypeId() === BlockTypeIds::OBSIDIAN) {
            return;
        }
        if ($block->getTypeId() === BlockTypeIds::COBBLESTONE) {
            $event->cancel();
        }
    }
}
