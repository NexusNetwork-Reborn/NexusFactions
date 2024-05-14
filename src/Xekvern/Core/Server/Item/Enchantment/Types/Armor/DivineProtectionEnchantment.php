<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use Xekvern\Core\Server\Item\ItemHandler;

class DivineProtectionEnchantment extends Enchantment {

    /**
     * DivineProtectionEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Divine Protection", self::RARITY_GODLY, "Be immune to all enchantments for a short amount of time.", self::DAMAGE_BY, ItemFlags::ARMOR, 5);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            if(!$entity instanceof NexusPlayer) {
                return;
            }
            if($entity->getCESession()->isDivineProtected()) {
                return;
            }
            $random = mt_rand(1, 2000);
            $chance = $level;
            if($chance >= $random) {
                $entity->getCESession()->setDivineProtected(true);
                Nexus::getInstance()->getScheduler()->scheduleDelayedTask(new class($entity) extends Task {

                    /** @var NexusPlayer */
                    private $player;

                    /**
                     *  constructor.
                     *
                     * @param NexusPlayer $player
                     */
                    public function __construct(NexusPlayer $player) {
                        $this->player = $player;
                    }

                    /**
                     * @param int $currentTick
                     */
                    public function onRun(): void {
                        if($this->player->isOnline()) {
                            $this->player->getCESession()->setDivineProtected(false);
                            $this->player->sendMessage(Translation::RED . "You no longer have divine protection.");
                        }
                    }
                }, 20 * $level);
                $entity->sendMessage(Translation::GREEN . "Divine Protection " . ItemHandler::getRomanNumber($level) . " has Activated");
                $entity->sendMessage(Translation::GREEN . "You have are now divine protected.");
            }
        };
    }
}