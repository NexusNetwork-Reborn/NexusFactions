<?php

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Translation\Translation;

class AnnihilationEnchantment extends Enchantment {

    /**
     * AnnihilationEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Annihilation", Rarity::MYTHIC, "Increase damage the lower your opponent's health is.", self::DAMAGE, ItemFlags::SWORD, 10);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $damager = $event->getDamager();
            $entity = $event->getEntity();
            if(!$entity instanceof Living) {
                return;
            }
            if($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                return;
            }
            if(!$damager instanceof NexusPlayer) {
                return;
            }
            $add = (($level / 60) * ($entity->getMaxHealth() - $entity->getHealth())) - 2;
            if($add > 0) {
                $event->setBaseDamage($event->getBaseDamage() + $add);
                $damager->sendMessage(Translation::GREEN . "Annihilation " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}