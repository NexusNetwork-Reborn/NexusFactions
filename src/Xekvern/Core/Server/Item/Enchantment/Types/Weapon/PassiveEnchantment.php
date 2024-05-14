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

class PassiveEnchantment extends Enchantment
{

    /**
     * PassiveEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Passive", Rarity::RARE, "Has a chance to give mining fatigue to your opponent for 5 seconds and can have a higher chance of doing so depending on the level of the enchant.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function (EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if ((!$damager instanceof NexusPlayer) or (!$entity instanceof NexusPlayer))  {
                return;
            }
            $random = mt_rand(1, 300);
            $chance = $level * 1.5;
            if($chance >= $random) {
                $randomLevel = mt_rand(1, 6);
                $entity->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId(EffectIds::MINING_FATIGUE), 120, $randomLevel));
                $damager->sendMessage(Translation::GREEN . "Passive " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}