<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use Xekvern\Core\Translation\Translation;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\ItemHandler;

class ImmunityEnchantment extends Enchantment {

    /**
     * ImmunityEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Immunity", Rarity::MYTHIC, "Have a chance to remove negative effects.", self::EFFECT_ADD, ItemFlags::ARMOR, 10);
        $this->callable = function(EntityEffectAddEvent $event, int $level) {
            $effect = $event->getEffect();
            if(!$effect->getType()->isBad()) {
                return;
            }
            if($effect->getType() === VanillaEffects::SLOWNESS() and $effect->getEffectLevel() > 5) {
                return;
            }
            $entity = $event->getEntity();
            if(!$entity instanceof NexusPlayer) {
                return;
            }
            $random = mt_rand(1, 30);
            if(ceil($level * 1) >= $random) {
                $event->cancel();
                $entity->sendMessage(Translation::GREEN . "Immunity " . ItemHandler::getRomanNumber($level) . " has Activated");
                $entity->sendMessage(Translation::GREEN . "Your immunity protected you from negative effects.");
                return;
            }
        };
    }
}