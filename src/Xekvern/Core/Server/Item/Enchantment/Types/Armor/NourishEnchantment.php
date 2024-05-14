<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\player\Player;
use pocketmine\world\sound\PopSound;

class NourishEnchantment extends Enchantment {

    /**
     * NourishEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Nourish", Rarity::COMMON, "Restore your hunger while moving.", self::MOVE, ItemFlags::ARMOR, 10);
        $this->callable = function(PlayerMoveEvent $event, int $level) {
            $entity = $event->getPlayer();
            if(!$entity instanceof Player) {
                return;
            }
            if($event->getFrom()->equals($event->getTo())) {
                return;
            }
            if($entity->getHungerManager()->getFood() > ($entity->getHungerManager()->getMaxFood() - 6)) {
                return;
            }
            $random = mt_rand(1, 500);
            $chance = $level * 3;
            if($chance >= $random) {
                $entity->broadcastSound(new PopSound());
                $entity->getHungerManager()->setFood($entity->getHungerManager()->getMaxFood());
            }
        };
    }
}