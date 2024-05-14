<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Bow;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\player\Player;
use Xekvern\Core\Server\Item\ItemHandler;

class ParalyzeEnchantment extends Enchantment {

    /**
     * ParalyzeEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Paralyze", Rarity::RARE, "Give slowness for a long period.", self::DAMAGE, ItemFlags::BOW, 3);
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
                $entity->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), $level * 60, 1));
                $entity->sendMessage(Translation::RED . "You are paralyzed.");
                $damager->sendMessage(Translation::GREEN . "Your opponent is paralyzed.");
                $damager->sendMessage(Translation::GREEN . "Paralyze " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}