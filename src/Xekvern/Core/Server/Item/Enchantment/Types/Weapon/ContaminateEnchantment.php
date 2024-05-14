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

class ContaminateEnchantment extends Enchantment
{

    /**
     * ContaminateEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Contaminate", Rarity::UNCOMMON, "Have a chance to poison your opponent.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function (EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if ((!$entity instanceof NexusPlayer) or (!$damager instanceof NexusPlayer)) {
                return;
            }
            if ($entity->getEffects()->get(VanillaEffects::POISON())) {
                return;
            }
            if ($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                return;
            }
            $random = mt_rand(1, 175);
            $chance = $level * 3;
            if ($chance >= $random) {
                $entity->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), $level * 20, 1));
                $entity->sendMessage(Translation::ORANGE . "You are poisoned.");
                $damager->sendMessage(Translation::GREEN . "Contaminate " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}