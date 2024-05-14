<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Translation\Translation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\player\Player;
use Xekvern\Core\Server\Item\ItemHandler;

class EvadeEnchantment extends Enchantment {

    /**
     * EvadeEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Evade", Rarity::MYTHIC, "Have a chance to dodge an attack.", self::DAMAGE_BY, ItemFlags::ARMOR, 10);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            if(!$entity instanceof Player) {
                return;
            }
            $random = mt_rand(1, 800);
            $chance = $level * 3;
            if($chance >= $random) {
                $event->cancel();
                $entity->sendMessage(Translation::GREEN . "Evade " . ItemHandler::getRomanNumber($level) . " has Activated");
                $entity->sendMessage(Translation::GREEN . "You evaded the attack.");
                $damager = $event->getDamager();
                if($damager instanceof Player) {
                    $damager->sendMessage(Translation::RED . "Your opponent evaded the attack.");
                }
            }
        };
    }
}