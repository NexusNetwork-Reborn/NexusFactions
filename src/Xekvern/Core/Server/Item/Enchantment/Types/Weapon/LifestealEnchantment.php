<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EffectIds;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Translation\Translation;

class LifestealEnchantment extends Enchantment
{

    /**
     * LifestealEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Lifesteal", Rarity::MYTHIC, "Has a chance to regain lots of health but chance to get weakness at the same time.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function (EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if ((!$damager instanceof NexusPlayer) or (!$entity instanceof NexusPlayer))  {
                return;
            }
            $random = mt_rand(1, 350);
			$chance = $level * 2;
            if($chance >= $random) {
                $randomHeal = mt_rand(1, 2);
				$randomDamage = mt_rand(1, 2);
                $entity->setHealth($entity->getHealth() - $randomDamage);
                $damager->setHealth($damager->getHealth() + $randomHeal);
                $damager->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId(EffectIds::WEAKNESS), 5 * 20, 1));
                $damager->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId(EffectIds::WITHER), 2 * 20, 1));
                $damager->sendMessage(Translation::GREEN . "Lifesteal " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}