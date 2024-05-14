<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\player\Player;

class ShatterEnchantment extends Enchantment {

    /**
     * ShatterEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Shatter", Rarity::RARE, "Break your opponent's armor faster.", self::DAMAGE, ItemFlags::SWORD, 15);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            if(!$entity instanceof NexusPlayer) {
                return;
            }
            if($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                return;
            }
            $damage = (mt_rand(2, 8) * $level);
            foreach($entity->getArmorInventory()->getContents() as $armor) {
                if($armor instanceof Durable) {
                    $armor->applyDamage((int)$damage);
                }
            }
            return;
        };
    }
}
