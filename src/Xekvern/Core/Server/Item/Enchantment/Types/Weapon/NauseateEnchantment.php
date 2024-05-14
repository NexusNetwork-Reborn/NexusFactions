<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\ItemHandler;

class NauseateEnchantment extends Enchantment {

    /**
     * NauseateEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Nauseate", Rarity::COMMON, "Have a chance to give nausea your opponent.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if((!$entity instanceof NexusPlayer) or (!$damager instanceof NexusPlayer)) {
                return;
            }
            if($entity->getEffects()->has(VanillaEffects::NAUSEA())) {
                return;
            }
            if($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                return;
            }
            $random = mt_rand(1, 200);
            $chance = $level * 3;
            if($chance >= $random) {
                $entity->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), $level * 20, 0));
                $entity->sendMessage(Translation::RED . "You are nauseated.");
                $damager->sendMessage(Translation::GREEN . "Nauseate " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}