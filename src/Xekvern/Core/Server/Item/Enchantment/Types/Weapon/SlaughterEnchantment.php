<?php

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Player\Combat\Boss\ArtificialIntelligence;

class SlaughterEnchantment extends Enchantment {

    /**
     * SlaughterEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Slaughter", Rarity::MYTHIC, "Deal more damage against bosses.", self::DAMAGE, ItemFlags::SWORD, 10);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            if(!$entity instanceof ArtificialIntelligence) {
                return;
            }
            $damage = $event->getBaseDamage();
            $damage = $damage + ($level * 0.4);
            $event->setBaseDamage($damage);    
        };
    }
}