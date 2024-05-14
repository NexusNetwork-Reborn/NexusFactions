<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Bow;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\player\Player;
use Xekvern\Core\Nexus;

class PierceEnchantment extends Enchantment {

    /**
     * PierceEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Pierce", Rarity::RARE, "Have a chance to ignore most armor protection and deal more damage.", self::DAMAGE, ItemFlags::BOW, 3);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if((!$entity instanceof Player) or (!$damager instanceof Player)) {
                return;
            }
            if($event->getCause() !== EntityDamageByEntityEvent::CAUSE_PROJECTILE) {
                return;
            }
            $random = mt_rand(1, 150);
            $chance = ceil($level * 3);
            if($chance >= $random) {
                $event->setBaseDamage($event->getBaseDamage() * (0.8 + ($level / 5)));
            }
        };
    }
}