<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Nexus;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use Xekvern\Core\Item\Task\EntityTextTask;
use Xekvern\Core\Server\Item\ItemHandler;

class ImprisonEnchantment extends Enchantment {

    /**
     * ImprisonEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Imprison", Rarity::RARE, "Have a chance add 5 seconds to your opponents enderpearl cooldown.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if((!$entity instanceof NexusPlayer) or (!$damager instanceof NexusPlayer)) {
                return;
            }
            if($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                return;
            }
            $combatManager = Nexus::getInstance()->getPlayerManager()->getCombatHandler();
            if($combatManager->getEnderPearlCooldown($entity) > 0) {
                return;
            }
            $random = mt_rand(1, 200);
            $chance = $level * 3;
            if($chance >= $random) {
                $combatManager->setEnderPearlCooldown($entity, 5);
                $entity->sendMessage(Translation::RED . "You have been imprisoned.");
                $damager->sendMessage(Translation::GREEN . "Imprison " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}