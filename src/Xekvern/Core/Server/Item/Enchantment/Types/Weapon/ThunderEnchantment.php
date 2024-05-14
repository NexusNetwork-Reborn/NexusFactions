<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\world\sound\ExplodeSound;
use Xekvern\Core\Server\Entity\Types\Lightning;

class ThunderEnchantment extends Enchantment {

    /**
     * ThunderEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Thunder", Rarity::COMMON, "Have a chance to send a lightning to your opponent.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if((!$entity instanceof NexusPlayer) or (!$damager instanceof NexusPlayer)) {
                return;
            }
            if($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                return;
            }
            $random = mt_rand(1, 200);
            $chance = $level * 3;
            if($chance >= $random) {
                $damage = mt_rand(1, 2) - 0.5;
                $entity->setHealth($entity->getHealth() - $damage);
                $lightning = new Lightning($entity->getLocation());
                $lightning->spawnToAll();
                $damager->broadcastSound(new ExplodeSound(), [$damager, $entity]);
            }
        };
    }
}