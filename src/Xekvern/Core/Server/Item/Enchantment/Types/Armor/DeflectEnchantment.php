<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\player\Player;

class DeflectEnchantment extends Enchantment {

    /**
     * DeflectEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Deflect", self::RARITY_GODLY, "Have a chance to dodge and reflect an attack.", self::DAMAGE_BY, ItemFlags::ARMOR, 10);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            if(!$entity instanceof NexusPlayer) {
                return;
            }
            $chance = 100 - (int)($level*3);
            if (mt_rand(1,100) >= $chance) {
                $event->cancel();
                $entity->sendMessage(Translation::RED . "You deflected the attack.");
                $damager = $event->getDamager();
                if($damager instanceof Player) {
                    $damager->sendMessage(Translation::RED . "Your opponent deflected the attack.");
                    $damage = min(1, $event->getFinalDamage() * 0.65);
                    $damager->setHealth($damager->getHealth() - $damage);
                }
            }
        };
    }
}