<?php

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\BlockBreakParticle;
use Xekvern\Core\Server\Item\ItemHandler;

class BleedEnchantment extends Enchantment {

    /**
     * BleedEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Bleed", Rarity::MYTHIC, "Have a chance to make your enemy bleed.", self::DAMAGE, ItemFlags::SWORD, 10);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if((!$entity instanceof NexusPlayer) or (!$damager instanceof NexusPlayer)) {
                return;
            }
            if($entity->getCESession()->isBleeding()) {
                return;
            }
            if($entity->getCESession()->isDivineProtected()) {
                return;
            }
            $random = mt_rand(1, 250);
            $chance = $level * 3;
            if($chance >= $random) {
                $damage = (($level + $entity->getHealth()) * 0.07) / 2;
                $entity->getCESession()->setBleeding(true);
                Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new class($entity, $damager, $damage) extends Task {

                    /** @var NexusPlayer */
                    private $player;

                    /** @var NexusPlayer */
                    private $damager;

                    /** @var float */
                    private $damage;

                    /** @var int */
                    private $runs = 0;

                    /**
                     *  constructor.
                     *
                     * @param NexusPlayer $player
                     * @param NexusPlayer $damager
                     * @param float $damage
                     */
                    public function __construct(NexusPlayer $player, NexusPlayer $damager, float $damage) {
                        $this->player = $player;
                        $this->damager = $damager;
                        $this->damage = $damage;
                    }

                    /**
                     * @param int $currentTick
                     */
                    public function onRun(): void {
                        if(++$this->runs > 5) {
                            if($this->player->isOnline()) {
                                $this->player->getCESession()->setBleeding(false);
                            }
                            $this->getHandler()->cancel();
                            return;
                        }
                        if($this->player->isOnline() === false) {
                            $this->getHandler()->cancel();
                            return;
                        }
                        $level = $this->player->getWorld();
                        if($level === null) {
                            return;
                        }
                        $level->addParticle($this->player->getEyePos(), new BlockBreakParticle(VanillaBlocks::REDSTONE()));
                        $this->player->attack(new EntityDamageEvent($this->player, EntityDamageEvent::CAUSE_MAGIC, $this->damage));
                    }
                }, 20);
                $entity->sendMessage(Translation::ORANGE . "You are bleeding.");
                $damager->sendMessage(Translation::GREEN . "Bleed " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}