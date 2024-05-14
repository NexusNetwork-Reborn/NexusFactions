<?php

declare(strict_types = 1);

namespace Xekvern\Core\Server\Item\Enchantment\Types\Weapon;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EffectIds;
use pocketmine\entity\effect\EffectInstance;
use Xekvern\Core\Server\Item\Enchantment\Enchantment;
use Xekvern\Core\Player\NexusPlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use Xekvern\Core\Server\Item\ItemHandler;
use Xekvern\Core\Translation\Translation;

class LustEnchantment extends Enchantment
{

    /**
     * LustEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Lust", Rarity::RARE, "Has a chance to take away maximum -10 food bars from your opponent and can have a higher chance and more food depending on the level of the enchant.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function (EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if ((!$damager instanceof NexusPlayer) or (!$entity instanceof NexusPlayer))  {
                return;
            }
            $random = mt_rand(1, 300);
			$chance = $level * 2;
            if($chance >= $random) {
                $randomDamage = mt_rand(1, 3);
                $entity->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId(EffectIds::HUNGER), $level * 20, $randomDamage));
                $damager->sendMessage(Translation::GREEN . "Lust " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}