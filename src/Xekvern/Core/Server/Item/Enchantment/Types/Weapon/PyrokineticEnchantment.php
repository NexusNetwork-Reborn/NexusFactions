<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;

class PyrokineticEnchantment extends Enchantment {

    /**
     * PyrokineticEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Pyrokinetic", self::RARITY_GODLY, "Have a chance send your nearby enemies into a blaze.", self::DAMAGE, ItemFlags::SWORD, 3);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if((!$entity instanceof NexusPlayer) or (!$damager instanceof NexusPlayer)) {
                return;
            }
            if($event->getCause() !== EntityDamageByEntityEvent::CAUSE_PROJECTILE) {
                return;
            }
            $random = mt_rand(1, 150);
            $chance = $level * 3;
            if($chance >= $random) {
                $bb = $damager->getBoundingBox()->expandedCopy(15, 15, 15);
                $world = $damager->getWorld();
                if($world === null) {
                    return;
                }
                foreach($world->getNearbyEntities($bb) as $e) {
                    if($e->getId() === $damager->getId()) {
                        continue;
                    }
                    if($e->isOnFire()) {
                        continue;
                    }
                    if($e instanceof NexusPlayer) {
                        if($e->isLoaded()) {
                            $faction = $e->getDataSession()->getFaction();
                            if($faction !== null and $faction->isInFaction($damager->getName())) {
                                continue;
                            }
                        }
                    }
                    $e->setOnFire($level * 4);
                }
            }
        };
    }
}