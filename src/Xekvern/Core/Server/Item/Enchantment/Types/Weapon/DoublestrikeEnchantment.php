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

class DoublestrikeEnchantment extends Enchantment
{

    /**
     * DoublestrikeEnchantment constructor.
     */
    public function __construct()
    {
        parent::__construct("Doublestrike", Rarity::RARE, "Chance to attack twice in one swing and gain a mystery effect.", self::DAMAGE, ItemFlags::SWORD, 5);
        $this->callable = function (EntityDamageByEntityEvent $event, int $level) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if ((!$damager instanceof NexusPlayer) or (!$entity instanceof NexusPlayer))  {
                return;
            }
            $random = mt_rand(1, 200);
			$chance = $level * 2;
            if($chance >= $random) {
                $entity->setHealth($entity->getHealth() - 0.4);
				$entity->setHealth($entity->getHealth() - 0.7);
				$event->setKnockback($event->getKnockback() + 0.22);
                $damager->sendMessage(Translation::GREEN . "Doublestrike " . ItemHandler::getRomanNumber($level) . " has Activated");
            }
        };
    }
}