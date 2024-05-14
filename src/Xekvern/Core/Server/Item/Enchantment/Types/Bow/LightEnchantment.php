<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Bow;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\player\Player;
use Xekvern\Core\Server\Entity\Types\Lightning;
use pocketmine\world\sound\ExplodeSound;

class LightEnchantment extends Enchantment {

    /**
     * LightEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Light", Rarity::MYTHIC, "Chance to smite your target", self::DAMAGE, ItemFlags::BOW, 5);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if((!$entity instanceof Player) or (!$damager instanceof Player)) {
                return;
            }
            if($event->getCause() !== EntityDamageByEntityEvent::CAUSE_PROJECTILE) {
                return;
            }
            if($entity->getEffects()->has(VanillaEffects::SLOWNESS())) {
                return;
            }
            if($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                return;
            }
            $random = mt_rand(1, 30);
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