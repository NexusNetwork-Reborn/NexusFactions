<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\player\Player;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Translation\Translation;

class BerserkEnchantment extends Enchantment
{

    /**
     * BerserkEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Berserk", self::RARITY_GODLY, "Have a get strength but also receive nausea at the same time.", self::DAMAGE, ItemFlags::SWORD, 2);
        $this->callable = function (EntityDamageByEntityEvent $event, int $level) {
            $damager = $event->getDamager();
            if (!$damager instanceof Player) {
                return;
            }
            $entity = $event->getEntity();
            if ($entity instanceof NexusPlayer and $entity->getCESession()->isDivineProtected()) {
                return;
            }
            if ($damager->getEffects()->has(VanillaEffects::STRENGTH()) and $damager->getEffects()->has(VanillaEffects::NAUSEA())) {
                return;
            }
            $random = mt_rand(1, 200);
            $chance = $level * 3;
            if ($chance >= $random) {
                $damager->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 100, $level - 1));
                $damager->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), 100, 0));
                $damager->sendMessage(Translation::GREEN . "Berserk " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}