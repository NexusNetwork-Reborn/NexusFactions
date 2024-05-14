<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Armor;

use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Translation\Translation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\player\Player;

class BlessEnchantment extends Enchantment {

    /**
     * BlessEnchantment constructor.
     */
    public function __construct() {
        parent::__construct("Bless", Rarity::MYTHIC, "Have a chance to gain regeneration and speed when health is low.", self::DAMAGE_BY, ItemFlags::ARMOR, 2);
        $this->callable = function(EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            if(!$entity instanceof Player) {
                return;
            }
            if($entity->getHealth() <= $event->getFinalDamage()) {
                $random = mt_rand(1, 5);
                $chance = $level;
                if($chance >= $random) {
                    $entity->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 100, 2));
                    $entity->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 100, 3));
                    $entity->sendMessage(Translation::ORANGE . "You've been blessed.");
                    $event->cancel();
                }
            }
        };
    }
}